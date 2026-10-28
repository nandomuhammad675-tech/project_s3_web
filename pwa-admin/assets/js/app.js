/* SI-KESA PWA Admin | kerangka: sidebar, pencarian menu, avatar, toast */
(function () {
  'use strict';
  var Auth = window.SikesaAuth;
  if (!Auth || (window.SIKESA.requireAuth && !Auth.token())) return;

  document.body.classList.add('is-ready');

  /* Toast */
  window.SikesaUI = {
    toast: function (text, type) {
      var stack = document.getElementById('toast-stack');
      if (!stack) return;
      var el = document.createElement('div');
      el.className = 'toast' + (type === 'error' ? ' toast--error' : '');
      el.textContent = text;
      stack.appendChild(el);
      setTimeout(function () { el.remove(); }, 4500);
    }
  };

  /* Avatar: inisial dari nama/username */
  var av = document.getElementById('avatar');
  var u = Auth.user();
  if (av && u) {
    var name = (u.nama || u.username || 'Admin').trim();
    var parts = name.split(/\s+/);
    av.textContent = ((parts[0] || '')[0] + ((parts[1] || parts[0] || '')[1] || '')).toUpperCase().slice(0, 2);
    av.title = name;
  }

  /* Sidebar: buka-tutup submenu */
  document.querySelectorAll('[data-toggle-sub]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var sub = document.getElementById(btn.getAttribute('data-toggle-sub'));
      var open = !sub.classList.contains('is-open');
      sub.classList.toggle('is-open', open);
      btn.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
    });
  });

  /* Sidebar versi layar kecil */
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.querySelector('.sidebar-backdrop');
  function setSidebar(open) {
    sidebar.classList.toggle('is-open', open);
    backdrop.hidden = !open;
  }
  document.querySelectorAll('[data-sidebar-open]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(true); }); });
  document.querySelectorAll('[data-sidebar-close]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(false); }); });

  /* Keluar */
  document.querySelectorAll('[data-action="logout"]').forEach(function (b) {
    b.addEventListener('click', function () { Auth.logout(); });
  });

  /* Pencarian menu */
  var input = document.getElementById('global-search');
  var list = document.getElementById('search-results');
  if (input && list) {
    var items = Array.from(document.querySelectorAll('.sidebar a[href]')).map(function (a) {
      var parentBtn = a.closest('.nav-sub') && a.closest('.nav-sub').previousElementSibling;
      return {
        label: a.textContent.trim(),
        group: parentBtn ? parentBtn.querySelector('.nav-item__text').textContent.trim() : '',
        href: a.getAttribute('href')
      };
    });
    var focusIdx = -1;

    function render(q) {
      q = q.trim().toLowerCase();
      list.innerHTML = '';
      focusIdx = -1;
      if (!q) { list.hidden = true; input.setAttribute('aria-expanded', 'false'); return; }
      var hits = items.filter(function (i) { return (i.label + ' ' + i.group).toLowerCase().indexOf(q) > -1; }).slice(0, 8);
      if (!hits.length) {
        var li = document.createElement('li');
        li.className = 'search__empty';
        li.textContent = 'Menu tidak ditemukan';
        list.appendChild(li);
      } else {
        hits.forEach(function (h) {
          var li = document.createElement('li');
          var a = document.createElement('a');
          a.href = h.href; a.textContent = h.label;
          if (h.group) { var s = document.createElement('small'); s.textContent = h.group; a.appendChild(s); }
          li.appendChild(a); list.appendChild(li);
        });
      }
      list.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }
    input.addEventListener('input', function () { render(input.value); });
    input.addEventListener('keydown', function (ev) {
      var links = Array.from(list.querySelectorAll('a'));
      if (ev.key === 'ArrowDown' || ev.key === 'ArrowUp') {
        ev.preventDefault();
        if (!links.length) return;
        focusIdx = (focusIdx + (ev.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length;
        links.forEach(function (l, i) { l.classList.toggle('is-focus', i === focusIdx); });
      } else if (ev.key === 'Enter') {
        var t = links[Math.max(focusIdx, 0)];
        if (t) window.location.href = t.href;
      } else if (ev.key === 'Escape') { render(''); input.blur(); }
    });
    document.addEventListener('click', function (ev) {
      if (!ev.target.closest('.search')) render('');
    });
  }

  /* Lonceng: lompat ke peringatan di dasbor */
  var bell = document.getElementById('btn-bell');
  if (bell) {
    bell.addEventListener('click', function () {
      var a = document.getElementById('alerts');
      if (a && a.children.length) a.scrollIntoView({ behavior: 'smooth', block: 'center' });
      else window.SikesaUI.toast('Tidak ada pemberitahuan baru');
    });
  }
})();