<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];

    // Delete the book from the cart for the current user
    $sql = "DELETE FROM cart WHERE user_id = ? AND book_id = ?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$user_id, $book_id])) {
        header("Location: your_cart_items.php");
    } else {
        echo "Error removing the book from your cart.";
    }
}
?>
