
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form:not(.no-validate)');

        forms.forEach(form => {

            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    form.classList.add('was-validated');

                    form.animate([
                        { transform: 'translateX(0)' },
                        { transform: 'translateX(-5px)' },
                        { transform: 'translateX(5px)' },
                        { transform: 'translateX(0)' }
                    ], {
                        duration: 300
                    });
                }
            });
        });
    });
})();
