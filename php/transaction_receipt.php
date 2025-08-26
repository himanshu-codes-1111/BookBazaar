<?php
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Get the order ID from the query string
$order_id = $_GET['order_id'];

// Fetch all books associated with this order
$sql = "SELECT o.*, oi.book_id, oi.price AS book_price, oi.quantity, b.title, b.author, b.genre, 
               bi.front_cover_path, 
               u1.username AS seller_name, u1.email AS seller_email, u1.phone_number AS seller_phone, u1.address AS seller_address, 
               u2.username AS buyer_name, u2.email AS buyer_email, u2.phone_number AS buyer_phone, u2.address AS buyer_address
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN books b ON oi.book_id = b.book_id
        JOIN book_images bi ON b.book_id = bi.book_id
        JOIN users u1 ON o.seller_id = u1.user_id
        JOIN users u2 ON o.buyer_id = u2.user_id
        WHERE o.order_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$order_items) {
    echo "Order not found.";
    exit();
}

// Get the common order details from the first item (since they are the same for all items in the order)
$order = $order_items[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Receipt</title>
    <link rel="stylesheet" href="../css/transactionreceipt.css">
</head>
<body>
    <div class="container">
        <div class="headline">Transaction ID: <?php echo htmlspecialchars(uniqid('TXN') . '-' . $order_id); ?></div>

        <div class="info">
            <h3>Seller's Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['seller_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['seller_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['seller_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['seller_address']); ?></p>
        </div>

        <div class="info">
            <h3>Buyer's Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['buyer_address']); ?></p>
        </div>

        <hr>

        <div class="order-details">
            <h3>Order Details</h3>

            <!-- Loop through each book associated with this order -->
            <?php foreach ($order_items as $item): ?>
            <div class="details">
                <img src="<?php echo htmlspecialchars($item['front_cover_path']); ?>" alt="Book Cover">
                <div>
                    <p><strong>Book Name:</strong> <?php echo htmlspecialchars($item['title']); ?></p>
                    <p><strong>Book Author:</strong> <?php echo htmlspecialchars($item['author']); ?></p>
                    <p><strong>Book Genre:</strong> <?php echo htmlspecialchars($item['genre']); ?></p>
                    <p><strong>Book Price:</strong> ₹<?php echo number_format($item['book_price'], 2); ?></p>
                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
                </div>
            </div>
            <hr>
            <?php endforeach; ?>

            <!-- Common details for the entire order -->
            <p><strong>Purchase Option:</strong> <?php echo htmlspecialchars($order['purchase_option']); ?></p>
            <?php if ($order['purchase_option'] === 'Delivery') : ?>
                <p><strong>Delivery Charges:</strong> ₹50</p>
            <?php endif; ?>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Total Price:</strong> ₹<?php echo number_format($order['total_price'], 2); ?></p>
        </div>
    </div>
</body>
</html>
