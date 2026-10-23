(function () {
    'use strict';

    // Eye icon: show / hide the password in the field named by data-toggle-password.
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        var input = document.getElementById(button.getAttribute('data-toggle-password'));
        if (!input) {
            return;
        }
        button.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.querySelector('.icon-show').classList.toggle('d-none', show);
            button.querySelector('.icon-hide').classList.toggle('d-none', !show);
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            input.focus();
        });
    });
})();
