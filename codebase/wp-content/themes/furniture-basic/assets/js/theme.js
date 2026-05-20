/**
 * Furniture Basic — tương tác giao diện.
 */
(function () {
  'use strict';

  var body = document.body;

  /* --- Mobile nav drawer --------------------------------------------- */
  var menuToggle = document.querySelector('.menu-toggle');
  var nav = document.querySelector('.primary-nav');
  var overlay = document.querySelector('.nav-overlay');

  function closeNav() {
    body.classList.remove('nav-open');
    if (menuToggle) { menuToggle.setAttribute('aria-expanded', 'false'); }
  }

  if (menuToggle && nav) {
    menuToggle.addEventListener('click', function () {
      var open = body.classList.toggle('nav-open');
      menuToggle.setAttribute('aria-expanded', String(open));
    });
    nav.addEventListener('click', function (e) {
      var link = e.target.closest('a');
      if (link && !link.parentElement.querySelector('.sub-menu')) { closeNav(); }
    });
  }
  if (overlay) { overlay.addEventListener('click', closeNav); }

  /* --- Header search bar --------------------------------------------- */
  var searchToggle = document.querySelector('.search-toggle');
  if (searchToggle) {
    searchToggle.addEventListener('click', function () {
      var open = body.classList.toggle('search-open');
      searchToggle.setAttribute('aria-expanded', String(open));
      if (open) {
        var input = document.querySelector('.header-search-bar input[type="search"]');
        if (input) { setTimeout(function () { input.focus(); }, 280); }
      }
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeNav();
      body.classList.remove('search-open');
    }
  });

  /* --- Sticky header + back-to-top ----------------------------------- */
  var header = document.querySelector('.site-header');

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (header) { header.classList.toggle('is-scrolled', y > 16); }
    body.classList.toggle('scrolled-down', y > 480);
  }
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  var toTop = document.querySelector('.to-top');
  if (toTop) {
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* --- Shop sort: tự submit ------------------------------------------ */
  var sort = document.querySelector('.shop-sort select');
  if (sort) {
    sort.addEventListener('change', function () {
      var url = new URL(window.location.href);
      if (sort.value) {
        url.searchParams.set('orderby', sort.value);
      } else {
        url.searchParams.delete('orderby');
      }
      url.searchParams.delete('paged');
      window.location.href = url.toString();
    });
  }

  /* --- Reveal khi cuộn ----------------------------------------------- */
  var revealItems = document.querySelectorAll('[data-reveal]');
  if (revealItems.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealItems.forEach(function (el) { io.observe(el); });
  }
})();
