/* ============================================================
   FOOD FACTORY — site.js
   Navbar scroll effect, mobile drawer, password strength,
   star rating, toast notifications, cart interactions
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Toast helper ── */
  const toastContainer = (() => {
    let c = document.getElementById('toast-container');
    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; c.className = 'toast-container'; document.body.appendChild(c); }
    return c;
  })();

  window.showToast = (msg, type = 'success') => {
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span><span>${msg}</span>`;
    toastContainer.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(40px)'; t.style.transition = '0.3s'; setTimeout(() => t.remove(), 300); }, 3000);
  };

  /* ── Navbar scroll effect ── */
  const header = document.querySelector('header');
  if (header) {
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── Mobile drawer ── */
  const toggle    = document.getElementById('menu-toggle');
  const navMenu   = document.getElementById('nav-menu');
  const overlay   = document.getElementById('nav-overlay');

  if (toggle && navMenu) {
    const open  = () => { navMenu.classList.add('active'); overlay && overlay.classList.add('active'); toggle.classList.add('open'); document.body.style.overflow = 'hidden'; };
    const close = () => { navMenu.classList.remove('active'); overlay && overlay.classList.remove('active'); toggle.classList.remove('open'); document.body.style.overflow = ''; };
    toggle.addEventListener('click', () => navMenu.classList.contains('active') ? close() : open());
    overlay && overlay.addEventListener('click', close);
    navMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
  }

  /* ── Hero background animation ── */
  const heroBg = document.querySelector('.hero-bg');
  if (heroBg) setTimeout(() => heroBg.classList.add('loaded'), 100);

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

  /* ── Password strength meter ── */
  const pwdInput = document.getElementById('password-input');
  const pwdFill  = document.getElementById('pwd-strength-fill');
  const pwdText  = document.getElementById('pwd-strength-text');
  if (pwdInput && pwdFill && pwdText) {
    const levels = [
      { score: 0,  label: '',       color: '#374151', w: '0%'   },
      { score: 1,  label: 'Weak',   color: '#ef4444', w: '25%'  },
      { score: 2,  label: 'Fair',   color: '#f97316', w: '50%'  },
      { score: 3,  label: 'Good',   color: '#eab308', w: '75%'  },
      { score: 4,  label: 'Strong', color: '#22c55e', w: '100%' },
    ];
    pwdInput.addEventListener('input', () => {
      const v = pwdInput.value;
      let s = 0;
      if (v.length >= 8) s++;
      if (/[A-Z]/.test(v)) s++;
      if (/[0-9]/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      const lvl = levels[s] || levels[0];
      pwdFill.style.width = lvl.w;
      pwdFill.style.background = lvl.color;
      pwdText.textContent = lvl.label;
      pwdText.style.color = lvl.color;
    });
  }

  /* ── Password confirm match ── */
  const confirmInput = document.getElementById('password-confirm-input');
  if (pwdInput && confirmInput) {
    const check = () => {
      const field = confirmInput.closest('.form-field');
      if (!field) return;
      if (confirmInput.value === '') { field.classList.remove('valid','invalid'); return; }
      const match = confirmInput.value === pwdInput.value;
      field.classList.toggle('valid', match);
      field.classList.toggle('invalid', !match);
      const icon = field.querySelector('.field-icon');
      if (icon) icon.textContent = match ? '✓' : '✗';
    };
    confirmInput.addEventListener('input', check);
    pwdInput.addEventListener('input', check);
  }

  /* ── Password toggle visibility ── */
  document.querySelectorAll('.pwd-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const inp = btn.previousElementSibling || btn.closest('.form-field').querySelector('input');
      if (!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    });
  });

  /* ── Interactive star rating ── */
  const starRating = document.getElementById('star-rating');
  const ratingInput = document.getElementById('rating-value');
  if (starRating && ratingInput) {
    const stars = starRating.querySelectorAll('.star');
    const highlight = n => stars.forEach((s, i) => s.classList.toggle('active', i < n));
    stars.forEach((star, i) => {
      star.addEventListener('mouseover', () => highlight(i + 1));
      star.addEventListener('click', () => { ratingInput.value = i + 1; highlight(i + 1); });
    });
    starRating.addEventListener('mouseleave', () => highlight(parseInt(ratingInput.value) || 0));
  }

  /* ── Floating label: mark input as filled ── */
  document.querySelectorAll('.form-field input').forEach(inp => {
    const update = () => inp.classList.toggle('has-value', inp.value.length > 0);
    inp.addEventListener('input', update);
    update();
  });

  /* ── Order type toggle for checkout ── */
  document.querySelectorAll('.order-type-toggle label').forEach(label => {
    const radio = label.querySelector('input[type="radio"]');
    if (radio) {
      radio.addEventListener('change', () => {
        document.querySelectorAll('.order-type-toggle label').forEach(l => l.classList.remove('selected'));
        if (radio.checked) label.classList.add('selected');
      });
      if (radio.checked) label.classList.add('selected');
    }
  });

  /* ── Smooth counter animation for stats ── */
  const statNums = document.querySelectorAll('.stat-num[data-target]');
  if (statNums.length) {
    const animate = (el) => {
      const target = parseInt(el.dataset.target);
      const suffix = el.dataset.suffix || '';
      const duration = 1800;
      const step = target / (duration / 16);
      let current = 0;
      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = Math.floor(current).toLocaleString() + suffix;
        if (current >= target) clearInterval(timer);
      }, 16);
    };
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { animate(e.target); obs.unobserve(e.target); } });
    }, { threshold: 0.5 });
    statNums.forEach(n => obs.observe(n));
  }

});
