/**
 * Filter sidebar UX: auto-submit on change (debounced) so users see results
 * immediately without hunting the "Áp dụng" button. Falls back to manual submit
 * when JS disabled.
 */
(function () {
  'use strict';
  var form = document.querySelector('.aib-filter form');
  if (!form) return;

  var t = null;
  function submitDebounced() {
    clearTimeout(t);
    t = setTimeout(function () { form.submit(); }, 350);
  }

  form.addEventListener('change', function (e) {
    var target = e.target;
    if (target && (target.type === 'checkbox' || target.type === 'number')) {
      submitDebounced();
    }
  });
})();
