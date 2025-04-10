class TabToolkit {
    constructor() {
        this.activeToolkit = null;
        this.hideTimeout = null;
        this.currentHoveredTab = null;
        this.isTransitioning = false;
        this.init();
    }

    init() {
        document.querySelectorAll('.tab-item').forEach(tab => {
            tab.addEventListener('mouseenter', () => {
                if (this.currentHoveredTab !== tab) {
                    this.currentHoveredTab = tab;
                    this.showTooltipForTab(tab);
                }
            });

            tab.addEventListener('mouseleave', (e) => {
                const relatedTarget = e.relatedTarget;
                const isGoingToTooltip = relatedTarget && relatedTarget.closest('.tab-toolkit');
                const isGoingToAnotherTab = relatedTarget && relatedTarget.closest('.tab-item');

                if (!isGoingToTooltip && !isGoingToAnotherTab) {
                    this.currentHoveredTab = null;
                    this.scheduleHideTooltip();
                }
            });
        });
    }

    showTooltipForTab(tab) {
        if (this.isTransitioning) return;
        if (!document.getElementById('sidebar').classList.contains('sidebar-collapsed')) return;

        const title = tab.dataset.title || tab.querySelector('.tab-label')?.textContent?.trim();
        const isDropdown = tab.classList.contains('dropdown');

        this.removeExistingTooltip(true);
        this.createTooltip(tab, title, isDropdown);
    }

    createTooltip(tab, title, isDropdown) {
        this.removeExistingTooltip();

        const tooltip = document.createElement('div');
        tooltip.className = 'tab-toolkit';

        if (isDropdown) {
            this.createDropdownTooltip(tooltip, tab, title);
        } else {
            this.createSimpleTooltip(tooltip, title);
        }

        tooltip.addEventListener('mouseenter', () => clearTimeout(this.hideTimeout));
        tooltip.addEventListener('mouseleave', (e) => {
            const relatedTarget = e.relatedTarget;
            const isGoingToTab = relatedTarget && relatedTarget.closest('.tab-item');
            
            if (!isGoingToTab) {
                this.currentHoveredTab = null;
                this.scheduleHideTooltip();
            }
        });

        document.body.appendChild(tooltip);
        this.positionTooltip(tooltip, tab);
        this.activeToolkit = tooltip;

        requestAnimationFrame(() => tooltip.style.animation = 'fadeIn 0.2s ease-out forwards');
    }

    createSimpleTooltip(tooltip, title) {
        tooltip.innerHTML = `<div class="toolkit-content">${title}</div>`;
    }

    createDropdownTooltip(tooltip, tab, title) {
        const items = Array.from(tab.querySelectorAll('.dropdown-item'));
        const activeItem = tab.querySelector('.dropdown-item.active');

        tooltip.innerHTML = `
            <div class="toolkit-header">${title}</div>
            <div class="toolkit-items">
                ${items.map(item => `
                    <a href="${item.getAttribute('href') || '#'}" 
                       class="toolkit-item ${item === activeItem ? 'active' : ''}"
                       data-tab="${item.dataset.tab || ''}">
                        ${item.textContent.trim()}
                        ${item === activeItem ? '<span class="active-indicator">•</span>' : ''}
                    </a>
                `).join('')}
            </div>
        `;

        tooltip.querySelectorAll('.toolkit-item').forEach((item, index) => {
            item.addEventListener('click', (e) => {
                if (!item.href || item.href === '#') {
                    e.preventDefault();
                    const originalItem = items[index];
                    if (originalItem && typeof originalItem.click === 'function') {
                        originalItem.click();
                    }
                }
            });
        });
    }

    positionTooltip(tooltip, tab) {
        const tabRect = tab.getBoundingClientRect();
        const sidebarWidth = document.getElementById('sidebar').offsetWidth;

        tooltip.style.position = 'fixed';
        tooltip.style.left = `${sidebarWidth}px`;
        tooltip.style.top = `${tabRect.top}px`;

        tooltip.addEventListener('mouseenter', () => {
            clearTimeout(this.hideTimeout);
        });

        tooltip.addEventListener('mouseleave', (e) => {
            const relatedTarget = e.relatedTarget;
            const isGoingToTab = relatedTarget && relatedTarget.closest('.tab-item');
            
            if (!isGoingToTab) {
                this.currentHoveredTab = null;
                this.scheduleHideTooltip();
            }
        });

        const tooltipHeight = tooltip.offsetHeight;
        const windowHeight = window.innerHeight;
        if (tabRect.top + tooltipHeight > windowHeight) {
            tooltip.style.top = `${windowHeight - tooltipHeight - 10}px`;
        }
    }

    scheduleHideTooltip() {
        if (this.hideTimeout) {
            clearTimeout(this.hideTimeout);
        }
        this.hideTimeout = setTimeout(() => {
            if (!this.currentHoveredTab) {
                this.removeExistingTooltip();
            }
        }, 100);
    }

    handleMouseLeave() {
        this.hideTimeout = setTimeout(() => {
            const tooltip = this.activeToolkit;
            if (!tooltip) return;

            const tooltipRect = tooltip.getBoundingClientRect();
            const mouseX = event.clientX;
            const mouseY = event.clientY;
            const isOverTooltip = mouseX >= tooltipRect.left && 
                                mouseX <= tooltipRect.right && 
                                mouseY >= tooltipRect.top && 
                                mouseY <= tooltipRect.bottom;

            if (!isOverTooltip && !this.currentHoveredTab) {
                this.removeExistingTooltip();
            }
        }, 100);
    }

    removeExistingTooltip(immediate = false) {
        if (this.activeToolkit) {
            if (immediate) {
                this.activeToolkit.remove();
                this.activeToolkit = null;
                this.isTransitioning = false;
            } else {
                this.isTransitioning = true;
                const tooltip = this.activeToolkit;
                tooltip.classList.add('removing');
                setTimeout(() => {
                    tooltip.remove();
                    if (this.activeToolkit === tooltip) {
                        this.activeToolkit = null;
                    }
                    this.isTransitioning = false;
                }, 200);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new TabToolkit());

