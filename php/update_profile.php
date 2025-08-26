<?php
session_start();
require_once 'db_connection.php'; // Include the database connection file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the logged-in user's ID
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate inputs
    $username = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $mobile = trim($_POST['mobile']);
    $address = trim($_POST['address']);

    // Check if passwords match (assuming the confirm password field is named 'confirm_password')
    if ($password !== trim($_POST['confirm_password'])) {
        die("Passwords do not match!");
    }

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Initialize the profile picture variable
    $profile_picture = null;

    // Handle the profile picture upload if a file is provided
    if (isset($_FILES['profile-pic']) && $_FILES['profile-pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile-pic']['tmp_name'];
        $file_name = $_FILES['profile-pic']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed file types
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_extensions)) {
            // Set the upload directory and create it if it doesn't exist
            $upload_dir = 'uploads/profile_pics/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = uniqid('profile_') . '.' . $file_ext;
            $upload_file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_file_path)) {
                $profile_picture = $upload_file_path;
            } else {
                die("Failed to upload profile picture.");
            }
        } else {
            die("Invalid file type for profile picture.");
        }
    }

    try {
        // Prepare an SQL statement to update the user's profile
        $sql = "UPDATE users SET 
                username = :username, 
                email = :email, 
                password = :password, 
                phone_number = :mobile, 
                address = :address";

        if ($profile_picture) {
            $sql .= ", profile_picture = :profile_picture";
        }

        $sql .= " WHERE user_id = :user_id";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);

        if ($profile_picture) {
            $stmt->bindParam(':profile_picture', $profile_picture);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo "Profile updated successfully!";
            // Optionally, you can redirect the user to another page
            header('Location: userprofile.php');
            exit();
        } else {
            echo "Failed to update profile.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
