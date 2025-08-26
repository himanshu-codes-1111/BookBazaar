<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    // Delete book from both 'books' and 'book_images' tables
    $conn->begin_transaction();
    
    try {
        // Delete book from book_images table
        $stmt = $conn->prepare("DELETE FROM book_images WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete book from books table
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit the transaction
        $conn->commit();
        
        // Return success
        echo "Book removed successfully";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        echo "Error: Unable to remove the book.";
    }
    
    $conn->close();
}
?>
