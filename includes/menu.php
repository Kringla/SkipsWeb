<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!defined('BASE_URL')) {
  // BASE_URL should be defined in your config/constants and point to your web root for SkipsWeb (e.g. /skipsweb)
  // We intentionally avoid guessing to prevent broken links from subfolders.
  throw new \RuntimeException('BASE_URL is not defined. Define it (e.g. /skipsweb) before including menu.php.');
}
$BASE = rtrim(BASE_URL, '/');
?>
<nav class="container navbar">
  <a href="<?= $BASE ?>/dashboard.php">Dashboard</a>
  <a href="<?= $BASE ?>/user/fartoy_nat.php">Søk fartøy</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="<?= $BASE ?>/logout.php">Logg ut</a>
  <?php else: ?>
    <a href="<?= $BASE ?>/login.php">Logg inn</a>
  <?php endif; ?>
</nav>
