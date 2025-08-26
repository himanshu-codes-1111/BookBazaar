<?php
require 'db_connection.php'; // Assuming this file returns the $pdo object

$genre = $_GET['genre'] ?? null;

if ($genre) {
    try {
        // Prepare and execute the query to fetch books along with their images
        $stmt = $pdo->prepare("
            SELECT b.*, bi.front_cover_path 
            FROM books b
            LEFT JOIN book_images bi ON b.book_id = bi.book_id
            WHERE b.genre = :genre
            AND b.status = 'Available'
        ");

        $stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No genre selected.";
    exit(); // Stop further execution if no genre is provided
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books in <?= htmlspecialchars($genre) ?></title>
    <link rel="stylesheet" href="../css/searchresults.css"> <!-- Keep this path as it is -->
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
        <h2>Books in <?= htmlspecialchars($genre) ?></h2>
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
                <p style="text-align: center; font-size: 1.5em; margin-top: 20px;">Sorry, no books available for <?= htmlspecialchars($genre) ?> genre.</p>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
