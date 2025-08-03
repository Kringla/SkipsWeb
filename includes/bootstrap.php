<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koble til databasen (MySQLi) med feilhåndtering
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_errno) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    exit('Internal Server Error');
}
$conn->set_charset('utf8mb4');

// Autentiseringsjekk for beskyttede sider
$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
$publicPages = ['login.php', 'register.php', 'logout.php'];
if (!in_array($script, $publicPages)) {
    if (empty($_SESSION['user_id'])) {
        header(header('Location: ' . BASE_URL . '/login.php'););
        exit;
    }
}

require_once __DIR__ . '/functions.php';
?>
