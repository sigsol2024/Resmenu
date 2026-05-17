/**
 * Resmenu Cart Widget
 * Floating cart button at bottom-left, red, with count and total
 */

(function (window) {
    'use strict';

    const CART = window.RESMENU_CART;
    const ICONS = window.RESMENU_ICONS;
    if (!CART) return;

    let container = null;

    function cartIcon(name, size, className) {
        if (ICONS && typeof ICONS.icon === 'function') {
            return ICONS.icon(name, { size: size || 24, className: className || '' });
        }
        return '';
    }

    function render(config) {
        const slug = config.restaurantSlug || '';
        const count = CART.getTotalCount(slug);
        const total = CART.getTotalAmount(slug);
        const symbol = config.currencySymbol || '₦';
        const checkoutUrl = config.checkoutUrl || '#';

        if (!container) return;

        if (count <= 0) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        const primaryColor = config.primaryColor || '#f20d0d';
        container.innerHTML = `
            <button type="button" id="resmenu-cart-widget-btn" class="resmenu-cart-widget-btn flex items-center gap-3 px-4 py-3 rounded-full text-white shadow-lg transition-colors border-0 cursor-pointer font-display" style="background-color:${primaryColor}">
                ${cartIcon('shopping-bag', 24, 'shrink-0')}
                <span class="flex flex-col items-start">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-90">${count} item${count !== 1 ? 's' : ''}</span>
                    <span class="text-sm font-bold">${CART.formatPrice(total, symbol)}</span>
                </span>
            </button>
        `;

        const btn = container.querySelector('#resmenu-cart-widget-btn');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var doOpen = function () {
                    if (typeof config.onOpenModal === 'function') {
                        config.onOpenModal();
                    } else if (window.RESMENU_CART_MODAL && typeof window.RESMENU_CART_MODAL.open === 'function') {
                        window.RESMENU_CART_MODAL.open();
                    }
                };
                setTimeout(doOpen, 0);
            });
        }
    }

    function ensureWidgetStyles() {
        if (document.getElementById('resmenu-cart-widget-styles')) return;
        const style = document.createElement('style');
        style.id = 'resmenu-cart-widget-styles';
        style.textContent = '#resmenu-cart-widget{z-index:9999!important}.resmenu-cart-widget-btn:hover{background-color:#121212!important}.resmenu-cart-widget-btn .resmenu-icon{display:inline-block;line-height:0;flex-shrink:0}';
        document.head.appendChild(style);
    }

    function init(config) {
        if (!config || !config.restaurantSlug) return;

        ensureWidgetStyles();
        container = document.getElementById('resmenu-cart-widget');
        if (!container) {
            container = document.createElement('div');
            container.id = 'resmenu-cart-widget';
            container.className = 'fixed bottom-6 left-6 z-50 hidden';
            document.body.appendChild(container);
        }

        render(config);

        window.addEventListener(CART.CART_UPDATE_EVENT, function (e) {
            if (e.detail && e.detail.restaurantSlug === config.restaurantSlug) {
                render(config);
            }
        });
    }

    window.RESMENU_CART_WIDGET = {
        init,
        render: function () { if (container) render(window.RESMENU_CART_CONFIG || {}); }
    };
})(window);
