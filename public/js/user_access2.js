function viewUser(userId) {
    fetch(`/users/${userId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                populateUpdateForm(data.data);
                showUpdateUserModal();
            } else {
                showUserResultModal(false, 'Error', 'Failed to load user data');
            }
        })
        .catch(error => {
            showUserResultModal(false, 'Error', 'Failed to fetch user data');
        });
}

function showUpdateUserModal() {
    const modal = document.getElementById('updateUserModal');
    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        const firstInput = modal.querySelector('input:first-of-type');
        if (firstInput) firstInput.focus();
    }, 50);
}

function populateUpdateForm(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_firstname').value = user.firstname;
    document.getElementById('edit_lastname').value = user.lastname;
    document.getElementById('edit_contactnum').value = user.contactnum;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;

    const deleteBtn = document.getElementById('deleteUserBtn');
    if (deleteBtn) {
        deleteBtn.style.display = user.role === 'Administrator' ? 'none' : 'inline-block';
    }
}

function handleUpdateUserSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const userId = document.getElementById('edit_user_id').value;
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    clearValidationErrors();

    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    fetch(`/users/${userId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            submitButton.disabled = false;

            if (data.success) {
                closeModal('updateUserModal');
                showUserResultModal(
                    true,
                    'Success',
                    'User details have been successfully updated.'
                );
            } else if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById('edit_' + field);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[field];
                            feedback.style.display = 'block';
                        }
                    }
                });
            }
        })
        .catch(error => {
            submitButton.disabled = false;
            showUserResultModal(
                false,
                'Error',
                'An error occurred while updating the user. Please try again.'
            );
        });
}

function resetModalClose() {
    const modal = document.getElementById('pwdResetVerificationModal');
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 250);
    }
}

function deleteModalClose() {
    const modal = document.getElementById('deleteVerificationModal');
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 250);
    }
}

function initiatePasswordReset() {
    const userId = document.getElementById('edit_user_id').value;
    showPasswordResetVerification(userId);
}

function showPasswordResetVerification(userId) {
    const modal = document.getElementById('pwdResetVerificationModal');
    const passwordInput = document.getElementById('reset_verify_password');
    const feedback = passwordInput.nextElementSibling.nextElementSibling;

    passwordInput.value = '';
    feedback.style.display = 'none';

    modal.dataset.userId = userId;

    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        passwordInput.focus();
    }, 50);
}

function verifyPasswordForReset() {
    const modal = document.getElementById('pwdResetVerificationModal');
    const userId = modal.dataset.userId;
    const password = document.getElementById('reset_verify_password').value;
    const feedback = document.querySelector('#reset_verify_password + button + .invalid-feedback');

    const formData = new FormData();
    formData.append('password', password);

    fetch(`/users/${userId}/verify-password`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('pwdResetVerificationModal');
                showPasswordResetForm(userId);
            } else {
                feedback.textContent = data.message || 'Invalid password';
                feedback.style.display = 'block';
            }
        })
        .catch(error => {
            feedback.textContent = 'An error occurred while verifying the password';
            feedback.style.display = 'block';
        });
}

function showPasswordResetForm(userId) {
    const modal = document.getElementById('pwdResetFormModal');
    const form = document.getElementById('pwdResetForm');

    form.reset();
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.style.display = 'none';
    });

    modal.dataset.userId = userId;

    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        document.getElementById('new_password').focus();
    }, 50);

    form.onsubmit = function (e) {
        e.preventDefault();
        submitPasswordReset(userId);
    };
}

function submitPasswordReset(userId) {
    const form = document.getElementById('pwdResetForm');
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;

    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
    submitButton.disabled = true;

    fetch(`/users/${userId}/reset-password`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;

            if (data.success) {
                closeModal('pwdResetFormModal');
                showPasswordResetResult(true, 'Success', 'Password has been successfully reset.');
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            const feedback = input.nextElementSibling.nextElementSibling;
                            if (feedback) {
                                feedback.textContent = data.errors[field];
                                feedback.style.display = 'block';
                            }
                        }
                    });
                }
            }
        })
        .catch(error => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            showPasswordResetResult(false, 'Error', 'Failed to reset password. Please try again.');
        });
}

