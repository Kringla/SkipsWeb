<?php
require_once __DIR__ . '/includes/header_auto.php';
// EDIT THE PATH BELOW if your site is not under /skipsweb
$ABS = '/skipsweb';
?>
<link href="<?= $ABS ?>/assets/css/app.css?v=1" rel="stylesheet">
<main class="container" style="margin-top:16px">
  <div class="card">
    <h1>test_css_abs.php</h1>
    <p>Denne siden bruker en <strong>hardkodet absolutt path</strong> til CSS. Hvis dette virker og BASE_URL ikke gjør det, peker BASE_URL feil.</p>
    <ul>
      <li>Brukt absolutt path: <code><?= htmlspecialchars($ABS) ?>/assets/css/app.css?v=1</code></li>
      <li><a href="<?= $ABS ?>/assets/css/app.css?v=1" target="_blank">Åpne CSS</a></li>
    </ul>
  </div>
</main>
