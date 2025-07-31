<?php
// includes/bootstrap.php

define('BASE_PATH', __DIR__ . '/..');

// Autoloader (dersom dere bruker Composer eller egen autoloader)
// require BASE_PATH . '/vendor/autoload.php';

// Laste konfigurasjon fra ekstern fil
$config = require BASE_PATH . '/config/config.php';
$dbCfg = $config['db'];

// Start session om ikke allerede startet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database-tilkobling via PDO
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbCfg['host'],
    $dbCfg['port'],
    $dbCfg['name'],
    $dbCfg['charset']
);

try {
    $pdo = new PDO(
        $dsn,
        $dbCfg['user'],
        $dbCfg['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Logg feil (bruk PSR-3 logger hvis tilgjengelig)
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal server error');
}

// Auth-check: her kan du sjekke om bruker har gyldig sesjon / rolle
// f.eks.:
// if (!isset($_SESSION['user'])) {
//     header('Location: /login.php');
//     exit;
// }