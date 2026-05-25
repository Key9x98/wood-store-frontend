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

  /* --- Product image gallery ----------------------------------------- */
  var galleryEl = document.getElementById('fb-gallery');
  if (galleryEl) {
    var dataEl    = document.getElementById('fb-gallery-data');
    var images    = dataEl ? JSON.parse(dataEl.textContent || '[]') : [];
    var total     = images.length;
    var current   = 0;
    var zoomScale = 1;
    var panX      = 0;
    var panY      = 0;
    var dragging  = false;
    var dragSX    = 0;
    var dragSY    = 0;
    var dragPX    = 0;
    var dragPY    = 0;

    var mainEl    = document.getElementById('fb-pg-main');
    var thumbsEl  = document.getElementById('fb-pg-thumbs');
    var counterEl = document.getElementById('fb-pg-counter');
    var prevBtn   = document.getElementById('fb-pg-prev');
    var nextBtn   = document.getElementById('fb-pg-next');
    var ziBtn     = document.getElementById('fb-pg-zoom-in');
    var zoBtn     = document.getElementById('fb-pg-zoom-out');
    var zrBtn     = document.getElementById('fb-pg-reset');
    var fsBtn     = document.getElementById('fb-pg-fullscreen');

    // Lightbox
    var lb        = document.getElementById('fb-lightbox');
    var lbImg     = document.getElementById('fb-lb-img');
    var lbCur     = document.getElementById('fb-lb-cur');
    var lbClose   = document.getElementById('fb-lb-close');
    var lbPrev    = document.getElementById('fb-lb-prev');
    var lbNext    = document.getElementById('fb-lb-next');
    var lbBack    = document.getElementById('fb-lb-backdrop');

    function getSlides() { return mainEl ? mainEl.querySelectorAll('.pg-slide') : []; }
    function getThumbs() { return thumbsEl ? thumbsEl.querySelectorAll('.pg-thumb') : []; }

    function resetZoom() {
      zoomScale = 1; panX = 0; panY = 0;
      var img = getActiveImg();
      if (img) { img.style.transform = ''; img.classList.remove('is-zoomed'); }
    }

    function getActiveImg() {
      var slides = getSlides();
      if (slides[current]) return slides[current].querySelector('.pg-slide__img');
      return null;
    }

    function applyZoom() {
      var img = getActiveImg();
      if (!img) return;
      img.style.transform = 'scale(' + zoomScale + ') translate(' + (panX / zoomScale) + 'px, ' + (panY / zoomScale) + 'px)';
      img.classList.toggle('is-zoomed', zoomScale > 1);
    }

    function goTo(idx) {
      if (idx < 0) idx = total - 1;
      if (idx >= total) idx = 0;
      resetZoom();

      var slides = getSlides();
      var thumbs = getThumbs();

      slides.forEach(function(s, i) { s.classList.toggle('is-active', i === idx); });
      thumbs.forEach(function(t, i) {
        t.classList.toggle('is-active', i === idx);
        t.setAttribute('aria-selected', i === idx ? 'true' : 'false');
      });
      if (counterEl) counterEl.textContent = (idx + 1) + ' / ' + total;

      // Scroll thumb into view
      if (thumbsEl && thumbs[idx]) {
        thumbs[idx].scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
      }
      current = idx;
    }

    // Prev / Next
    if (prevBtn) prevBtn.addEventListener('click', function() { goTo(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function() { goTo(current + 1); });

    // Thumbnails click
    if (thumbsEl) {
      thumbsEl.addEventListener('click', function(e) {
        var btn = e.target.closest('.pg-thumb');
        if (btn) goTo(parseInt(btn.getAttribute('data-index'), 10));
      });
    }

    // Zoom in/out/reset
    if (ziBtn) ziBtn.addEventListener('click', function() {
      zoomScale = Math.min(zoomScale + 0.5, 4);
      applyZoom();
    });
    if (zoBtn) zoBtn.addEventListener('click', function() {
      zoomScale = Math.max(zoomScale - 0.5, 1);
      if (zoomScale === 1) { panX = 0; panY = 0; }
      applyZoom();
    });
    if (zrBtn) zrBtn.addEventListener('click', resetZoom);

    // Wheel zoom on main image
    if (mainEl) {
      mainEl.addEventListener('wheel', function(e) {
        var img = getActiveImg();
        if (!img) return;
        e.preventDefault();
        var delta = e.deltaY < 0 ? 0.25 : -0.25;
        zoomScale = Math.min(Math.max(zoomScale + delta, 1), 4);
        if (zoomScale === 1) { panX = 0; panY = 0; }
        applyZoom();
      }, { passive: false });
    }

    // Click on slide image — open lightbox or start zoom
    if (mainEl) {
      mainEl.addEventListener('click', function(e) {
        var img = e.target.closest('.pg-slide__img');
        if (img && zoomScale === 1 && lb) {
          openLightbox(current);
        }
      });
    }

    // Pan / drag when zoomed
    if (mainEl) {
      mainEl.addEventListener('mousedown', function(e) {
        if (zoomScale <= 1) return;
        var img = getActiveImg();
        if (!img) return;
        dragging = true;
        dragSX = e.clientX; dragSY = e.clientY;
        dragPX = panX; dragPY = panY;
        img.classList.add('is-dragging');
        e.preventDefault();
      });
      document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        panX = dragPX + (e.clientX - dragSX);
        panY = dragPY + (e.clientY - dragSY);
        applyZoom();
      });
      document.addEventListener('mouseup', function() {
        if (!dragging) return;
        dragging = false;
        var img = getActiveImg();
        if (img) img.classList.remove('is-dragging');
      });

      // Touch pan
      var touchStartX = 0, touchStartY = 0, touchPX = 0, touchPY = 0, isTouchPan = false;
      mainEl.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
          touchStartX = e.touches[0].clientX;
          touchStartY = e.touches[0].clientY;
          touchPX = panX; touchPY = panY;
          isTouchPan = zoomScale > 1;
        }
      }, { passive: true });
      mainEl.addEventListener('touchmove', function(e) {
        if (!isTouchPan || e.touches.length !== 1) return;
        panX = touchPX + (e.touches[0].clientX - touchStartX);
        panY = touchPY + (e.touches[0].clientY - touchStartY);
        applyZoom();
        e.preventDefault();
      }, { passive: false });
      // Swipe to change slide (only when not zoomed)
      mainEl.addEventListener('touchend', function(e) {
        if (isTouchPan) { isTouchPan = false; return; }
        var dx = (e.changedTouches[0].clientX - touchStartX);
        if (Math.abs(dx) > 50 && total > 1) {
          goTo(dx < 0 ? current + 1 : current - 1);
        }
      });
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      if (!galleryEl) return;
      if (lb && !lb.hidden) {
        if (e.key === 'ArrowLeft') lbGo(current - 1);
        if (e.key === 'ArrowRight') lbGo(current + 1);
        if (e.key === 'Escape') closeLightbox();
        return;
      }
      if (e.key === 'ArrowLeft' && total > 1) goTo(current - 1);
      if (e.key === 'ArrowRight' && total > 1) goTo(current + 1);
    });

    // Fullscreen button → open lightbox
    if (fsBtn) fsBtn.addEventListener('click', function() { if (lb) openLightbox(current); });

    // ----- Lightbox -----
    function openLightbox(idx) {
      if (!lb || !images[idx]) return;
      lb.hidden = false;
      document.body.style.overflow = 'hidden';
      lbGo(idx);
    }
    function closeLightbox() {
      if (!lb) return;
      lb.hidden = true;
      document.body.style.overflow = '';
    }
    function lbGo(idx) {
      if (idx < 0) idx = total - 1;
      if (idx >= total) idx = 0;
      current = idx;
      if (lbImg && images[idx]) {
        lbImg.src = images[idx].url;
        lbImg.alt = images[idx].alt || '';
      }
      if (lbCur) lbCur.textContent = idx + 1;
      goTo(idx);
    }
    if (lbClose) lbClose.addEventListener('click', closeLightbox);
    if (lbBack)  lbBack.addEventListener('click', closeLightbox);
    if (lbPrev)  lbPrev.addEventListener('click', function() { lbGo(current - 1); });
    if (lbNext)  lbNext.addEventListener('click', function() { lbGo(current + 1); });

    // Double-click to open lightbox from slide
    if (mainEl) {
      mainEl.addEventListener('dblclick', function(e) {
        var img = e.target.closest('.pg-slide__img');
        if (img && lb) openLightbox(current);
      });
    }
  }

  /* --- Quantity control ---------------------------------------------- */
  var qtyControl = document.querySelector('.qty-control');
  if (qtyControl) {
    var qtyInput = qtyControl.querySelector('.qty-input');
    var minusBtn = qtyControl.querySelector('.qty-minus');
    var plusBtn  = qtyControl.querySelector('.qty-plus');

    if (minusBtn) {
      minusBtn.addEventListener('click', function() {
        var val = parseInt(qtyInput.value, 10) || 1;
        if (val > 1) qtyInput.value = val - 1;
      });
    }
    if (plusBtn) {
      plusBtn.addEventListener('click', function() {
        var val = parseInt(qtyInput.value, 10) || 1;
        if (val < 99) qtyInput.value = val + 1;
      });
    }
  }

  /* --- Variant chip selector ----------------------------------------- */
  document.querySelectorAll('.variant-options').forEach(function(wrap) {
    wrap.addEventListener('click', function(e) {
      var chip = e.target.closest('.variant-chip');
      if (!chip) return;
      wrap.querySelectorAll('.variant-chip').forEach(function(c) { c.classList.remove('is-active'); });
      chip.classList.add('is-active');

      // Update label strong
      var label = wrap.previousElementSibling;
      if (label && label.classList.contains('variant-label')) {
        var strong = label.querySelector('strong');
        if (strong) strong.textContent = chip.getAttribute('data-value').toUpperCase();
      }
    });
  });

  /* --- Callback form (mock submit) ----------------------------------- */
  var callbackForm = document.getElementById('fb-callback-form');
  if (callbackForm) {
    callbackForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var phoneInput = callbackForm.querySelector('input[name="phone"]');
      if (phoneInput && phoneInput.value.trim()) {
        alert('Cảm ơn bạn! Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.');
        phoneInput.value = '';
      }
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
