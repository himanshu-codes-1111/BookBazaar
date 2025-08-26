<?php
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Get the user ID from session
$user_id = $_SESSION['user_id'];

// Fetch purchase history
$sql = "SELECT p.*, b.title, b.author, b.genre, b.price, bi.front_cover_path
        FROM purchase_history p
        JOIN books b ON p.book_id = b.book_id
        JOIN book_images bi ON b.book_id = bi.book_id
        WHERE p.buyer_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$purchases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - BookBazaar</title>
    <link rel="stylesheet" href="../css/purchasehistory.css">
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
    <h1 class="title">Purchase History</h1>

    <div class="container">
        <?php foreach ($purchases as $purchase) : ?>
            <div class="purchase-item">
                <img src="<?php echo htmlspecialchars($purchase['front_cover_path']); ?>" alt="Book Cover" class="book-cover">
                <div class="details">
                    <h3><?php echo htmlspecialchars($purchase['title']); ?></h3>
                    <p>Author: <?php echo htmlspecialchars($purchase['author']); ?></p>
                    <p>Date Purchased: <?php echo htmlspecialchars($purchase['purchase_date']); ?></p>
                    <p>Price: ₹<?php echo number_format($purchase['total_price'], 2); ?></p>
                </div>
                <a href="transaction_receipt.php?order_id=<?php echo urlencode($purchase['order_id']); ?>" class="details-button">View Receipt</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