function showPasswordResetResult(success, title, message) {
    const modal = document.getElementById('pwdResetResultModal');
    const iconDiv = document.getElementById('pwdResetResultIcon');
    const titleEl = document.getElementById('pwdResetResultTitle');
    const messageEl = document.getElementById('pwdResetResultMessage');
    const okButton = modal.querySelector('.btn_verify');

    iconDiv.innerHTML = success ?
        '<i class="fas fa-check-circle success-icon"></i>' :
        '<i class="fas fa-times-circle error-icon"></i>';
    titleEl.textContent = title;
    messageEl.textContent = message;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 50);

    const handleClose = () => {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            if (success) {
                window.location.reload();
            }
        }, 300);
    };

    if (okButton) {
        okButton.onclick = handleClose;
    }

    modal.onclick = function (e) {
        if (e.target === modal) {
            handleClose();
        }
    };
}

function deleteUser() {
    const userId = document.getElementById('edit_user_id').value;
    showDeleteVerificationModal(userId);
}

function showDeleteVerificationModal(userId) {
    const modal = document.getElementById('deleteVerificationModal');
    const passwordInput = document.getElementById('delete_password');
    const feedback = passwordInput.nextElementSibling.nextElementSibling;

    passwordInput.value = '';
    feedback.style.display = 'none';

    modal.dataset.userId = userId;

    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        passwordInput.focus();
    }, 50);
}

function verifyDeletePassword() {
    const modal = document.getElementById('deleteVerificationModal');
    const userId = modal.dataset.userId;
    const password = document.getElementById('delete_password').value;
    const feedback = document.querySelector('#delete_password + button + .invalid-feedback');

    fetch(`/users/${userId}/verify-delete-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ password: password })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('deleteVerificationModal');
                showDeleteConfirmation(userId);
            } else {
                feedback.textContent = data.message || 'Invalid password';
                feedback.style.display = 'block';
            }
        })
        .catch(error => {
            feedback.textContent = 'An error occurred while verifying the password';
            feedback.style.display = 'block';
        });
}

function showDeleteConfirmation(userId) {
    const modal = document.getElementById('deleteUserModal');
    modal.dataset.userId = userId;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 50);
}

function confirmDeleteUser() {
    const userId = document.getElementById('deleteUserModal').dataset.userId;

    fetch(`/users/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            closeModal('deleteUserModal');
            closeModal('updateUserModal');

            const modal = document.getElementById('userResultModal');
            const iconDiv = document.getElementById('userResultIcon');
            const titleEl = document.getElementById('userResultTitle');
            const messageEl = document.getElementById('userResultMessage');
            const okButton = modal.querySelector('.btn_verify');

            iconDiv.innerHTML = data.success ?
                '<i class="fas fa-check-circle success-icon"></i>' :
                '<i class="fas fa-times-circle error-icon"></i>';
            titleEl.textContent = data.success ? 'Success' : 'Error';
            messageEl.textContent = data.message || 'User has been deleted successfully';

            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('fade-in'), 50);

            const handleClose = () => {
                modal.classList.remove('fade-in');
                setTimeout(() => {
                    modal.style.display = 'none';
                    if (data.success) {
                        window.location.reload();
                    }
                }, 300);
            };

            okButton.onclick = handleClose;
            modal.onclick = function (e) {
                if (e.target === modal) {
                    handleClose();
                }
            };
        })
        .catch(error => {
            closeModal('deleteUserModal');
            showUserResultModal(
                false,
                'Error',
                'An error occurred while deleting the user. Please try again.'
            );
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const updateUserForm = document.querySelector('#updateUserModal form');
    if (updateUserForm) {
        const inputs = updateUserForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => handleInputChange(input));
            input.addEventListener('change', () => handleInputChange(input));
        });
    }

    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
});