'use strict';

/* ── Scroll Reveal ────────────────────────────────────────── */
(function () {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;
  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('revealed');
          obs.unobserve(e.target);
        }
      });
    },
    { threshold: 0.08, rootMargin: '0px 0px -36px 0px' }
  );
  els.forEach((el, i) => {
    el.style.transitionDelay = (i % 4) * 0.08 + 's';
    obs.observe(el);
  });
})();

/* ── Mobile Nav Toggle ────────────────────────────────────── */
(function () {
  const toggle = document.getElementById('nav-toggle');
  const menu   = document.getElementById('nav-menu');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => {
    const open = menu.classList.toggle('open');
    toggle.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open);
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      menu.classList.remove('open');
      toggle.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
})();

/* ── Active Nav Link on Scroll ───────────────────────────── */
(function () {
  const sections = document.querySelectorAll('section[id]');
  const links    = document.querySelectorAll('.nav__link[href^="#"], .nav__link[href*="#"]');
  if (!sections.length || !links.length) return;

  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          const id = e.target.id;
          links.forEach((l) => {
            const href = l.getAttribute('href');
            l.classList.toggle('active', href === '#' + id || href.endsWith('#' + id));
          });
        }
      });
    },
    { threshold: 0.35 }
  );
  sections.forEach((sec) => obs.observe(sec));
})();

/* ── Counter Animation ────────────────────────────────────── */
(function () {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length) return;

  function animateCounter(el) {
    const target   = parseInt(el.dataset.count, 10);
    const duration = 1100;
    const start    = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const ease     = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      el.textContent = Math.round(ease * target);
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          animateCounter(e.target);
          obs.unobserve(e.target);
        }
      });
    },
    { threshold: 0.5 }
  );
  counters.forEach((el) => obs.observe(el));
})();

/* ── Client-side Event Filter ────────────────────────────── */
(function () {
  const btns  = document.querySelectorAll('[data-filter]');
  const cards = document.querySelectorAll('[data-type]');
  if (!btns.length || !cards.length) return;

  btns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const filter = btn.dataset.filter;
      btns.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      cards.forEach((c) => {
        c.style.display = (filter === 'all' || c.dataset.type === filter) ? '' : 'none';
      });
    });
  });
})();
