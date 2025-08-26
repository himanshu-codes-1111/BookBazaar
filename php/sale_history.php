<?php
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Get the seller ID from session
$seller_id = $_SESSION['user_id'];

// Fetch sale history
$sql = "SELECT s.*, b.title, b.author, b.genre, b.price, bi.front_cover_path
        FROM sale_history s
        JOIN books b ON s.book_id = b.book_id
        JOIN book_images bi ON b.book_id = bi.book_id
        WHERE s.seller_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$seller_id]);
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale History - BookBazaar</title>
    <link rel="stylesheet" href="../css/salehistory.css">
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
    <h1 class="title">Sale History</h1>

    <div class="container">
        <?php foreach ($sales as $sale) : ?>
            <div class="sale-item">
                <img src="<?php echo htmlspecialchars($sale['front_cover_path']); ?>" alt="Book Cover" class="book-cover">
                <div class="details">
                    <h3><?php echo htmlspecialchars($sale['title']); ?></h3>
                    <p>Author: <?php echo htmlspecialchars($sale['author']); ?></p>
                    <p>Date Sold: <?php echo htmlspecialchars($sale['sale_date']); ?></p>
                    <p>Price: ₹<?php echo number_format($sale['total_price'], 2); ?></p>
                </div>
                <a href="transaction_receipt.php?order_id=<?php echo urlencode($sale['order_id']); ?>" class="details-button">View Receipt</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
