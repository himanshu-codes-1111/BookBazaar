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

// Fetch only 'available' books in the user's cart along with seller info
$sql = "SELECT c.cart_id, b.book_id, b.title, b.author, b.genre, b.price, bi.front_cover_path, b.user_id as seller_id
        FROM cart c
        JOIN books b ON c.book_id = b.book_id
        JOIN book_images bi ON b.book_id = bi.book_id
        WHERE c.user_id = ? AND b.status = 'available'"; // Only fetch books with status 'available'

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - BookBazaar</title>
    <link rel="stylesheet" href="../css/your_cart_items.css">
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
    <h1 class="title">Your Cart</h1>

    <div class="container">
        <?php if (count($cart_items) > 0) : ?>
            <?php foreach ($cart_items as $item) : ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['front_cover_path']); ?>" alt="Book Cover" class="book-cover">
                    <div class="details">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p>Author: <?php echo htmlspecialchars($item['author']); ?></p>
                        <p>Genre: <?php echo htmlspecialchars($item['genre']); ?></p>
                        <p>Price: ₹<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <a href="bookdetails.php?id=<?php echo urlencode($item['book_id']); ?>" class="details-button">View Details</a>
                    <button class="remove-button" onclick="confirmRemove(<?php echo $item['book_id']; ?>)">Remove</button>
                </div>
            <?php endforeach; ?>

            <!-- Buy Now Button at the Bottom -->
            <div class="buy-now-container">
                <button type="button" class="buy-now-button" onclick="checkSellerAndBuyer(<?php echo $cart_items[0]['seller_id']; ?>)">Buy Now</button>
            </div>
        <?php else : ?>
            <p>Your cart is currently empty.</p>
        <?php endif; ?>
    </div>

    <!-- Same Seller Warning Popup 
    <div id="same-seller-popup" class="modal" style="display:none;">
        <div class="modal-content">
            <p>You cannot purchase your own book!</p>
            <button onclick="closePopup('same-seller-popup')">OK</button>
        </div>
    </div> -->

    <!-- Purchase Option Popup -->
    <div id="purchase-option-popup" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closePopup('purchase-option-popup')">&times;</span>
            <h3>Select Purchase Option</h3>
            <p>How would you like to receive your book?</p>
            <button onclick="proceedToBuy('Self-Pickup')">Self-Pickup</button>
            <button onclick="proceedToBuy('Delivery')">Delivery by Partner (₹50)</button>
        </div>
    </div>

    <!-- Remove Confirmation Popup -->
    <div id="confirmModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closePopup('confirmModal')">&times;</span>
            <p>Are you sure you want to remove this book from your cart?</p>
            <div class="popup-buttons">
                <button id="closeModal">Close</button>
                <button id="confirmDelete">Yes, Remove</button>
            </div>
        </div>
    </div>

    <script>
        let bookIdToRemove = null;

        function confirmRemove(bookId) {
            bookIdToRemove = bookId;
            document.getElementById('confirmModal').style.display = 'block';
        }

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('confirmModal').style.display = 'none';
        };

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        document.getElementById('confirmDelete').onclick = function() {
            if (bookIdToRemove) {
                window.location.href = 'remove_from_cart.php?book_id=' + bookIdToRemove;
            }
        };

        function checkSellerAndBuyer(sellerId) {
            const buyerId = <?php echo json_encode($user_id); ?>;
            if (sellerId === buyerId) {
                document.getElementById('same-seller-popup').style.display = 'block';
            } else {
                document.getElementById('purchase-option-popup').style.display = 'block';
            }
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function proceedToBuy(option) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'cart_buy.php';

            const purchaseOption = document.createElement('input');
            purchaseOption.type = 'hidden';
            purchaseOption.name = 'purchase_option';
            purchaseOption.value = option;
            form.appendChild(purchaseOption);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
