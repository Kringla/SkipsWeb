<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_unset();
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
?>
