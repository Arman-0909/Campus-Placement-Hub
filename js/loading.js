/**
 * Global Loading State Handler
 * Campus Placement Hub
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);

        // Handle all form submissions
        document.addEventListener('submit', function (e) {
            const form = e.target;

            // Skip if form has 'no-loading' class
            if (form.classList.contains('no-loading')) return;

            // Show overlay
            overlay.classList.add('active');

            // Find submit button and add loading state
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.classList.add('btn-loading');
                const originalText = btn.innerHTML;

                // Restore button state after 30 seconds (timeout safety)
                setTimeout(() => {
                    btn.classList.remove('btn-loading');
                    overlay.classList.remove('active');
                }, 30000);
            }
        });

        // Handle browser back button (hide overlay if user comes back)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                overlay.classList.remove('active');
                document.querySelectorAll('.btn-loading').forEach(btn => {
                    btn.classList.remove('btn-loading');
                });
            }
        });
    });
})();
