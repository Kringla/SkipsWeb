<?php
require_once __DIR__ . '/includes/header_auto.php';
?>
<style>
/* INLINE CSS JUST FOR THIS PAGE */
.container{max-width:900px;margin:0 auto;padding:0 16px}
.card{background:#fff;border:2px dashed #1f5fbf;border-radius:12px;padding:16px;margin-top:16px}
a{color:#1f5fbf}
</style>

<main class="container">
  <div class="card">
    <h1>test_css_inline.php</h1>
    <p>Hvis denne boksen har <strong>hvit bakgrunn</strong> og <em>blå lenker</em>, er HTML/CSS-rendering OK ✅ (uavhengig av app.css).</p>
    <p><a href="#">Testlenke</a></p>
  </div>
</main>
