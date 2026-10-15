<?php
/**
 * Layout: footer — menutup <div class="main"> dan <div class="app">, lalu memuat script bersama:
 * apiClient.js -> skrip layout (nama pengguna, menu avatar, keluar) -> script halaman.
 */
?>
  </div><!-- /.main -->
</div><!-- /.app -->

<script src="<?= e(asset('js/apiClient.js')) ?>"></script>
<script>
/* Skrip layout: sidebar + topbar */
(function () {
    'use strict';
    var S = window.SIKESA;

    function inisial(nama) {
        var k = String(nama || '').trim().split(/\s+/).filter(Boolean);
        if (!k.length) { return '-'; }
        return (k[0][0] + (k[1] ? k[1][0] : '')).toUpperCase();
    }
    function isi(selector, teks) {
        document.querySelectorAll(selector).forEach(function (el) { el.textContent = teks; });
    }

    /* Dipanggil juga oleh skrip halaman setelah data guru/kelas dimuat */
    S.layout = {
        setProfil: function (p) {
            p = p || {};
            if (p.nama)  { isi('[data-user-name]', p.nama); isi('[data-user-initials]', inisial(p.nama)); }
            if (p.peran) { isi('[data-user-role]', p.peran); }
        }
    };

    var u = S.getUser() || {};
    S.layout.setProfil({ nama: u.nama_guru || u.nama || u.username || 'Guru', peran: u.jabatan || 'Guru' });

    /* Keluar */
    document.querySelectorAll('[data-logout]').forEach(function (b) {
        b.addEventListener('click', function () { S.logout(); });
    });

    /* Menu avatar */
    var btn = document.getElementById('btnAvatar');
    var menu = document.getElementById('menuAvatar');
    function tutup() { menu.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    btn.addEventListener('click', function (ev) {
        ev.stopPropagation();
        var buka = menu.hidden;
        menu.hidden = !buka;
        btn.setAttribute('aria-expanded', String(buka));
    });
    document.addEventListener('click', function (ev) { if (!menu.contains(ev.target)) { tutup(); } });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { tutup(); btn.focus(); } });
})();
</script>
<?php foreach ((array) ($extraScripts ?? []) as $js): if (is_file(__DIR__ . '/../../assets/js/' . $js) && filesize(__DIR__ . '/../../assets/js/' . $js) > 0): ?>
<script src="<?= e(asset('js/' . $js)) ?>"></script>
<?php endif; endforeach; ?>
</body>
</html>