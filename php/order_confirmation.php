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

    // Fetch order and order item details
    $sql = "SELECT o.*, oi.book_id, oi.quantity, oi.price, b.title, b.author, b.genre, bi.front_cover_path, 
    u1.username AS seller_name, u1.email AS seller_email, u1.phone_number AS seller_phone, u1.address AS seller_address, 
    u2.username AS buyer_name, u2.email AS buyer_email, u2.phone_number AS buyer_phone, u2.address AS buyer_address,
    o.purchase_option
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id  -- Ensure you're getting book_id, quantity, and price from order_items
    JOIN books b ON oi.book_id = b.book_id  -- Join with books to get book details
    JOIN book_images bi ON b.book_id = bi.book_id
    JOIN users u1 ON o.seller_id = u1.user_id
    JOIN users u2 ON o.buyer_id = u2.user_id
    WHERE o.order_id = ?";


    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo "Order not found.";
        exit();
    }

    // Finalize the order
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $purchase_option = $order['purchase_option'];
        $status = 'pending';
        
        // Update book status to 'pending'
        $sql = "UPDATE books SET status = :status WHERE book_id = :book_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['status' => $status, 'book_id' => $order['book_id']]);

        // Insert into sale_history
        $sql = "INSERT INTO sale_history (seller_id, book_id, order_id, quantity, total_price, sale_date) 
                VALUES (:seller_id, :book_id, :order_id, :quantity, :total_price, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'seller_id' => $order['seller_id'],
            'book_id' => $order['book_id'],
            'order_id' => $order_id,
            'quantity' => $order['quantity'], // Corrected for quantity
            'total_price' => $order['price'], // You're fetching price in the query now

        ]);

        // Insert into purchase_history
        $sql = "INSERT INTO purchase_history (buyer_id, book_id, order_id, quantity, total_price, purchase_date) 
                VALUES (:buyer_id, :book_id, :order_id, :quantity, :total_price, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'buyer_id' => $order['buyer_id'],
            'book_id' => $order['book_id'],
            'order_id' => $order_id,
            'quantity' => $order['quantity'], // Adjust based on your requirements
            'total_price' => $order['price']
        ]);

        // Send notifications
        if ($purchase_option === 'Delivery') {
            $sql = "SELECT * FROM delivery_partners ORDER BY RAND() LIMIT 1";
            $stmt = $pdo->query($sql);
            $delivery_partner = $stmt->fetch();

            $notification_seller = "Hello {$order['seller_name']}, {$order['buyer_name']} has chosen the delivery purchase option to buy '{$order['title']}'. " .
                "Our delivery partner {$delivery_partner['partner_name']} will pick up the book from you tomorrow by 9 PM. You can contact them at {$delivery_partner['contact_number']} or via email at {$delivery_partner['email']}.";

            $notification_buyer = "Hello {$order['buyer_name']}, you have chosen the delivery purchase option for the book '{$order['title']}'. " .
                "Our delivery partner {$delivery_partner['partner_name']} will deliver the book to you within 4-5 days. You can contact them at {$delivery_partner['contact_number']} or via email at {$delivery_partner['email']}.";

            $sql = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $order['seller_id'], 'message' => $notification_seller]);
            $stmt->execute(['user_id' => $order['buyer_id'], 'message' => $notification_buyer]);
        } else {
            $notification_seller = "Hello {$order['seller_name']}, {$order['buyer_name']} has chosen the self-pickup option to buy '{$order['title']}'.";

            $notification_buyer = "Hello {$order['buyer_name']}, you have chosen the self-pickup option for the book '{$order['title']}'.";

            $sql = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $order['seller_id'], 'message' => $notification_seller]);
            $stmt->execute(['user_id' => $order['buyer_id'], 'message' => $notification_buyer]);
        }

        // Redirect after finalizing the order
        $search_query = isset($_SESSION['search_query']) ? $_SESSION['search_query'] : '';
        header("Location: search_results.php?query=" . urlencode($search_query));
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
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['seller_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['seller_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['seller_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['seller_address']); ?></p>
        </div>

        <div class="info">
            <h3>Buyer's Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['buyer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['buyer_phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($order['buyer_address']); ?></p>
        </div>

        <hr>

        <div class="order-details">
    <h3>Order Details</h3>
    <div class="details">
        <img src="<?php echo htmlspecialchars($order['front_cover_path']); ?>" alt="Book Cover">
        <div>
            <p><strong>Book Name:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
            <p><strong>Book Author:</strong> <?php echo htmlspecialchars($order['author']); ?></p>
            <p><strong>Book Genre:</strong> <?php echo htmlspecialchars($order['genre']); ?></p>
            <p><strong>Book Price:</strong> ₹<?php echo number_format($order['price'], 2); ?></p>
            <p><strong>Purchase Option:</strong> <?php echo htmlspecialchars($order['purchase_option']); ?></p>
            <?php if ($order['purchase_option'] === 'Delivery') : ?>
                <p><strong>Delivery Charges:</strong> ₹50</p>
            <?php endif; ?>
        </div>
    </div>
    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Total Price:</strong> ₹<?php echo number_format($order['total_price'], 2); ?></p>
    <button id="complete-order" class="btn">Finalize Order</button> <!-- Move the button here -->
</div>

    </div>

    <!-- Confirm Order Popup -->
    <div id="confirm-popup" class="popup">
        <div class="popup-content">
            <h2>Confirm Your Order</h2>
            <p>You're about to complete your purchase for "<?php echo htmlspecialchars($order['title']); ?>" with a total cost of ₹<?php echo number_format($order['total_price'], 2); ?>. Are you sure you want to place this order?</p>
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
