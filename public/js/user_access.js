function showAddUserModal() {
    const modal = document.getElementById('addUserModal');
    const form = modal.querySelector('form');
    form.reset();
    clearValidationErrors();

    modal.style.display = 'block';
    setTimeout(() => {
        modal.classList.add('fade-in');
        const firstInput = form.querySelector('input:first-of-type');
        if (firstInput) firstInput.focus();
    }, 50);
}

function closeModal(modalId) {
    if (modalId === 'reload') {
        window.location.reload();
        return;
    }

    if (modalId === 'noreload' || !modalId) {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        });
        return;
    }

    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 250);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const addUserForm = document.querySelector('#addUserModal form');
    if (addUserForm) {
        const inputs = addUserForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => handleInputChange(input));
            input.addEventListener('change', () => handleInputChange(input));
        });

        addUserForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;

            clearValidationErrors();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    submitButton.disabled = false;

                    if (data.success) {
                        closeModal('addUserModal');
                        showUserResultModal(
                            true,
                            'Success',
                            `User "${formData.get('username')}" has been successfully created with the role of ${formData.get('role')}.`
                        );
                        window.location.reload();
                    } else if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = data.errors[field][0];
                                    feedback.style.display = 'block';
                                }
                            }
                        });

                        const firstInvalid = addUserForm.querySelector('.is-invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                })
                .catch(error => {
                    submitButton.disabled = false;
                    showUserResultModal(
                        false,
                        'Error',
                        'An error occurred while creating the user. Please try again.'
                    );
                });
        });
    }

    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const visibleModal = document.querySelector('.modal[style*="display: block"]');
            if (visibleModal) {
                closeModal(visibleModal.id);
            }
        }
    });
});

function showUserResultModal(success, title, message) {
    const modal = document.getElementById('userResultModal');
    const iconDiv = document.getElementById('userResultIcon');
    const titleEl = document.getElementById('userResultTitle');
    const messageEl = document.getElementById('userResultMessage');
    const okButton = modal.querySelector('.btn_verify');

    iconDiv.innerHTML = success ?
        '<i class="fas fa-check-circle success-icon"></i>' :
        '<i class="fas fa-times-circle error-icon"></i>';
    titleEl.textContent = title || (success ? 'Success' : 'Error');
    messageEl.textContent = message || (success ? 'Operation completed successfully.' : 'An error occurred.');

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

function clearValidationErrors() {
    const errorElements = document.querySelectorAll('.invalid-feedback');
    errorElements.forEach(element => {
        element.textContent = '';
        element.style.display = 'none';
    });

    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-warning', 'is-valid');
    });
}

function displayValidationErrors(errors) {
    clearValidationErrors();

    if (errors.generic) {
        const firstInput = document.querySelector('.form-control');
        if (firstInput) {
            firstInput.classList.add('is-invalid');
            const feedback = firstInput.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = Array.isArray(errors.generic) ? errors.generic[0] : errors.generic;
                feedback.style.display = 'block';
            }
        }
        return;
    }

    Object.keys(errors).forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                feedback.style.display = 'block';
            }
        }
    });

    const firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.focus();
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function handleInputChange(input) {
    input.classList.remove('is-invalid');
    const feedback = input.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const contactInput = document.getElementById('contactnum');
    if (contactInput) {
        contactInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }

            const maxLength = 11;
            const currentLength = this.value.length;

            if (currentLength > 0) {
                if (currentLength < maxLength) {
                    this.classList.add('is-warning');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-warning');
                }
            } else {
                this.classList.remove('is-warning', 'is-valid');
            }
        });
    }

    const editContactInput = document.getElementById('edit_contactnum');
    if (editContactInput) {
        editContactInput.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }

            const maxLength = 11;
            const currentLength = this.value.length;

            if (currentLength > 0) {
                if (currentLength < maxLength) {
                    this.classList.add('is-warning');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.add('is-valid');
                    this.classList.remove('is-warning');
                }
            } else {
                this.classList.remove('is-warning', 'is-valid');
            }
        });
    }
});

function toggleUserStatus(userId, action) {
    const modal = document.getElementById('statusChangeModal');
    const title = document.getElementById('statusModalTitle');
    const message = document.getElementById('statusModalMessage');
    const confirmBtn = document.getElementById('confirmStatusBtn');

    title.textContent = action === 'activate' ? 'Activate User' : 'Deactivate User';
    message.textContent = `Are you sure you want to ${action} this user?`;
    confirmBtn.textContent = action === 'activate' ? 'Activate' : 'Deactivate';
    confirmBtn.className = `btn_modal ${action === 'activate' ? 'btn_verify' : 'btn_delete'}`;

    confirmBtn.onclick = () => confirmStatusChange(userId, action);

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 50);
}

function confirmStatusChange(userId, action) {
    fetch(`/users/${userId}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            closeModal('statusChangeModal');
            if (data.success) {
                showStatusResultModal(true,
                    `User ${action}d Successfully`,
                    `The user has been ${action}d.`
                );
            } else {
                showStatusResultModal(false,
                    'Operation Failed',
                    data.message || `Failed to ${action} user.`
                );
            }
        })
        .catch(error => {
            closeModal('statusChangeModal');
            showStatusResultModal(false,
                'Error',
                `An error occurred while trying to ${action} the user.`
            );
        });
}

function showStatusResultModal(success, title, message) {
    const modal = document.getElementById('statusResultModal');
    const iconDiv = document.getElementById('statusResultIcon');
    const titleEl = document.getElementById('statusResultTitle');
    const messageEl = document.getElementById('statusResultMessage');
    const okButton = modal.querySelector('.btn_verify');

    iconDiv.innerHTML = success ?
        '<i class="fas fa-check-circle success-icon"></i>' :
        '<i class="fas fa-times-circle error-icon"></i>';
    titleEl.textContent = title;
    messageEl.textContent = message;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 50);

    if (okButton) {
        okButton.onclick = function () {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
                if (success) {
                    window.location.reload();
                }
            }, 300);
        };
    }

    modal.onclick = function (e) {
        if (e.target === modal) {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
                if (success) {
                    window.location.reload();
                }
            }, 300);
        }
    };
}