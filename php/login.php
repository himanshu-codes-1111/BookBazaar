<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        
        if (password_verify($password, $hashed_password)) {
            // Password is correct, start a session
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user_id; // Store user ID in session
            header("Location: ../home.html"); // Redirect to homepage or dashboard
            exit();
        } else {
            // Redirect back to login with an error message
            header("Location: ../login.html?error=Invalid+password.");
            exit();
        }
    } else {
        // Redirect back to login with an error message
        header("Location: ../login.html?error=No+account+found+with+that+email.");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
