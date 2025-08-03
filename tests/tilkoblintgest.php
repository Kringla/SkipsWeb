<?php
$host   = 'host.onnet.no';
$port   = 3306;
$dbname = 'gerhard_skip';
$user   = 'gerhard_skipsweb';
$pass   = '5!Ling?#';

// Establish connection
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection status
if ($conn->connect_error) {
    // Connection failed – output the error message
    die("Tilkobling feilet: " . $conn->connect_error);
}

// If we reach here, connection is successful
echo "Tilkobling OK";

// Close the connection (optional, since script ends)
$conn->close();
?>