<?php
// searchresults.php

include 'db_connection.php'; // Include the database connection file

// Get the search query from the request
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

if ($search_query) {
    // Prepare the SQL query with LIKE operator for search functionality
    $sql = "SELECT b.*, bi.front_cover_path 
        FROM books b
        LEFT JOIN book_images bi ON b.book_id = bi.book_id
        WHERE (b.title LIKE :query OR b.author LIKE :query OR b.genre LIKE :query)
        AND b.status = 'Available'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$search_query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $results = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bazaar - Search Results</title>
    <link rel="stylesheet" href="../css/searchresults.css"> <!-- Ensure this path is correct -->
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
    <main>
        <section class="search-results">
            <h2>Search Results</h2>
            <div class="grid-container">
                <?php if ($results): ?>
                    <?php foreach ($results as $book): ?>
                        <a href="bookdetails.php?id=<?php echo $book['book_id']; ?>" class="book-item-link">
                            <div class="book-item">
                                <img src="<?php echo htmlspecialchars($book['front_cover_path']) ?: 'https://via.placeholder.com/250x350'; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                                <p>Price: ₹<?php echo number_format($book['price'], 2); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No books found.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var searchQuery = new URLSearchParams(window.location.search).get('query');
            
            if (searchQuery) {
                // Store the query in session storage
                sessionStorage.setItem('search_query', searchQuery);
            } else {
                // Check if session storage has a query
                searchQuery = sessionStorage.getItem('search_query');
                
                if (searchQuery) {
                    // Redirect to the same page with the query if not in URL
                    window.location.search = 'query=' + encodeURIComponent(searchQuery);
                }
            }
        });
    </script>


</body>
</html>
