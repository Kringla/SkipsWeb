<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'DB-vertsnavn';     // f.eks. localhost eller onnet.no
$db   = 'gerhard_skip';     // eller det navnet dere bruker
$user = 'db_bruker';        // ditt brukernavn
$pass = 'db_passord';       // ditt passord
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Tilkobling mot databasen OK!";
} catch (\PDOException $e) {
    echo "Database-feil: " . $e->getMessage();
}
