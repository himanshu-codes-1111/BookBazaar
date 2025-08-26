<?php
$host = 'localhost'; // Server name or IP address
$port = '4306'; // Port number for MySQL (Make sure this matches your MySQL server's port)
$dbname = 'bookbazaar'; // Your database name
$username = 'root'; // Database username
$password = ''; // Database password

try {
    // Include the port number in the DSN
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Set the default fetch mode to fetch associative arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Return the PDO object so it can be used in other scripts
    return $pdo;
} catch (PDOException $e) {
    // Handle connection error
    echo 'Connection failed: ' . $e->getMessage();
    exit(); // Terminate the script if the connection fails
}
?>
