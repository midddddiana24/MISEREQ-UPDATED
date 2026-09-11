    <script>
        // ============================================
        // SIDEBAR TOGGLE
        // ============================================
        (function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const menuToggle = document.getElementById('menuToggle');
            let isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            // Restore collapsed state on desktop
            if (isSidebarCollapsed && window.innerWidth > 968) {
                sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('collapsed');
                if (sidebarToggle) sidebarToggle.textContent = '▶';
            }

            // Desktop collapse/expand button
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth > 968) {
                        isSidebarCollapsed = !isSidebarCollapsed;
                        sidebar.classList.toggle('collapsed', isSidebarCollapsed);
                        if (mainContent) mainContent.classList.toggle('collapsed', isSidebarCollapsed);
                        sidebarToggle.textContent = isSidebarCollapsed ? '▶' : '◀';
                        localStorage.setItem('sidebarCollapsed', isSidebarCollapsed);
                    }
                });
            }

            // Mobile hamburger toggle
            window.toggleSidebar = function() {
                if (window.innerWidth <= 968) {
                    sidebar.classList.toggle('show');
                } else {
                    if (sidebarToggle) sidebarToggle.click();
                }
            };

            // Close sidebar on outside click (mobile)
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 968) {
                    if (menuToggle && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            // Handle resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 968) {
                    sidebar.classList.remove('show');
                    sidebar.style.transform = '';
                }
            });
        })();

        // ============================================
        // NOTIFICATION BELL
        // ============================================
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.toggle('active');
            }
        }

        document.addEventListener('click', function(event) {
            const bell = document.getElementById('notificationBell');
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown && bell && !dropdown.contains(event.target) && !bell.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
