<?php 
session_start();
require 'db_connection.php'; // Include the PDO connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];

    // Fetch book details (add books.user_id to the SELECT query)
    $stmt = $pdo->prepare("SELECT books.user_id, books.title, books.author, books.genre, books.condition, books.price, books.description, 
                              book_images.front_cover_path, book_images.back_cover_path, 
                              book_images.inner_page1_path, book_images.inner_page2_path,
                              users.username, users.address, users.email, users.phone_number, users.profile_picture
                       FROM books 
                       LEFT JOIN book_images ON books.book_id = book_images.book_id 
                       LEFT JOIN users ON books.user_id = users.user_id
                       WHERE books.book_id = :book_id");

    $stmt->execute(['book_id' => $book_id]);
    $book = $stmt->fetch();

    // Check if any book was found
    if (!$book) {
        echo "Book not found!";
        exit();
    }

    // Fetch other books by the seller that are available
    $otherBooksStmt = $pdo->prepare("SELECT books.book_id, books.title, book_images.front_cover_path 
    FROM books 
    LEFT JOIN book_images ON books.book_id = book_images.book_id 
    WHERE books.user_id = :user_id AND books.book_id != :current_book_id AND books.status = 'available'");
    $otherBooksStmt->execute(['user_id' => $book['user_id'], 'current_book_id' => $book_id]);
    $otherBooks = $otherBooksStmt->fetchAll();

    // Get the logged-in user ID from the session
    $loggedInUserId = $_SESSION['user_id'];

    // Check if the logged-in user is the seller of the book
    $isSeller = $loggedInUserId === $book['user_id'];

} else {
    echo "Invalid book ID!";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bookdetails.css">
    <title>Book Details - <?php echo htmlspecialchars($book['title']); ?></title>
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
    <main class="containering">
        <section class="book-details">
            <div class="left-section">
                <!-- Display the main book image -->
                <img id="main-img" src="<?php echo htmlspecialchars($book['front_cover_path']); ?>" alt="Book Cover" class="book-img" onclick="openFullscreenImage(this)">
                
                <div class="thumbnails">
                    <img src="<?php echo htmlspecialchars($book['front_cover_path']); ?>" alt="Front Cover" onclick="changeImage(this)">
                    <img src="<?php echo htmlspecialchars($book['back_cover_path']); ?>" alt="Back Cover" onclick="changeImage(this)">
                    <img src="<?php echo htmlspecialchars($book['inner_page1_path']); ?>" alt="Inner Page 1" onclick="changeImage(this)">
                    <img src="<?php echo htmlspecialchars($book['inner_page2_path']); ?>" alt="Inner Page 2" onclick="changeImage(this)">
                </div>
            </div>
            
            <div class="book-info">
                <!-- Book information and buttons -->
                <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                <p class="author"><b>Author: </b><?php echo htmlspecialchars($book['author']); ?></p>
                <p class="category"><b>Category: </b><?php echo htmlspecialchars($book['genre']); ?></p>
                <p class="condition"><b>Condition: </b><?php echo htmlspecialchars($book['condition']); ?></p>
                <p class="price"><b>Price: </b>₹<?php echo number_format($book['price'], 2); ?></p>
                <p class="description"><?php echo htmlspecialchars($book['description']); ?></p>
                <div class="button-group">
                    <a href="javascript:void(0);" id="addToCartButton" class="add-to-cart-button">Add to Cart</a>
                    <a href="#" id="buyNowButton" class="buy-button">Buy Now</a>
                </div>
            </div>
        </section>

        <!-- Seller's information -->
        <section class="seller-info">
            <h1>Seller's Info:</h1>
            <div class="seller-details">
                <div class="profile-picture">
                    <img src="<?php echo htmlspecialchars($book['profile_picture']); ?>" alt="Profile Picture" onclick="openFullscreenImage(this)">
                </div>
                <div class="seller-text">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($book['username']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($book['address']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($book['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($book['phone_number']); ?></p>
                </div>
            </div>
        </section>


        <!-- Other books by the seller -->
        <section class="other-books">
            <h1>Other Books for Sale:</h1>
            <div class="books-for-sale">
                <?php foreach ($otherBooks as $otherBook): ?>
                    <div class="book-item">
                        <a href="bookdetails.php?id=<?php echo htmlspecialchars($otherBook['book_id']); ?>">
                            <img src="<?php echo htmlspecialchars($otherBook['front_cover_path']); ?>" alt="Book Front Cover">
                            <p><?php echo htmlspecialchars($otherBook['title']); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        
        <!-- Buy Options Popup Modal -->
        <div id="buyOptionsPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closePopup()">&times;</span>
                <h3>Select Purchase Option</h3>
                <p>How would you like to receive your book?</p>
                <div class="popup-buttons">
                    <button id="selfPickupButton" class="popup-btn">Self-Pickup</button>
                    <button id="deliveryButton" class="popup-btn">Delivery by Partner (₹50)</button>
                </div>
            </div>
        </div>

        <!-- Seller Warning Modal -->
        <div id="sellerWarningPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeSellerPopup()">&times;</span>
                <h3>Warning</h3>
                <p>You cannot buy your own book!</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="closeSellerPopup()">OK</button>
                </div>
            </div>
        </div>

        <!-- Cart Confirmation Popup (Success) -->
        <div id="cartSuccessPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeCartPopup()">&times;</span>
                <h3>Success</h3>
                <p>The book has been added to your cart!</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="closeCartPopup()">OK</button>
                </div>
            </div>
        </div>

        <!-- Cart Replace Popup -->
        <div id="cartReplacePopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeCartPopup()">&times;</span>
                <h3>Different Seller</h3>
                <p>Your cart contains books from a different seller. Do you want to replace them?</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="replaceCartItems()">Yes, Replace</button>
                    <button class="popup-btn" onclick="closeCartPopup()">No</button>
                </div>
            </div>
        </div>

        <!-- Seller Cart Warning Modal -->
        <div id="sellerCartWarningPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeSellerCartPopup()">&times;</span>
                <h3>Warning</h3>
                <p>You cannot add your own book to the cart!</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="closeSellerCartPopup()">OK</button>
                </div>
            </div>
        </div>

        <!-- Cart Already Exists Popup -->
        <div id="cartExistsPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeCartExistsPopup()">&times;</span>
                <h3>Warning</h3>
                <p>This book is already in your cart!</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="closeCartExistsPopup()">OK</button>
                </div>
            </div>
        </div>

        <!-- Seller Cart Warning Modal -->
        <div id="sellerCartWarningPopup" class="popup-modal" style="display:none;">
            <div class="popup-content">
                <span class="close-popup" onclick="closeSellerCartPopup()">&times;</span>
                <h3>Warning</h3>
                <p>You cannot add your own book to the cart!</p>
                <div class="popup-buttons">
                    <button class="popup-btn" onclick="closeSellerCartPopup()">OK</button>
                </div>
            </div>
        </div>

        <!-- Fullscreen Image Modal -->
        <div id="fullscreen-modal" class="fullscreen-modal">
            <span class="close" onclick="closeFullscreenImage()">&times;</span>
            <img class="fullscreen-img" id="fullscreen-img">
        </div>
    </main>

    <script>
        // Check if the user is the seller
        var isSeller = <?php echo json_encode($isSeller); ?>;

        // Open the popup when 'Buy Now' is clicked
        document.getElementById('buyNowButton').addEventListener('click', function() {
            if (isSeller) {
                document.getElementById('sellerWarningPopup').style.display = 'block';
            } else {
                document.getElementById('buyOptionsPopup').style.display = 'block';
            }
        });

        // Open the popup when 'Add to Cart' is clicked
        document.getElementById('addToCartButton').addEventListener('click', function() {
            if (isSeller) {
                // If the logged-in user is the seller, show the warning popup
                document.getElementById('sellerCartWarningPopup').style.display = 'block';
            } else {
                // Redirect to the cart.php if the user is not the seller
                window.location.href = 'cart.php?book_id=<?php echo $book_id; ?>';
            }
        });

        // Close the seller cart warning popup
        function closeSellerCartPopup() {
            document.getElementById('sellerCartWarningPopup').style.display = 'none';
        }
        
        // Close the popup for selecting purchase options
        function closePopup() {
            document.getElementById('buyOptionsPopup').style.display = 'none';
        }

        // Close the seller warning popup
        function closeSellerPopup() {
            document.getElementById('sellerWarningPopup').style.display = 'none';
        }

        // Add event listeners for purchase option buttons
        document.getElementById('selfPickupButton').addEventListener('click', function() {
            // Handle Self-Pickup option (e.g., redirect or display a confirmation message)
            window.location.href = 'buy.php?book_id=<?php echo $book_id; ?>&option=self-pickup'; // Replace with the actual processing script
        });

        document.getElementById('deliveryButton').addEventListener('click', function() {
            // Handle Delivery option (e.g., redirect or display a confirmation message)
            window.location.href = 'buy.php?book_id=<?php echo $book_id; ?>&option=delivery'; // Replace with the actual processing script
        });

        // Fullscreen image functionality
        function openFullscreenImage(element) {
            const modal = document.getElementById('fullscreen-modal');
            const fullscreenImg = document.getElementById('fullscreen-img');
            modal.style.display = 'block';
            fullscreenImg.src = element.src;
        }

        function changeImage(element) {
            document.getElementById('main-img').src = element.src;
        }

        function closeFullscreenImage() {
            document.getElementById('fullscreen-modal').style.display = 'none';
        }

        window.onload = function() {
            // Show cart success popup if session variable is set
            <?php if (isset($_SESSION['cart_success']) && $_SESSION['cart_success'] === true): ?>
                document.getElementById('cartSuccessPopup').style.display = 'block';
                <?php unset($_SESSION['cart_success']); ?>
            <?php endif; ?>

            // Show cart replace popup if session variable is set
            <?php if (isset($_SESSION['replace_cart']) && $_SESSION['replace_cart'] === true): ?>
                document.getElementById('cartReplacePopup').style.display = 'block';
                <?php unset($_SESSION['replace_cart']); ?>
            <?php endif; ?>

            // Show 'already in cart' popup if session variable is set
            <?php if (isset($_SESSION['cart_exists']) && $_SESSION['cart_exists'] === true): ?>
                document.getElementById('cartExistsPopup').style.display = 'block';
                <?php unset($_SESSION['cart_exists']); ?>
            <?php endif; ?>
        }

        // Function to close the 'Already in Cart' popup
        function closeCartExistsPopup() {
        document.getElementById('cartExistsPopup').style.display = 'none';
        }

        // Close the cart popup
        function closeCartPopup() {
            document.getElementById('cartSuccessPopup').style.display = 'none';
            document.getElementById('cartReplacePopup').style.display = 'none';
        }

        // Replace cart items (on confirmation)
        function replaceCartItems() {
            // Redirect to a script to clear the cart and add the new book
            window.location.href = 'replace_cart.php?book_id=<?php echo $book_id; ?>';
        }

        // Check if the user is the seller
        var isSeller = <?php echo json_encode($isSeller); ?>;

        // Open the popup when 'Add to Cart' is clicked
        document.getElementById('addToCartButton').addEventListener('click', function() {
            if (isSeller) {
                // If the logged-in user is the seller, show the warning popup
                document.getElementById('sellerCartWarningPopup').style.display = 'block';
            } else {
                // Redirect to the cart.php if the user is not the seller
                window.location.href = 'cart.php?book_id=<?php echo $book_id; ?>';
            }
        });

        // Close the seller cart warning popup
        function closeSellerCartPopup() {
            document.getElementById('sellerCartWarningPopup').style.display = 'none';
        }
    </script>
</body>
</html>
