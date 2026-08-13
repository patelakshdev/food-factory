// Lightweight fetch-based cart interactions. Progressive enhancement over
// plain form posts, so the app still works with JS disabled.

function ffToast(message, isError = false) {
    let el = document.getElementById('ff-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'ff-toast';
        el.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;padding:12px 18px;border-radius:8px;color:#fff;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.2);transition:opacity .3s;';
        document.body.appendChild(el);
    }
    el.style.background = isError ? '#b3261e' : '#1e7b34';
    el.textContent = message;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; }, 2500);
}

function ffCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function ffUpdateCartBadge(count) {
    const link = document.querySelector('.cart-link');
    if (!link) return;
    let badge = link.querySelector('.cart-badge');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            link.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

document.addEventListener('click', async (evt) => {
    const btn = evt.target.closest('[data-add-to-cart]');
    if (!btn) return;
    evt.preventDefault();
    btn.disabled = true;

    const payload = {
        menu_item_id: btn.dataset.itemId,
        variant_id: btn.dataset.variantId || null,
        quantity: 1,
        csrf_token: ffCsrfToken(),
    };

    try {
        const res = await fetch('/api/cart/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            ffToast('Added to cart');
            ffUpdateCartBadge(data.cart_count);
        } else {
            ffToast(data.message || 'Could not add item', true);
        }
    } catch (err) {
        ffToast('Network error — please try again', true);
    } finally {
        btn.disabled = false;
    }
});
