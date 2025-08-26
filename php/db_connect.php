
<?php
// Database configuration
$servername = "localhost:4306";
$username = "root"; // Default username for MySQL
$password = ""; // Leave this empty because the root user has no password
$database = "bookbazaar"; // Your database name
//$port = 4306; // Port number specified in your my.ini

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo"connected successfully";
?>