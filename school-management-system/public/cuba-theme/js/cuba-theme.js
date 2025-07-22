/**
 * Cuba Admin Theme JavaScript
 * Version: 2.0.0
 * Author: Pixelstrap
 * For: School Management System Laravel App
 */

(function() {
    'use strict';

    // Theme Configuration
    const CubaTheme = {
        config: {
            sidebarCollapsedKey: 'cubaTheme_sidebarCollapsed',
            alertDismissTimeout: 5000,
            animationDuration: 300
        },

        // Initialize theme
        init: function() {
            this.initFeatherIcons();
            this.initSidebar();
            this.initAlerts();
            this.initTooltips();
            this.initProgressBars();
            this.initResponsive();
            this.initSearchFunctionality();
            this.initThemeToggle();
        },

        // Initialize Feather Icons
        initFeatherIcons: function() {
            if (typeof feather !== 'undefined') {
                feather.replace({
                    'stroke-width': 1.5,
                    width: 20,
                    height: 20
                });
            }
        },

        // Sidebar Management
        initSidebar: function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (!sidebar || !sidebarToggle) return;

            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                CubaTheme.saveSidebarState(sidebar.classList.contains('collapsed'));
            });

            // Restore sidebar state
            this.restoreSidebarState();

            // Handle sidebar menu items
            this.initSidebarMenu();
        },

        // Sidebar menu interaction
        initSidebarMenu: function() {
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            
            menuItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    // Remove active class from all items
                    menuItems.forEach(function(menuItem) {
                        menuItem.classList.remove('active');
                    });
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                });
            });
        },

        // Save sidebar state
        saveSidebarState: function(collapsed) {
            try {
                localStorage.setItem(this.config.sidebarCollapsedKey, collapsed ? 'true' : 'false');
            } catch (e) {
                console.log('Could not save sidebar state');
            }
        },

        // Restore sidebar state
        restoreSidebarState: function() {
            try {
                const collapsed = localStorage.getItem(this.config.sidebarCollapsedKey) === 'true';
                const sidebar = document.getElementById('sidebar');
                
                if (collapsed && sidebar) {
                    sidebar.classList.add('collapsed');
                }
            } catch (e) {
                console.log('Could not restore sidebar state');
            }
        },

        // Alert Management
        initAlerts: function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            
            // Auto-dismiss alerts
            setTimeout(function() {
                alerts.forEach(function(alert) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        if (bsAlert) {
                            bsAlert.close();
                        }
                    }
                });
            }, this.config.alertDismissTimeout);
        },

        // Initialize Bootstrap Tooltips
        initTooltips: function() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        },

        // Animate Progress Bars
        initProgressBars: function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            
            // Animate progress bars on load
            setTimeout(function() {
                progressBars.forEach(function(bar) {
                    const percentage = bar.getAttribute('aria-valuenow');
                    bar.style.width = percentage + '%';
                });
            }, 500);

            // Animate progress bars when they come into view
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const bar = entry.target.querySelector('.progress-bar');
                            if (bar) {
                                const percentage = bar.getAttribute('aria-valuenow');
                                bar.style.width = percentage + '%';
                            }
                        }
                    });
                });

                document.querySelectorAll('.progress').forEach(function(progress) {
                    observer.observe(progress);
                });
            }
        },

        // Responsive Behavior
        initResponsive: function() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            function handleResize() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    document.body.classList.add('sidebar-mobile');
                } else {
                    document.body.classList.remove('sidebar-mobile');
                    // Restore desktop sidebar state
                    CubaTheme.restoreSidebarState();
                }
            }

            // Handle resize events
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(handleResize, 150);
            });

            // Initial check
            handleResize();
        },

        // Search Functionality
        initSearchFunctionality: function() {
            const searchInputs = document.querySelectorAll('.search-input');
            
            searchInputs.forEach(function(input) {
                input.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const targetSelector = e.target.getAttribute('data-search-target');
                    
                    if (targetSelector) {
                        const targets = document.querySelectorAll(targetSelector);
                        
                        targets.forEach(function(target) {
                            const text = target.textContent.toLowerCase();
                            const shouldShow = searchTerm === '' || text.includes(searchTerm);
                            
                            target.style.display = shouldShow ? '' : 'none';
                        });
                    }
                });
            });
        },

        // Theme Toggle (for future dark mode support)
        initThemeToggle: function() {
            const themeToggle = document.getElementById('themeToggle');
            
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-theme');
                    
                    // Save theme preference
                    const isDark = document.body.classList.contains('dark-theme');
                    try {
                        localStorage.setItem('cubaTheme_darkMode', isDark ? 'true' : 'false');
                    } catch (e) {
                        console.log('Could not save theme preference');
                    }
                });

                // Restore theme preference
                try {
                    const isDark = localStorage.getItem('cubaTheme_darkMode') === 'true';
                    if (isDark) {
                        document.body.classList.add('dark-theme');
                    }
                } catch (e) {
                    console.log('Could not restore theme preference');
                }
            }
        },

        // Utility Functions
        utils: {
            // Smooth scroll to element
            scrollTo: function(element, offset = 0) {
                if (typeof element === 'string') {
                    element = document.querySelector(element);
                }
                
                if (element) {
                    const top = element.offsetTop - offset;
                    window.scrollTo({
                        top: top,
                        behavior: 'smooth'
                    });
                }
            },

            // Show loading state
            showLoading: function(button) {
                if (button) {
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i data-feather="loader" class="feather spin me-2"></i>Loading...';
                    button.disabled = true;
                    button.setAttribute('data-original-text', originalText);
                    
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }
            },

            // Hide loading state
            hideLoading: function(button) {
                if (button) {
                    const originalText = button.getAttribute('data-original-text');
                    if (originalText) {
                        button.innerHTML = originalText;
                        button.disabled = false;
                        button.removeAttribute('data-original-text');
                    }
                }
            },

            // Format numbers with commas
            formatNumber: function(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },

            // Debounce function
            debounce: function(func, wait, immediate) {
                let timeout;
                return function executedFunction() {
                    const context = this;
                    const args = arguments;
                    const later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            }
        }
    };

    // Initialize theme when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        CubaTheme.init();
    });

    // Make CubaTheme globally available
    window.CubaTheme = CubaTheme;

    // Add CSS animation classes
    const style = document.createElement('style');
    style.textContent = `
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

})();