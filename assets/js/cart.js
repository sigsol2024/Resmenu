/**
 * Resmenu Cart API
 * Manages cart state in sessionStorage for restaurant ordering
 * Cart key: cart_{restaurant_slug}
 */

(function (window) {
    'use strict';
    var PATHS = {
        'shopping-bag': '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        plus: '<path d="M5 12h14"/><path d="M12 5v14"/>',
        minus: '<path d="M5 12h14"/>',
        x: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'
    };
    var ALIASES = { shopping_bag: 'shopping-bag', remove: 'minus', add: 'plus', close: 'x' };
    window.RESMENU_ICONS = {
        icon: function (name, opts) {
            opts = opts || {};
            var resolved = ALIASES[name] || name;
            var path = PATHS[resolved];
            if (!path) return '';
            var size = opts.size || 24;
            var cls = 'resmenu-icon resmenu-icon--' + resolved + (opts.className ? ' ' + opts.className : '');
            return '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '" aria-hidden="true">' + path + '</svg>';
        }
    };
})(window);

(function (window) {
    'use strict';

    const STORAGE_PREFIX = 'cart_';
    const CART_UPDATE_EVENT = 'resmenu:cart-update';

    function getCartKey(restaurantSlug) {
        return STORAGE_PREFIX + (restaurantSlug || '').toString();
    }

    function getCart(restaurantSlug) {
        try {
            const key = getCartKey(restaurantSlug);
            const raw = sessionStorage.getItem(key);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setCart(restaurantSlug, items) {
        const key = getCartKey(restaurantSlug);
        sessionStorage.setItem(key, JSON.stringify(items));
        window.dispatchEvent(new CustomEvent(CART_UPDATE_EVENT, { detail: { restaurantSlug, items } }));
    }

    function addItem(restaurantSlug, item, quantity = 1) {
        const items = getCart(restaurantSlug);
        const id = parseInt(item.id, 10);
        const existing = items.find(i => parseInt(i.id, 10) === id);
        if (existing) {
            existing.quantity = (existing.quantity || 1) + quantity;
        } else {
            items.push({
                id: id,
                name: item.name || '',
                price: parseFloat(item.price) || 0,
                image: item.image || '',
                quantity: quantity
            });
        }
        setCart(restaurantSlug, items);
        return items;
    }

    function updateQuantity(restaurantSlug, itemId, delta) {
        const items = getCart(restaurantSlug);
        const id = parseInt(itemId, 10);
        const idx = items.findIndex(i => parseInt(i.id, 10) === id);
        if (idx < 0) return items;
        items[idx].quantity = Math.max(0, (items[idx].quantity || 1) + delta);
        if (items[idx].quantity <= 0) {
            items.splice(idx, 1);
        }
        setCart(restaurantSlug, items);
        return items;
    }

    function removeItem(restaurantSlug, itemId) {
        const items = getCart(restaurantSlug).filter(i => parseInt(i.id, 10) !== parseInt(itemId, 10));
        setCart(restaurantSlug, items);
        return items;
    }

    function clearCart(restaurantSlug) {
        setCart(restaurantSlug, []);
    }

    function getTotalCount(restaurantSlug) {
        return getCart(restaurantSlug).reduce((sum, i) => sum + (i.quantity || 1), 0);
    }

    function getTotalAmount(restaurantSlug) {
        return getCart(restaurantSlug).reduce((sum, i) => sum + (parseFloat(i.price) || 0) * (i.quantity || 1), 0);
    }

    function formatPrice(amount, symbol = '₦') {
        const n = parseFloat(amount) || 0;
        let str = n.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (str.endsWith('.00')) {
            str = str.slice(0, -3);
        }
        return symbol + str;
    }

    window.RESMENU_CART = {
        getCart,
        addItem,
        updateQuantity,
        removeItem,
        clearCart,
        getTotalCount,
        getTotalAmount,
        formatPrice,
        CART_UPDATE_EVENT,
        getCartKey
    };
})(window);
