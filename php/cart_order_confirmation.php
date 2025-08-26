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

// Fetch order and associated books details from the order_items table
$sql = "SELECT o.*, b.book_id, b.title, b.author, b.genre, b.price, bi.front_cover_path, 
    u1.username AS seller_name, u1.email AS seller_email, u1.phone_number AS seller_phone, u1.address AS seller_address, 
    u2.username AS buyer_name, u2.email AS buyer_email, u2.phone_number AS buyer_phone, u2.address AS buyer_address,
    o.purchase_option
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN books b ON oi.book_id = b.book_id
        JOIN book_images bi ON b.book_id = bi.book_id
        JOIN users u1 ON o.seller_id = u1.user_id
        JOIN users u2 ON o.buyer_id = u2.user_id
        WHERE o.order_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$order_books = $stmt->fetchAll();

if (!$order_books) {
    echo "Order not found.";
    exit();
}

// Initialize the book titles list for both GET and POST requests
$book_titles = array_column($order_books, 'title');
$book_titles_list = implode(", ", $book_titles);

// Finalize the order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_option = $order_books[0]['purchase_option'];
    $status = 'Pending';

    // Update the status for each book in the order
    foreach ($order_books as $order) {
        if (!isset($order['book_id']) || empty($order['book_id'])) {
            echo "Error: book_id is missing or null.";
            continue; // Skip this entry if book_id is not found
        }

        // Insert into sale_history
        $sql = "INSERT INTO sale_history (seller_id, book_id, order_id, quantity, total_price, sale_date) 
                VALUES (:seller_id, :book_id, :order_id, :quantity, :total_price, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'seller_id' => $order['seller_id'],
            'book_id' => $order['book_id'],
            'order_id' => $order_id,
            'quantity' => 1, // Adjust based on your requirements
            'total_price' => $order['price']
        ]);

        // Insert into purchase_history
        $sql = "INSERT INTO purchase_history (buyer_id, book_id, order_id, quantity, total_price, purchase_date) 
                VALUES (:buyer_id, :book_id, :order_id, :quantity, :total_price, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'buyer_id' => $order['buyer_id'],
            'book_id' => $order['book_id'],
            'order_id' => $order_id,
            'quantity' => 1, // Adjust based on your requirements
            'total_price' => $order['price']
        ]);
    }    

    // Send notifications for each book in the order
    if ($purchase_option === 'Delivery') {
        $sql = "SELECT * FROM delivery_partners ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->query($sql);
        $delivery_partner = $stmt->fetch();

        $notification_seller = "Hello {$order_books[0]['seller_name']}, {$order_books[0]['buyer_name']} has chosen the delivery purchase option to buy '{$book_titles_list}'. " .
            "Our delivery partner {$delivery_partner['partner_name']} will pick up the books from you tomorrow by 9 PM. You can contact them at {$delivery_partner['contact_number']} or via email at {$delivery_partner['email']}.";

        $notification_buyer = "Hello {$order_books[0]['buyer_name']}, you have chosen the delivery purchase option for the books '{$book_titles_list}'. " .
            "Our delivery partner {$delivery_partner['partner_name']} will deliver the books to you within 4-5 days. You can contact them at {$delivery_partner['contact_number']} or via email at {$delivery_partner['email']}.";

        $sql = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $order_books[0]['seller_id'], 'message' => $notification_seller]);
        $stmt->execute(['user_id' => $order_books[0]['buyer_id'], 'message' => $notification_buyer]);
    } else {
        $notification_seller = "Hello {$order_books[0]['seller_name']}, {$order_books[0]['buyer_name']} has chosen the self-pickup option to buy '{$book_titles_list}'.";

        $notification_buyer = "Hello {$order_books[0]['buyer_name']}, you have chosen the self-pickup option for the books '{$book_titles_list}'.";

        $sql = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $order_books[0]['seller_id'], 'message' => $notification_seller]);
        $stmt->execute(['user_id' => $order_books[0]['buyer_id'], 'message' => $notification_buyer]);
    }

    // Update the status of the books to 'Sold'
    foreach ($order_books as $order) {
        $sql = "UPDATE books SET status = 'Pending' WHERE book_id = :book_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['book_id' => $order['book_id']]);
    }

    // Clear the cart for the current user after order is finalized
    $sql = "DELETE FROM cart WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    // Redirect to user profile after finalizing the order
    header("Location: userprofile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="../css/orderconfirmation.css"> <!-- Link to CSS file -->
</head>
<body>
    <div class="container">
        <div class="headline">Transaction ID: <?php echo htmlspecialchars(uniqid('TXN') . '-' . $order_id); ?></div>

        <div class="info">
            <h3>Seller's Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_books[0]['seller_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_books[0]['seller_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_books[0]['seller_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order_books[0]['seller_address']); ?></p>
        </div>

        <div class="info">
            <h3>Buyer's Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order_books[0]['buyer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_books[0]['buyer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order_books[0]['buyer_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order_books[0]['buyer_address']); ?></p>
        </div>

        <hr>

        <div class="order-details">
            <h3>Order Details</h3>
            <?php foreach ($order_books as $order): ?>
                <div class="details">
                    <img src="<?php echo htmlspecialchars($order['front_cover_path']); ?>" alt="Book Cover">
                    <div>
                        <p><strong>Book Name:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
                        <p><strong>Book Author:</strong> <?php echo htmlspecialchars($order['author']); ?></p>
                        <p><strong>Book Genre:</strong> <?php echo htmlspecialchars($order['genre']); ?></p>
                        <p><strong>Book Price:</strong> ₹<?php echo number_format($order['price'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <p><strong>Purchase Option:</strong> <?php echo htmlspecialchars($order_books[0]['purchase_option']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_books[0]['payment_method']); ?></p>
            <?php if ($order_books[0]['purchase_option'] === 'Delivery') : ?>
                <p><strong>Delivery Charges:</strong> ₹50</p>
                <p><strong>Total Price:</strong> ₹<?php echo number_format(array_sum(array_column($order_books, 'price')) + 50, 2); ?></p>
            <?php else: ?>
                <p><strong>Total Price:</strong> ₹<?php echo number_format(array_sum(array_column($order_books, 'price')), 2); ?></p>
            <?php endif; ?>
        </div>

        <form action="" method="post">
            <button id="complete-order" class="btn">Finalize Order</button>
        </form>
    </div>

    <!-- Confirm Order Popup -->
    <div id="confirm-popup" class="popup">
        <div class="popup-content">
            <h2>Confirm Your Order</h2>
            <p>You're about to complete your purchase for "<?php echo htmlspecialchars($book_titles_list); ?>" with a total cost of ₹<?php echo number_format(array_sum(array_column($order_books, 'price')), 2); ?>. Are you sure you want to place this order?</p>
            <form method="post">
                <button id="confirm-order" class="btn">Yes, Place Order</button>
                <button id="cancel-order" class="btn">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Order Confirmation Popup -->
    <div id="confirmation-popup" class="popup">
        <div class="popup-content">
            <p>Order confirmed successfully!</p>
        </div>
    </div>

    <script>
        document.getElementById('complete-order').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default form submission

            var confirmPopup = document.getElementById('confirm-popup');
            confirmPopup.style.display = 'flex'; // Show confirmation popup
        });

        document.getElementById('cancel-order').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default form submission

            var confirmPopup = document.getElementById('confirm-popup');
            confirmPopup.style.display = 'none'; // Close confirmation popup
        });

        document.getElementById('confirm-order').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default form submission

            var confirmPopup = document.getElementById('confirm-popup');
            confirmPopup.style.display = 'none'; // Close confirmation popup

            var confirmationPopup = document.getElementById('confirmation-popup');
            confirmationPopup.style.display = 'flex'; // Show order confirmation popup

            // Submit the form to finalize the order after a delay
            setTimeout(function() {
                var form = document.querySelector('form');
                form.submit();
            }, 2000); // 2 seconds delay
        });
    </script>
</body>
</html>
