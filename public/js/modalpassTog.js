function setupModalPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(toggleButton => {
        const newToggle = toggleButton.cloneNode(true);
        toggleButton.parentNode.replaceChild(newToggle, toggleButton);

        let passwordInput = newToggle.closest('.form-group').querySelector('input');

        newToggle.addEventListener('mousedown', function (e) {
            e.preventDefault();
            if (passwordInput) {
                passwordInput.type = 'text';
                this.querySelector('i').classList.remove('fa-eye-slash');
                this.querySelector('i').classList.add('fa-eye');
            }
        });

        newToggle.addEventListener('mouseup', function (e) {
            e.preventDefault();
            if (passwordInput) {
                passwordInput.type = 'password';
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            }
        });

        newToggle.addEventListener('mouseleave', function (e) {
            e.preventDefault();
            if (passwordInput) {
                passwordInput.type = 'password';
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            }
        });
 
        newToggle.addEventListener('selectstart', function (e) {
            e.preventDefault();
        });
    });
}

document.addEventListener('DOMContentLoaded', setupModalPasswordToggles);

document.addEventListener('tabChanged', setupModalPasswordToggles);

document.addEventListener('click', function (e) {
    if (e.target.matches('[onclick*="showAddUserModal"], [onclick*="initiatePasswordReset"], [onclick*="deleteUser"]')) {
        setTimeout(setupModalPasswordToggles, 100);
    }
});

window.setupModalPasswordToggles = setupModalPasswordToggles;
