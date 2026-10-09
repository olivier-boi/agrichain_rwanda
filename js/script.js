/* AgriChain Rwanda - JavaScript (plain, beginner friendly) */
document.addEventListener('DOMContentLoaded', function () {

  /* 1. Mobile navigation + dashboard sidebar toggle */
  var navToggle = document.getElementById('navToggle');
  var navMenu = document.getElementById('navMenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () { navMenu.classList.toggle('open'); });
  }
  var sideToggle = document.getElementById('sideToggle');
  var sidebar = document.getElementById('sidebar');
  if (sideToggle && sidebar) {
    sideToggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
  }

  /* 2. Toast notification (message comes from PHP flash message) */
  var toastData = document.getElementById('toastData');
  if (toastData) {
    var toast = document.createElement('div');
    toast.className = 'toast ' + (toastData.dataset.type === 'error' ? 'error' : 'success');
    toast.textContent = toastData.dataset.msg;
    document.body.appendChild(toast);
    setTimeout(function () { toast.style.opacity = '0'; toast.style.transition = 'opacity .5s'; }, 4000);
    setTimeout(function () { toast.remove(); }, 4600);
  }

  /* 3. Animated counters: <div data-count="25"> */
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseInt(el.dataset.count, 10) || 0;
    var current = 0;
    var step = Math.max(1, Math.ceil(target / 40));
    var timer = setInterval(function () {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = current.toLocaleString();
    }, 30);
  });

  /* 4. Confirmation dialogs: <button data-confirm="Are you sure?"> */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.dataset.confirm)) { e.preventDefault(); }
    });
  });

  /* 5. Marketplace search + filters (runs in the browser, no page reload) */
  var grid = document.getElementById('productGrid');
  if (grid) {
    var cards = Array.from(grid.querySelectorAll('.product-card'));
    var fSearch = document.getElementById('fSearch'), fCategory = document.getElementById('fCategory'),
        fLocation = document.getElementById('fLocation'), fGrade = document.getElementById('fGrade'),
        fPrice = document.getElementById('fPrice');
    function applyFilters() {
      var text = fSearch.value.trim().toLowerCase(), shown = 0;
      cards.forEach(function (card) {
        var ok = card.dataset.name.indexOf(text) !== -1
          && (!fCategory.value || card.dataset.category === fCategory.value)
          && (!fLocation.value || card.dataset.location === fLocation.value)
          && (!fGrade.value || card.dataset.grade === fGrade.value)
          && (!fPrice.value || parseFloat(card.dataset.price) <= parseFloat(fPrice.value));
        card.style.display = ok ? '' : 'none';
        if (ok) shown++;
      });
      document.getElementById('resultCount').textContent = shown;
      document.getElementById('emptyMsg').hidden = shown !== 0;
    }
    [fSearch, fCategory, fLocation, fGrade, fPrice].forEach(function (f) {
      f.addEventListener('input', applyFilters);
      f.addEventListener('change', applyFilters);
    });
    document.getElementById('fReset').addEventListener('click', function () {
      [fSearch, fCategory, fLocation, fGrade, fPrice].forEach(function (f) { f.value = ''; });
      applyFilters();
    });
  }

  /* 6. Order total:  Total = Quantity x Price */
  var qty = document.getElementById('qty'), unit = document.getElementById('unitPrice'), total = document.getElementById('orderTotal');
  if (qty && unit && total) {
    qty.addEventListener('input', function () {
      var q = parseFloat(qty.value) || 0;
      total.textContent = (q * parseFloat(unit.dataset.price)).toLocaleString();
    });
  }

  /* 7. Quality grade calculator (same rule as calc_grade() in PHP)
        A: average score >= 8 and damage <= 5 | B: average >= 6 and damage <= 12 | otherwise C */
  var sA = document.getElementById('sAppearance'), sS = document.getElementById('sSize'),
      sC = document.getElementById('sClean'), sD = document.getElementById('sDamage'),
      result = document.getElementById('gradeResult');
  if (sA && sS && sC && sD && result) {
    function showGrade() {
      if (!sA.value || !sS.value || !sC.value || sD.value === '') { result.textContent = '-'; result.className = 'grade-big'; return; }
      var avg = (parseFloat(sA.value) + parseFloat(sS.value) + parseFloat(sC.value)) / 3;
      var dmg = parseFloat(sD.value), grade = 'C';
      if (avg >= 8 && dmg <= 5) grade = 'A'; else if (avg >= 6 && dmg <= 12) grade = 'B';
      result.textContent = grade; result.className = 'grade-big ' + grade;
      document.querySelectorAll('#gradeCards .grade-card').forEach(function (c) { c.classList.toggle('highlight', c.dataset.grade === grade); });
    }
    [sA, sS, sC, sD].forEach(function (i) { i.addEventListener('input', showGrade); });
  }
  var insProduct = document.getElementById('insProduct'), insFarmer = document.getElementById('insFarmer');
  if (insProduct && insFarmer) {
    insProduct.addEventListener('change', function () {
      var opt = insProduct.options[insProduct.selectedIndex];
      insFarmer.value = opt.dataset.farmer || '';
    });
  }

  /* 8. Supply-chain progress bar: fills up to the current stage */
  document.querySelectorAll('.timeline').forEach(function (tl) {
    var stage = parseInt(tl.dataset.stage, 10) || 1;
    var percent = ((stage - 1) / 6) * 86;                        // 7 stages = 6 gaps; the line spans 86% of the width
    setTimeout(function () { tl.style.setProperty('--progress', percent + '%'); }, 200);
  });

  /* 9. Form validation (forms with the data-validate attribute) */
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var valid = true;
      form.querySelectorAll('.field-error').forEach(function (m) { m.remove(); });
      form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.classList.remove('invalid');
        if (field.type === 'hidden' || field.readOnly || field.type === 'file') return;
        if (!field.checkValidity()) {
          valid = false;
          field.classList.add('invalid');
          var msg = document.createElement('span');
          msg.className = 'field-error';
          msg.textContent = field.validationMessage || 'Please check this field.';
          field.insertAdjacentElement('afterend', msg);
        }
      });
      var pw1 = form.querySelector('#pw1'), pw2 = form.querySelector('#pw2');
      if (pw1 && pw2 && pw1.value !== pw2.value) {
        valid = false; pw2.classList.add('invalid');
        var m2 = document.createElement('span'); m2.className = 'field-error'; m2.textContent = 'Passwords do not match.';
        pw2.insertAdjacentElement('afterend', m2);
      }
      if (!valid) { e.preventDefault(); var first = form.querySelector('.invalid'); if (first) first.focus(); }
    });
  });
});
