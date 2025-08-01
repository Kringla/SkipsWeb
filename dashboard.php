<?php
// dashboard.php
require_once __DIR__ . '/includes/bootstrap.php'; // Sjekk at bruker er logget inn
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
</head>
<body>
  <?php
    // Hent brukerinfo
    session_start();
    $userId = $_SESSION['user_id'] ?? '';
    $role   = $_SESSION['role']    ?? '';
  ?>
  <h1>Velkommen til SKIPSWEB</h1>
  <p>Du er logget inn som bruker-ID: <strong><?= htmlspecialchars($userId) ?></strong>
     med rolle: <strong><?= htmlspecialchars($role) ?></strong>.</p>

  <nav>
    <ul>
      <li><a href="user/fartoy_sok.php">Søk fartøy</a></li>
      <?php if ($role === 'ADM'): ?>
        <li><a href="admin/fartoy_edit.php">Administrer fartøy</a></li>
        <li><a href="admin/brukere.php">Administrer brukere</a></li>
      <?php endif; ?>
      <li><a href="logout.php">Logg ut</a></li>
    </ul>
  </nav>
</body>
</html>
