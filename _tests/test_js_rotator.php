<?php
require_once __DIR__ . '/includes/header_auto.php';
$BASE = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<link href="<?= $BASE ?>/assets/css/app.css?v=1" rel="stylesheet">
<section class="hero hero-rotator" 
         data-images='["<?= $BASE ?>/assets/img/hero1.jpg","<?= $BASE ?>/assets/img/hero2.jpg","<?= $BASE ?>/assets/img/hero3.jpg"]'
         style="margin-top:16px">
  <div class="hero-overlay"></div>
  <div class="container hero-inner">
    <h2>test_js_rotator.php</h2>
    <p class="muted">Åpne nettleserkonsollen (F12). Du skal se loggmeldinger fra rotatoren, og bildet skal fade hvert ~6s.</p>
    <div class="cta">
      <a class="btn primary" href="#">Primær</a>
      <a class="btn" href="#">Sekundær</a>
    </div>
  </div>
</section>
<script>
console.log("Rotator test: script tag present. Loading hero-rotator.js...");
</script>
<script src="<?= $BASE ?>/assets/js/hero-rotator.js" onload="console.log('hero-rotator.js loaded');" onerror="console.error('hero-rotator.js failed to load')"></script>
