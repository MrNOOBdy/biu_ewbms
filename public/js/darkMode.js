function initializeDarkMode() {
    const darkModeSelect = document.getElementById('darkModeSelect');
    const globalToggle = document.getElementById('darkModeToggle');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const savedPreference = localStorage.getItem('darkModePreference') || 'system';

    function updateDarkMode(preference) {
        const isDark = preference === 'on' || (preference === 'system' && prefersDark.matches);
        document.documentElement.classList.remove('dark-mode', 'light-mode');
        document.documentElement.classList.add(isDark ? 'dark-mode' : 'light-mode');
        document.body.classList.toggle('dark-mode', isDark);
        
        if (globalToggle) {
            globalToggle.checked = isDark;
        }

        if (darkModeSelect) {
            darkModeSelect.value = preference;
        }
    }

    updateDarkMode(savedPreference);

    if (darkModeSelect) {
        darkModeSelect.addEventListener('change', function() {
            const preference = this.value;
            localStorage.setItem('darkModePreference', preference);
            updateDarkMode(preference);
        });
    }

    if (globalToggle) {
        globalToggle.addEventListener('change', function() {
            const preference = this.checked ? 'on' : 'off';
            localStorage.setItem('darkModePreference', preference);
            updateDarkMode(preference);
        });
    }

    prefersDark.addEventListener('change', (e) => {
        if (localStorage.getItem('darkModePreference') === 'system') {
            updateDarkMode('system');
        }
    });
}

initializeDarkMode();
