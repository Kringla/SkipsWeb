<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$baseUrlDefined = false;
$constPath = __DIR__ . '/../config/constants.php';
if (is_file($constPath)) {
  require_once $constPath;
  if (defined('BASE_URL')) { $baseUrlDefined = true; }
}
if (!$baseUrlDefined) {
  $dir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
  define('BASE_URL', $dir === '/' ? '' : $dir);
}
$role = $_SESSION['user_role'] ?? 'guest';
?>
<!doctype html>
<html lang="no">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SkipsWeb (diagnose)</title>
</head>
<body class="role-<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
<header class="site-header">
  <div class="container">
    <a class="brand" href="<?= BASE_URL ?>/">SkipsWeb</a>
  </div>
</header>
