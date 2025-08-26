<?php 
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Get user and purchase option from the session
$user_id = $_SESSION['user_id'];
$purchase_option = $_POST['purchase_option'];

// Fetch cart items of the user
$sql = "SELECT c.cart_id, b.book_id, b.price, b.user_id as seller_id
        FROM cart c
        JOIN books b ON c.book_id = b.book_id
        WHERE c.user_id = ? AND b.status = 'available'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) > 0) {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Calculate total price and get seller ID from the first cart item
        $total_price = 0;
        $seller_id = $cart_items[0]['seller_id'];

        foreach ($cart_items as $item) {
            $total_price += $item['price'];
        }

        // If the purchase option is delivery, add ₹50 as the delivery charge
        if ($purchase_option === 'delivery') {
            $total_price += 50;
        }

        // Insert into orders table with payment method hardcoded to 'COD'
        $sql = "INSERT INTO orders (buyer_id, seller_id, total_price, status, purchase_option, payment_method, created_at)
                VALUES (?, ?, ?, 'Pending', ?, 'COD', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $seller_id, $total_price, $purchase_option]);

        // Get the newly inserted order ID
        $order_id = $pdo->lastInsertId();

        // Insert each item into the order_items table
        $sql = "INSERT INTO order_items (order_id, book_id, quantity, price)
                VALUES (?, ?, 1, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['book_id'], $item['price']]);
        }

        // Commit the transaction
        $pdo->commit();

        // Redirect to order confirmation or receipt page
        header("Location: cart_order_confirmation.php?order_id=" . $order_id);
        exit();

    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();
        echo "Failed to place the order: " . $e->getMessage();
    }
} else {
    echo "Your cart is empty.";
}
