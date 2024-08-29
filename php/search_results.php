<?php
// searchresults.php

include 'new_db_conn .php'; // Include the database connection file

// Get the search query from the request
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

if ($search_query) {
    // Prepare the SQL query with LIKE operator for search functionality
    $sql = "SELECT * FROM books WHERE title LIKE :query OR author LIKE :query OR genre LIKE :query";
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
    <link rel="stylesheet" href="css/searchresults.css">
</head>
<body>
    <header>
        <h1>Book Bazaar</h1>
        <nav>
            <a href="home.php">Home</a>
            <a href="searchresults.php">Search</a>
            <a href="#">Contact</a>
        </nav>
    </header>
    
    <main>
        <section class="search-results">
            <h2>Search Results</h2>
            <div class="grid-container">
                <?php if ($results): ?>
                    <?php foreach ($results as $book): ?>
                        <div class="book-item">
                            <img src="<?php echo $book['image_path'] ?? 'https://via.placeholder.com/250x350'; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                            <p>Price: $<?php echo number_format($book['price'], 2); ?></p>
                            <a href="bookdetails.php?id=<?php echo $book['book_id']; ?>" class="header-link">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Book Bazaar</p>
    </footer>
</body>
</html>
