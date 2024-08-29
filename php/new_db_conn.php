<?php
$host = 'localhost'; // Server name or IP address
$port = '4306'; // Port number for MySQL
$dbname = 'bookbazaar'; // Your database name
$username = 'root'; // Database username
$password = ''; // Database password

try {
    // Include the port number in the DSN
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
