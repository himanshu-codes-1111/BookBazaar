<?php
session_start();
require_once 'db_new_connection.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header('Location: ../home.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch existing user data
$sql = "SELECT username, email, phone_number, address, profile_picture FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - BookBazaar</title>
    <link rel="stylesheet" href="../css/edituserprofile.css">
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <form class="edit-profile-form" enctype="multipart/form-data" method="POST" action="update_profile.php">
            <div class="profile-pic-container">
                <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'https://picsum.photos/seed/xeuF13jjd1068rmU11Tgk/512'; ?>" alt="Profile Picture" id="profile-pic-preview" class="profile-pic">
                <div class="file-upload">
                    <label for="profile-pic" class="file-upload-label">Change Profile Picture</label>
                    <input type="file" id="profile-pic" name="profile-pic" accept="image/*" class="file-upload-input">
                </div>
            </div>

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter the new password">
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number:</label>
                <input type="tel" id="mobile" name="mobile" placeholder="Enter your mobile number" required value="<?php echo htmlspecialchars($user['phone_number']); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="4" placeholder="Enter your address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <button type="submit" class="submit-button">Save Changes</button>
        </form>
    </div>
    <script>
        document.getElementById('profile-pic').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-pic-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>
