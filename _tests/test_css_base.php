<?php
require_once __DIR__ . '/includes/header_auto.php';
$BASE = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
?>
<link href="<?= $BASE ?>/assets/css/app.css?v=1" rel="stylesheet">
<main class="container" style="margin-top:16px">
  <div class="card">
    <h1>test_css_base.php</h1>
    <p>Hvis denne boksen er hvit med skygge og du ser maritim blå farger, er <strong>app.css via BASE_URL</strong> riktig lastet.</p>
    <ul>
      <li>BASE_URL: <code><?= htmlspecialchars($BASE) ?></code></li>
      <li><a href="<?= $BASE ?>/assets/css/app.css?v=1" target="_blank">Åpne CSS</a></li>
    </ul>
  </div>
</main>
