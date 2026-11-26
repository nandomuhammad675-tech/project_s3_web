<?php /** Kartu satu guru. Butuh: $g (nama, nip, jabatan, foto), $kls (kelas CSS). Email/telepon tidak pernah tersedia di sini. */ ?>
<figure class="<?= esc($kls) ?>">
    <img src="<?= esc(sikesa_img($g['foto'], 'img/guru-default.svg')) ?>" alt="Foto <?= esc($g['nama']) ?>" loading="lazy" width="240" height="320">
    <figcaption>
        <strong><?= esc($g['nama']) ?></strong>
        <?php if ($g['jabatan'] !== ''): ?><em><?= esc($g['jabatan']) ?></em><?php endif; ?>
        <?php if ($g['nip'] !== ''): ?><span>NIP. <?= esc($g['nip']) ?></span><?php endif; ?>
    </figcaption>
</figure>
