<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$book_id = $_GET['book_id'];

// Fetch the seller of the book that the user is trying to add
$stmt = $pdo->prepare("SELECT user_id FROM books WHERE book_id = :book_id");
$stmt->execute(['book_id' => $book_id]);
$book = $stmt->fetch();

if (!$book) {
    echo "Book not found!";
    exit();
}

$bookSellerId = $book['user_id'];

// Check if the book already exists in the user's cart
$existingBookStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND book_id = :book_id");
$existingBookStmt->execute(['user_id' => $loggedInUserId, 'book_id' => $book_id]);
$existingBook = $existingBookStmt->fetch();

if ($existingBook) {
    // The book is already in the cart
    $_SESSION['cart_exists'] = true; // Set a session variable for cart exists
    header("Location: bookdetails.php?id=$book_id"); // Redirect back to book details
    exit();
}

// Check if there are books in the user's cart
$cartStmt = $pdo->prepare("SELECT books.user_id FROM cart 
                           JOIN books ON cart.book_id = books.book_id 
                           WHERE cart.user_id = :user_id");
$cartStmt->execute(['user_id' => $loggedInUserId]);
$cartItems = $cartStmt->fetchAll();

if (count($cartItems) > 0) {
    // There are books in the cart, check the seller
    $existingCartSellerId = $cartItems[0]['user_id'];

    if ($existingCartSellerId != $bookSellerId) {
        // Different seller, show confirmation to replace the cart
        $_SESSION['replace_cart'] = true;  // Store a session variable for the front end to handle the confirmation pop-up
    } else {
        // Same seller, add the book to the cart
        $insertCartStmt = $pdo->prepare("INSERT INTO cart (user_id, book_id) VALUES (:user_id, :book_id)");
        $insertCartStmt->execute(['user_id' => $loggedInUserId, 'book_id' => $book_id]);
        $_SESSION['cart_success'] = true;  // Store a session variable for success
    }
} else {
    // Cart is empty, just add the book
    $insertCartStmt = $pdo->prepare("INSERT INTO cart (user_id, book_id) VALUES (:user_id, :book_id)");
    $insertCartStmt->execute(['user_id' => $loggedInUserId, 'book_id' => $book_id]);
    $_SESSION['cart_success'] = true;  // Store a session variable for success
}

// Redirect back to book details page
header("Location: bookdetails.php?id=$book_id");
exit();
?>
