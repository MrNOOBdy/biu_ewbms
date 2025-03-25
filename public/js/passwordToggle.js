document.addEventListener('DOMContentLoaded', function () {
    function setupUnauthorizedPasswordToggle() {
        const toggleButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('mousedown', function (e) {
                e.preventDefault();
                passwordInput.type = 'text';
                this.querySelector('i').classList.remove('fa-eye-slash');
                this.querySelector('i').classList.add('fa-eye');
            });

            toggleButton.addEventListener('mouseup', function (e) {
                e.preventDefault();
                passwordInput.type = 'password';
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            });

            toggleButton.addEventListener('mouseleave', function (e) {
                e.preventDefault();
                passwordInput.type = 'password';
                this.querySelector('i').classList.remove('fa-eye');
                this.querySelector('i').classList.add('fa-eye-slash');
            });

            toggleButton.addEventListener('selectstart', function (e) {
                e.preventDefault();
            });
        }
    }

    setupUnauthorizedPasswordToggle();
});
