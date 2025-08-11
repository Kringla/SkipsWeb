<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$BASE = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/menu.php';

$loggedIn = !empty($_SESSION['user_id']);
?>
<?php if ($loggedIn): ?>
  <div class="container mt-3">
    <div class="card">
      <strong>Innlogget.</strong> Gå til <a href="<?= $BASE ?>/dashboard.php">Dashboard</a>.
    </div>
  </div>
<?php endif; ?>

<section class="hero hero-rotator" 
         data-images='["<?= $BASE ?>/assets/img/hero1.jpg","<?= $BASE ?>/assets/img/hero2.jpg","<?= $BASE ?>/assets/img/hero3.jpg"]'>
  <div class="hero-overlay"></div>
  <div class="container hero-inner">
    <h1>Finn skipsdata raskt og enkelt</h1>
    <p>SkipsWeb gir et lett grensesnitt for søk og lesing (og for admin: endring) i SkipDB. Logg inn for å starte.</p>
    <div class="cta">
      <?php if (!$loggedIn): ?>
        <a class="btn primary" href="<?= $BASE ?>/login.php">Logg inn</a>
        <a class="btn" href="<?= $BASE ?>/register.php">Opprett bruker</a>
      <?php else: ?>
        <a class="btn primary" href="<?= $BASE ?>/dashboard.php">Til Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="container grid cols-2 mt-4">
  <div class="card">
    <h2>Søk i fartøy</h2>
    <p class="muted">Filtrer på navn og nasjon, klikk for detaljer med navnehistorikk og spesifikasjoner.</p>
  </div>
  <div class="card">
    <h2>Rent og raskt</h2>
    <p class="muted">Ingen tunge rammeverk. Kun det vi trenger, når vi trenger det.</p>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
