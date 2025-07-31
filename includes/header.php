<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <title>SKIPSWEB</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header>
    <h1>SKIPSWEB</h1>
    <?php if (isLoggedIn()): ?>
      <p>Logget inn som: <?= htmlspecialchars($_SESSION['role']) ?></p>
    <?php endif; ?>
    <hr>
  </header>
  <main>
