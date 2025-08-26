<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$book_id = $_GET['book_id'];

// Clear the existing cart for the user
$clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
$clearCartStmt->execute(['user_id' => $loggedInUserId]);

// Add the new book to the cart
$insertCartStmt = $pdo->prepare("INSERT INTO cart (user_id, book_id) VALUES (:user_id, :book_id)");
$insertCartStmt->execute(['user_id' => $loggedInUserId, 'book_id' => $book_id]);

$_SESSION['cart_success'] = true;
header("Location: bookdetails.php?id=$book_id");
exit();
