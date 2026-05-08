document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (!sidebar) return;

    // --- Overlay ---
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    // --- Toggle Logic ---
    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleSidebar() {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    }

    // --- Event Listeners ---
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // --- Auto-close on desktop resize ---
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024 && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // --- Active link highlighting ---
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = sidebar.querySelectorAll('.sidebar-link');

    navLinks.forEach(function (link) {
        const linkPath = link.getAttribute('href')
            ? link.getAttribute('href').split('/').pop()
            : '';
        if (linkPath && linkPath === currentPath) {
            link.classList.add('active');
        }
    });
});
