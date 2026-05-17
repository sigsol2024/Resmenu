/**
 * Resmenu Cart Preview Modal
 * WooCommerce-style modal with item list, quantity controls, summary, and checkout button
 */

(function (window) {
    'use strict';

    const CART = window.RESMENU_CART;
    const ICONS = window.RESMENU_ICONS;
    if (!CART) return;

    function cartIcon(name, size, className) {
        if (ICONS && typeof ICONS.icon === 'function') {
            return ICONS.icon(name, { size: size || 24, className: className || '' });
        }
        return '';
    }

    let overlay = null;
    let modalEl = null;
    let config = {};

    function close() {
        if (overlay) overlay.classList.add('hidden');
        if (modalEl) modalEl.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function open() {
        if (!overlay || !modalEl) {
            if (window.RESMENU_CART_CONFIG) init(window.RESMENU_CART_CONFIG);
            if (!overlay || !modalEl) return;
        }
        overlay.classList.remove('hidden');
        modalEl.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        render();
    }

    function render() {
        const slug = config.restaurantSlug || '';
        const items = CART.getCart(slug);
        const symbol = config.currencySymbol || '₦';
        const uploadBaseUrl = config.uploadBaseUrl || '';
        const checkoutUrl = config.checkoutUrl || '#';
        const primaryColor = config.primaryColor || '#f20d0d';

        const subtotal = CART.getTotalAmount(slug);
        const deliveryFee = config.deliveryFee || 0;
        const taxRate = config.taxRate != null ? config.taxRate : 0;
        const tax = subtotal * taxRate;
        const total = subtotal + deliveryFee + tax;

        if (!modalEl) return;

        const itemsHtml = items.map(function (item) {
            const imgUrl = item.image ? (uploadBaseUrl + '/menu-items/' + item.image) : '';
            const imgStyle = imgUrl ? 'background-image:url(\'' + imgUrl.replace(/'/g, "\\'") + '\')' : 'background:#e5e5e5';
            const lineTotal = (parseFloat(item.price) || 0) * (item.quantity || 1);
            return `
                <div class="flex gap-4 py-4 border-b border-gray-200 last:border-0" data-item-id="${item.id}">
                    <div class="w-16 h-16 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 bg-cover bg-center" style="${imgStyle}"></div>
                    <div class="flex-1 flex flex-col justify-between min-w-0">
                        <div class="flex justify-between items-start gap-2">
                            <p class="text-sm font-bold text-gray-900 truncate">${(item.name || '').replace(/</g, '&lt;')}</p>
                            <p class="text-sm font-bold text-gray-900 shrink-0">${CART.formatPrice(lineTotal, symbol)}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-1">
                            <button type="button" class="cart-qty-minus text-gray-500 hover:text-primary transition-colors p-1" data-item-id="${item.id}" aria-label="Decrease">
                                ${cartIcon('minus', 18, '')}
                            </button>
                            <span class="text-xs font-medium text-gray-900 min-w-[1.5rem] text-center">${item.quantity || 1}</span>
                            <button type="button" class="cart-qty-plus text-gray-500 hover:text-primary transition-colors p-1" data-item-id="${item.id}" aria-label="Increase">
                                ${cartIcon('plus', 18, '')}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const contentEl = modalEl.querySelector('#resmenu-cart-modal-content');
        if (!contentEl) return;

        contentEl.innerHTML = `
            <div class="flex flex-col h-full min-h-0">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-xl font-bold text-gray-900">Shopping Bag</h3>
                    <button type="button" id="resmenu-cart-modal-close" class="p-2 text-gray-500 hover:text-primary transition-colors" aria-label="Close">
                        ${cartIcon('x', 24, '')}
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6 min-h-0">
                    ${items.length ? itemsHtml : '<p class="text-gray-600 py-8 text-center">Your bag is empty.</p>'}
                </div>
                <div class="p-6 border-t border-gray-200 bg-white flex-shrink-0">
                    <div class="flex flex-col gap-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-900">${CART.formatPrice(subtotal, symbol)}</span>
                        </div>
                        ${deliveryFee > 0 ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery Fee</span>
                            <span class="font-medium text-gray-900">${CART.formatPrice(deliveryFee, symbol)}</span>
                        </div>
                        ` : ''}
                        ${tax > 0 ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium text-gray-900">${CART.formatPrice(tax, symbol)}</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="flex justify-between items-end pt-4 border-t border-dashed border-gray-200 mb-4">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-bold" style="color:${primaryColor}">${CART.formatPrice(total, symbol)}</span>
                    </div>
                    <div class="flex gap-3 items-stretch">
                        <button type="button" id="resmenu-cart-continue" class="flex-1 flex items-center justify-center px-4 py-3 rounded-lg border border-gray-200 text-gray-700 font-bold hover:bg-gray-100 transition-colors text-center">
                            Close cart
                        </button>
                        <a href="${checkoutUrl}" id="resmenu-cart-checkout" class="flex-1 flex items-center justify-center px-4 py-3 rounded-lg text-white font-bold text-center transition-colors" style="background-color:${primaryColor}" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            Checkout
                        </a>
                    </div>
                </div>
            </div>
        `;

        contentEl.querySelectorAll('.cart-qty-minus').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-item-id');
                CART.updateQuantity(slug, id, -1);
            });
        });
        contentEl.querySelectorAll('.cart-qty-plus').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-item-id');
                CART.updateQuantity(slug, id, 1);
            });
        });

        const closeBtn = contentEl.querySelector('#resmenu-cart-modal-close');
        if (closeBtn) closeBtn.addEventListener('click', close);

        const continueBtn = contentEl.querySelector('#resmenu-cart-continue');
        if (continueBtn) continueBtn.addEventListener('click', close);

        if (overlay) {
            overlay.onclick = function (e) {
                if (e.target === overlay) close();
            };
        }
    }

    function init(cfg) {
        config = cfg || {};
        if (!config.restaurantSlug) return;

        overlay = document.getElementById('resmenu-cart-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'resmenu-cart-overlay';
            overlay.className = 'fixed inset-0 bg-black/50 z-[100] hidden';
            document.body.appendChild(overlay);
        }

        modalEl = document.getElementById('resmenu-cart-modal');
        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'resmenu-cart-modal';
            modalEl.className = 'fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-lg md:max-h-[90vh] bg-white rounded-xl shadow-xl z-[101] overflow-hidden hidden';
            modalEl.innerHTML = '<div id="resmenu-cart-modal-content" class="h-full"></div>';
            document.body.appendChild(modalEl);
        }

        window.addEventListener(CART.CART_UPDATE_EVENT, function (e) {
            if (e.detail && e.detail.restaurantSlug === config.restaurantSlug && modalEl && !modalEl.classList.contains('hidden')) {
                render();
            }
        });
    }

    window.RESMENU_CART_MODAL = {
        init,
        open,
        close
    };
})(window);
