/* script.js — Add to Cart with toast feedback */
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!csrf) return;

  document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.itemId;
      if (!id || btn.disabled) return;
      btn.disabled = true;
      const orig = btn.textContent;
      btn.textContent = 'Adding…';
      try {
        const res = await fetch('/api/cart/add.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ menu_item_id: id, quantity: 1, csrf_token: csrf }),
        });
        const data = await res.json();
        if (data.success) {
          btn.textContent = '✓ Added';
          btn.style.background = 'linear-gradient(135deg,#22c55e,#16a34a)';
          if (typeof showToast === 'function') showToast('Item added to cart!', 'success');
          // Update cart badge
          const badge = document.querySelector('.cart-badge');
          if (badge && data.cart_count !== undefined) {
            badge.textContent = data.cart_count;
            badge.style.display = 'inline';
          }
          setTimeout(() => {
            btn.disabled = false;
            btn.textContent = orig;
            btn.style.background = '';
          }, 2000);
        } else {
          if (typeof showToast === 'function') showToast(data.message || 'Could not add item.', 'error');
          btn.disabled = false;
          btn.textContent = orig;
        }
      } catch {
        if (typeof showToast === 'function') showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = orig;
      }
    });
  });
});