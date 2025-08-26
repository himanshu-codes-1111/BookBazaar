<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: ../login.html'); // Redirect to login page if not logged in
    exit();
}

$email = $_SESSION['email'];

// Fetch user details from database
$stmt = $conn->prepare("SELECT username, email, phone_number, gender, address, profile_picture FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($username, $user_email, $phone_number, $gender, $address, $profile_picture);
$stmt->fetch();
$stmt->close();

// Fetch books for sale by the user, only showing books with status 'available'
$books_stmt = $conn->prepare("SELECT books.book_id, title, front_cover_path 
                              FROM books 
                              INNER JOIN book_images ON books.book_id = book_images.book_id 
                              WHERE books.user_id = (SELECT user_id FROM users WHERE email = ?) 
                              AND books.status = 'available'");
$books_stmt->bind_param("s", $email);
$books_stmt->execute();
$books_result = $books_stmt->get_result();
$books = [];
while ($book = $books_result->fetch_assoc()) {
    $books[] = $book;
}
$books_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - BookBazaar</title>
    <link rel="stylesheet" href="../css/userprofile.css">
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
    <div class="container">
        <div class="sidebar">
            <a href="../sellbook.html">Add Book for Sale</a>
            <a href="edituserprofile.php">Edit Profile</a>
            <a href="purchase_history.php">Purchase History</a>
            <a href="your_cart_items.php">Cart</a>
            <a href="notification.php">Notification</a>
            <a href="#" id="logoutBtn">Log Out</a> <!-- Updated for popup trigger -->
            <a href="sale_history.php">Sale History</a>
        </div>

        <div class="main-content">
            <div class="profile-header">
                <img class="profile-image" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="User Profile">
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($username); ?></h2>
                    <p><b>Email:</b> <?php echo htmlspecialchars($user_email); ?></p>
                    <p><b>Contact:</b> <?php echo htmlspecialchars($phone_number); ?></p>
                    <p><b>Gender:</b> <?php echo htmlspecialchars($gender); ?></p>
                    <p><b>Address:</b> <?php echo htmlspecialchars($address); ?></p>
                </div>
            </div>

            <div class="books-for-sale">
                <h1>Books for Sale</h1>
                <div class="books-grid">
                    <?php foreach ($books as $book): ?>
                    <div class="book-item" data-book-id="<?php echo $book['book_id']; ?>">
                        <a href="bookdetails.php?id=<?php echo $book['book_id']; ?>">
                            <img src="<?php echo htmlspecialchars($book['front_cover_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <p><?php echo htmlspecialchars($book['title']); ?></p>
                        </a>
                        <button class="remove-button" onclick="confirmRemove(<?php echo $book['book_id']; ?>)">Remove</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Popup Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to remove this book from your Books for Sale?</p>
            <button id="closeModal">Close</button>
            <button id="confirmDelete">Yes, Remove</button>
        </div>
    </div>

    <!-- Logout Confirmation Popup -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p>Are you sure you want to logout?</p>
            <button id="confirmLogout">Yes</button>
            <button id="cancelLogout">No</button>
        </div>
    </div>

    <script>
        let bookIdToRemove = null;

        function confirmRemove(bookId) {
            bookIdToRemove = bookId; // Store the book ID to be removed
            document.getElementById('confirmModal').style.display = 'block'; // Show the modal
        }

        // Close the modal
        document.getElementById('closeModal').addEventListener('click', function () {
            document.getElementById('confirmModal').style.display = 'none';
        });

        // Confirm deletion and send request to PHP for deletion
        document.getElementById('confirmDelete').addEventListener('click', function () {
            if (bookIdToRemove) {
                // Send a request to the backend to delete the book
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'removebook.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Remove the book from the frontend after successful deletion
                        const bookElement = document.querySelector(`[data-book-id="${bookIdToRemove}"]`);
                        bookElement.remove();
                        document.getElementById('confirmModal').style.display = 'none';
                    }
                };
                xhr.send(`book_id=${bookIdToRemove}`);
            }
        });

        // Get elements
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');
        const confirmLogout = document.getElementById('confirmLogout');
        const cancelLogout = document.getElementById('cancelLogout');
        const closeModal = document.querySelector('.close');

        // Show the popup when the logout button is clicked
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            logoutModal.style.display = 'block';
        });

        // Hide the modal when No or close (X) is clicked
        cancelLogout.addEventListener('click', function () {
            logoutModal.style.display = 'none';
        });

        closeModal.addEventListener('click', function () {
            logoutModal.style.display = 'none';
        });

        // Proceed with logout when Yes is clicked
        confirmLogout.addEventListener('click', function () {
            window.location.href = 'logout.php'; // Redirect to logout.php
        });

        // Close the modal when clicking outside the modal content
        window.onclick = function (event) {
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        };
    </script>
</body>
</html>
