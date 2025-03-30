document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById("sidebar");
    const sidebarToggleBtn = document.getElementById("sidebar-toggle-btn");

    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isSidebarCollapsed) {
        sidebar.classList.add('sidebar-collapsed');
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("sidebar-collapsed");
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
        });
    }

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', (e) => {
            if (sidebar.classList.contains('sidebar-collapsed')) {
                e.preventDefault();
                const href = item.getAttribute('href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            }
        });
    });

    const dropdowns = document.querySelectorAll('.tab-item.dropdown');
    dropdowns.forEach(dropdown => {
        const header = dropdown.querySelector('.tab-header');
        const hasActiveItem = dropdown.querySelector('.dropdown-item.active');

        if (hasActiveItem) {
            dropdown.classList.add('active-parent');
        }

        if (header) {
            header.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (!sidebar.classList.contains('sidebar-collapsed')) {
                    dropdown.classList.toggle('open');

        
                    dropdowns.forEach(other => {
                        if (other !== dropdown) {
                            other.classList.remove('open');
                        }
                    });
                }
            });
        }
    });

    const activeDropdownItems = document.querySelectorAll('.dropdown-item.active');
    activeDropdownItems.forEach(item => {
        const parentDropdown = item.closest('.dropdown');
        if (parentDropdown) {
            parentDropdown.classList.add('active-parent');
            if (!sidebar.classList.contains('sidebar-collapsed')) {
                parentDropdown.classList.add('open');
            }
        }
    });

    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');

            const parentDropdown = link.closest('.dropdown');
            if (parentDropdown) {
                parentDropdown.classList.add('open');
            }
        }
    });
});
