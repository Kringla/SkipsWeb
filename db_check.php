<?php
// db_check.php — SLETT ETTER BRUK
header('Content-Type: text/plain; charset=utf-8');

// 1) Last konstanter
$ok1 = @require_once __DIR__ . '/config/constants.php';
$ok2 = @require_once __DIR__ . '/config/config.php'; // KUN konstanter! (se steg 1)
if (!defined('DB_HOST')) { echo "Finner ikke DB-konstanter.\n"; exit(1); }

echo "DB_HOST: ".DB_HOST."\nDB_USER: ".DB_USER."\nDB_NAME: ".DB_NAME."\n\n";

// 2) DNS-resolusjon
$resolved = @gethostbyname(DB_HOST);
if ($resolved && $resolved !== DB_HOST) {
  echo "DNS OK: ".DB_HOST." -> $resolved\n";
} else {
  echo "DNS FEIL: Klarer ikke slå opp ".DB_HOST." (bruk f.eks. 'localhost' eller korrekt MySQL-host)\n";
}

// 3) Porttest (TCP 3306)
$target = ($resolved && $resolved !== DB_HOST) ? $resolved : DB_HOST;
$errno = $errstr = null;
$fp = @fsockopen($target, 3306, $errno, $errstr, 2.0);
if ($fp) {
  echo "PORT OK: 3306 er åpen på $target\n";
  fclose($fp);
} else {
  echo "PORT FEIL: 3306 utilgjengelig på $target ($errno $errstr)\n";
}

// 4) MySQL-tilkobling
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_errno) {
  http_response_code(500);
  echo "MYSQL FEIL: {$mysqli->connect_error}\n";
  exit(1);
}
$mysqli->set_charset('utf8mb4');
echo "MYSQL OK: Tilkoblet.\n";

// 5) Enkel spørring
$r = $mysqli->query("SELECT 1 AS ok");
if ($r && ($row = $r->fetch_assoc())) {
  echo "SELECT 1 => ".$row['ok']."\n";
}

// 6) Valgfritt: kjernetabeller finnes?
foreach (['tblFartObj','tblFartNavn','tblFartTid'] as $t) {
  $res = $mysqli->query("SHOW TABLES LIKE '".$mysqli->real_escape_string($t)."'");
  echo $t.': '.($res && $res->num_rows ? "finnes\n" : "finnes ikke\n");
}

$mysqli->close();
echo "Ferdig.\n";
