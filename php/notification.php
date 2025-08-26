<?php 
session_start();
require 'db_connection.php'; // Include your PDO connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$sql = "SELECT message, created_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notifications</title>
    <link rel="stylesheet" href="../css/notification.css"> <!-- Link to your CSS file for styling -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
<body>
<header>
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../assets/logo1.jpg" alt="Book Bazaar Logo">
                </div>
                <h1 class="platform-name">Book Bazaar</h1>
            </div>
            <div class="nav-links">
                <a href="../home.html"><i class="fa-solid fa-house-chimney"></i></a>
                <a href="../login.html"><i class="fa-solid fa-right-to-bracket"></i></a>
                <a href="userprofile.php"><i class="fa-solid fa-user"></i></a>
            </div>
        </nav>
    </header>
    <div class="notification-container">
        <h2>Notifications</h2>

        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <div class="icon">
                    <i class="fa-solid fa-bell"></i>
                    </div>
                    <div class="details">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="timestamp"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($notification['created_at']))); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</body>
</html>
