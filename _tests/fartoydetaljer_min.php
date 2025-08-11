<?php
require_once __DIR__ . '/../includes/bootstrap.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
echo "HELLO-MIN id=$id<br>";

$stmt = $conn->prepare("SELECT FartObj_ID, NavnObj FROM tblFartObj WHERE FartObj_ID=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
var_dump($r);
