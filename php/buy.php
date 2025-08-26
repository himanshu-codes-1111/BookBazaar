<?php
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

// Check if book_id is provided via GET or POST
$book_id = isset($_POST['book_id']) ? $_POST['book_id'] : (isset($_GET['book_id']) ? $_GET['book_id'] : null);

if (!$book_id) {
    echo "Required data is missing.";
    exit();
}

// Get the purchase option from GET (enum: 'Self-Pickup' or 'Delivery')
$purchase_option = isset($_GET['option']) && $_GET['option'] === 'delivery' ? 'Delivery' : 'Self-Pickup';

// Get the user ID from the session
$buyer_id = $_SESSION['user_id'];

// Fetch book details
$sql = "SELECT * FROM books WHERE book_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if ($book) {
    $seller_id = $book['user_id'];
    $quantity = 1; // Set quantity to 1 for unique books
    $book_price = $book['price'];
    $total_price = $book_price * $quantity;

    // Add delivery charges if applicable
    if ($purchase_option === 'Delivery') {
        $total_price += 50; // Assuming ₹50 delivery charge
    }

    $payment_method = 'COD'; // Set default payment method to COD
    $partner_id = null; // If partner_id logic exists, set it here (defaulting to null)

    // Insert the order into the orders table
    $sql = "INSERT INTO orders (buyer_id, seller_id, total_price, purchase_option, payment_method) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$buyer_id, $seller_id, $total_price, $purchase_option, $payment_method]);

    // Get the last inserted order ID
    $order_id = $pdo->lastInsertId();

    // Insert the book into the order_items table
    $sql = "INSERT INTO order_items (order_id, book_id, quantity, price) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $book_id, $quantity, $book_price]);

    // Redirect to the confirmation page with the order ID
    header("Location: order_confirmation.php?order_id=$order_id");
} else {
    echo "Book not found.";
}
?>
