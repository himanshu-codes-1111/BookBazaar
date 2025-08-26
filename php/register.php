<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Ensure the field name matches
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number']; // Ensure the field name matches
    $gender = $_POST['gender']; // Ensure the field name matches
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Ensure the field name matches

    // Validate passwords
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, address, email, phone_number, gender, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $address, $email, $phone_number, $gender, $hashed_password);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: ../login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
