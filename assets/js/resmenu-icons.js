/**
 * Inline SVG icons for Resmenu UI (no icon fonts).
 */
(function (window) {
    'use strict';

    var PATHS = {
        'shopping-bag': '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        plus: '<path d="M5 12h14"/><path d="M12 5v14"/>',
        minus: '<path d="M5 12h14"/>',
        x: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        menu: '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'arrow-left': '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'arrow-right': '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'arrow-up': '<path d="m18 15-6-6-6 6"/>',
        eye: '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off': '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'
    };

    var ALIASES = {
        shopping_bag: 'shopping-bag',
        remove: 'minus',
        add: 'plus',
        close: 'x'
    };

    function resolve(name) {
        return ALIASES[name] || name;
    }

    function icon(name, opts) {
        opts = opts || {};
        var resolved = resolve(name);
        var path = PATHS[resolved];
        if (!path) return '';
        var size = opts.size || 24;
        var cls = 'resmenu-icon resmenu-icon--' + resolved;
        if (opts.className) cls += ' ' + opts.className;
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '" aria-hidden="true">' + path + '</svg>';
    }

    window.RESMENU_ICONS = { icon: icon, resolve: resolve };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
            var inputId = btn.getAttribute('data-password-toggle');
            var input = inputId ? document.getElementById(inputId) : null;
            var wrap = btn.querySelector('[data-resmenu-password-icons]');
            if (!input || !wrap) return;
            var eye = wrap.querySelector('.resmenu-icon-eye');
            var eyeOff = wrap.querySelector('.resmenu-icon-eye-off');
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                if (eye) eye.classList.toggle('hidden', show);
                if (eyeOff) eyeOff.classList.toggle('hidden', !show);
            });
        });
    });
})(window);
