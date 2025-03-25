function showAddRoleModal() {
    const modal = document.getElementById('addRoleModal');
    const form = modal.querySelector('form');
    form.reset();
    clearRoleFormErrors(form);
    
    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        const firstInput = form.querySelector('input:first-of-type');
        if (firstInput) firstInput.focus();
    }, 50);
}

function closeAddRoleModal() {
    const modal = document.getElementById('addRoleModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        document.getElementById('addRoleForm').reset();
    }, 300);
}

function handleRoleSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    clearRoleFormErrors(form);
    const formData = new FormData(form);

    fetch('/roles', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        if (body.success) {
            closeAddRoleModal();
            const resultModal = document.getElementById('resultModal');
            resultModal.setAttribute('data-refresh', 'true');
            showRoleResultModal('success', `Role "${formData.get('name')}" has been successfully created.`);
        } else {
            if (status === 422 && body.errors) {
                Object.keys(body.errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = body.errors[field][0];
                            feedback.style.display = 'block';
                        }
                    }
                });
                
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                showRoleResultModal('warning', body.message || 'Failed to create role');
            }
        }
    })
    .catch(error => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        console.error('Error:', error);
        showRoleResultModal('error', 'An error occurred while creating the role. Please try again.');
    });
}

function handleRoleError(error) {
    if (error.name === 'SyntaxError') {
        console.error('Invalid JSON response:', error);
    } else if (error.errors) {
        Object.keys(error.errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback') || 
                               createErrorElement(error.errors[field][0]);
                errorDiv.style.display = 'block';
                if (!input.parentElement.querySelector('.invalid-feedback')) {
                    input.parentElement.appendChild(errorDiv);
                }
            }
        });
    } else {
        alert('Error creating role: ' + error.message);
    }
}

function createErrorElement(message) {
    const div = document.createElement('div');
    div.className = 'invalid-feedback';
    div.textContent = message;
    return div;
}

let roleToDelete = null;

function deleteRole(roleId) {
    roleToDelete = roleId;
    const modal = document.getElementById('deleteRoleModal');
    modal.style.display = 'block';

    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeRoleDeleteModal() {
    const modal = document.getElementById('deleteRoleModal');
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            roleToDelete = null;
        }, 300);
    }
}

function showRoleResultModal(type, message, shouldRefresh = false) {
    const modal = document.getElementById('resultModal');
    if (!modal) return;

    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const messageElement = document.getElementById('resultMessage');

    modal.onclick = null;
    icon.className = '';

    switch(type) {
        case 'warning':
            icon.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>';
            title.textContent = 'Warning';
            break;
        case 'success':
            icon.innerHTML = '<i class="fas fa-check-circle" style="color: #28a745;"></i>';
            title.textContent = 'Success';
            break;
        case 'error':
            icon.innerHTML = '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
            title.textContent = 'Error';
            break;
    }

    messageElement.textContent = message;
    
    if (shouldRefresh) {
        modal.setAttribute('data-refresh', 'true');
    }

    modal.onclick = function(event) {
        if (event.target === modal) {
            closePermissionResultModal();
        }
    };

    modal.style.display = 'block';
    modal.classList.add('fade-in');
}

function closeRoleResultModal() {
    const modal = document.getElementById('resultModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
            modal.removeAttribute('data-refresh');
        }
    }, 300);
}

function confirmDeleteRole() {
    if (!roleToDelete) return;

    fetch(`/roles/${roleToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        closeRoleDeleteModal();
        const modal = document.getElementById('resultModal');
        
        if (data.success) {
            showRoleResultModal('success', 'Role deleted successfully');
            modal.setAttribute('data-refresh', 'true');
        } else {
            if (data.message && (
                data.message.includes('currently assigned to') || 
                data.message.includes('cannot be deleted') ||
                data.message.includes('Cannot delete')
            )) {
                showRoleResultModal('warning', data.message);
            } else {
                showRoleResultModal('error', data.message || 'Failed to delete role');
            }
        }
    })
    .catch(error => {
        closeRoleDeleteModal();
        console.error('Error:', error);
        showRoleResultModal('error', 'Error deleting role. Please try again.');
    });
}

function editRole(roleId) {
    fetch(`/roles/${roleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('editRoleModal');
                const nameInput = document.getElementById('editRoleName');
                nameInput.value = data.role.name;
                nameInput.defaultValue = data.role.name;
                document.getElementById('editRoleId').value = data.role.role_id; 
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                showRoleResultModal('warning', data.message || 'Error loading role data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showRoleResultModal('warning', 'Error loading role data');
        });
}

function closeRoleEditModal() {
    const modal = document.getElementById('editRoleModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        document.getElementById('editRoleForm').reset();
    }, 300);
}

function updateRole(event) {
    event.preventDefault();
    const form = event.target;
    const nameInput = document.getElementById('editRoleName');
    
    if (nameInput.value === nameInput.defaultValue) {
        showRoleResultModal('warning', 'No changes were made to update');
        return;
    }

    const roleId = document.getElementById('editRoleId').value;
    const formData = new FormData(form);

    clearRoleFormErrors(form);

    fetch(`/roles/${roleId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRoleEditModal();
            showRoleResultModal('success', data.message || 'Role updated successfully');
            const modal = document.getElementById('resultModal');
            modal.setAttribute('data-refresh', 'true');
        } else if (data.errors) {
            handleRoleValidationErrors(form, data.errors);
        } else {
            showRoleResultModal('warning', data.message || 'Failed to update role');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showRoleResultModal('warning', error.message || 'Error updating role');
    });
}

function clearRoleFormErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(input => {
        input.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.textContent = '';
        feedback.style.display = 'none';
    });
}

function handleRoleValidationErrors(form, errors) {
    Object.keys(errors).forEach(field => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = errors[field][0];
                feedback.style.display = 'block';
            }
        }
    });
}

function updateRolePermissions(roleId) {
    const permissions = [];
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        if (!checkbox.disabled) {
            permissions.push({
                permission_id: checkbox.id.replace('permission_', ''),
                granted: checkbox.checked
            });
        }
    });

    fetch(`/roles/${roleId}/update-permissions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPermissionResultModal('success', data.message || 'Permissions updated successfully');
        } else {
            showPermissionResultModal('error', data.message || 'Failed to update permissions');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showPermissionResultModal('error', 'An error occurred while updating permissions');
    });
}

function showPermissionResultModal(type, message) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const messageElement = document.getElementById('resultMessage');

    icon.innerHTML = type === 'success' 
        ? '<i class="fas fa-check-circle" style="color: #28a745;"></i>'
        : '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
    
    title.textContent = type === 'success' ? 'Success' : 'Error';
    messageElement.textContent = message;

    modal.setAttribute('data-refresh', 'true');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);

    // Close modal when clicking outside
    modal.onclick = function(event) {
        if (event.target === modal) {
            closePermissionResultModal();
        }
    };
}

function closePermissionResultModal() {
    const modal = document.getElementById('resultModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
            modal.removeAttribute('data-refresh');
        }
    }, 300);
}