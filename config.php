<?php
define('DB_HOST', 'host.onnet.no');
define('DB_NAME', 'gerhard_skip');
define('DB_USER', 'gerhard_skipsweb');
define('DB_PASSWORD', '5!Ling?#');
// define('BASE_URL', 'https://ihlen.net/skipsweb');
define('BASE_URL', '/skipsweb');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_errno) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>