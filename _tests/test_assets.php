<?php
// Self-contained page to verify CSS + hero JS wiring.
// Place this in the project root beside index.php, then open /skipsweb/test_assets.php in your browser.
require_once __DIR__ . '/includes/header_auto.php';
$BASE = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<main class="container" style="margin-top:16px">
  <div class="card">
    <h1>Assets-diagnose</h1>
    <p>Hvis boksen under er hvit med skygge og blå lenker, er <strong>CSS</strong> lastet ✅</p>
    <ul>
      <li>BASE_URL oppfattet som: <code><?= htmlspecialchars($BASE) ?></code></li>
      <li><a href="<?= $BASE ?>/assets/css/app.css?v=1" target="_blank">Åpne CSS</a></li>
      <li><a href="<?= $BASE ?>/assets/js/hero-rotator.js" target="_blank">Åpne hero JS</a></li>
    </ul>
  </div>
</main>

<section class="hero hero-rotator" 
         data-images='["<?= $BASE ?>/assets/img/hero1.jpg","<?= $BASE ?>/assets/img/hero2.jpg"]'
         style="margin-top:16px">
  <div class="hero-overlay"></div>
  <div class="container hero-inner">
    <h2>Hero-rotator test</h2>
    <p class="muted">Hvis du har lagt inn bilder i <code>assets/img/hero*.jpg</code>, skal disse crossfade her.</p>
    <div class="cta">
      <a class="btn primary" href="#">Primær knapp</a>
      <a class="btn" href="#">Sekundær knapp</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="<?= $BASE ?>/assets/js/hero-rotator.js"></script>
