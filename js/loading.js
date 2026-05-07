
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);

        document.addEventListener('submit', function (e) {
            const form = e.target;

            if (form.classList.contains('no-loading')) return;

            overlay.classList.add('active');

            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.classList.add('btn-loading');

                setTimeout(() => {
                    btn.classList.remove('btn-loading');
                    overlay.classList.remove('active');
                }, 30000);
            }
        });

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
