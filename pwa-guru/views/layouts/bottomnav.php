<?php
/**
 * Layout: bottomnav — navigasi bawah untuk ponsel (< 900px).
 * Item diambil dari $nav di index.php (yang bertanda 'bawah' => true).
 */
?>
<nav class="bottomnav" aria-label="Navigasi bawah">
<?php foreach ($nav as $m): if (empty($m['bawah'])) { continue; } ?>
  <a class="bn-item<?= $m['key'] === $activePage ? ' is-active' : '' ?>"
     href="<?= e($root) ?>index.php?page=<?= e($m['key']) ?>"
     <?= $m['key'] === $activePage ? 'aria-current="page"' : '' ?>>
    <?= icon($m['icon']) ?>
    <span><?= e($m['label']) ?></span>
  </a>
<?php endforeach; ?>
</nav>