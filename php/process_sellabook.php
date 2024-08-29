<?php
include 'new_db_conn.php'; // Include your PDO connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $condition = $_POST['condition'];
    $price = $_POST['price'];

    // Handle file uploads
    $uploadDir = '../uploads/'; // Adjusted path to go up one directory from 'php/'

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true); // Create the uploads directory if it doesn't exist
    }

    // Initialize image paths
    $frontCoverPath = null;
    $backCoverPath = null;
    $innerPage1Path = null;
    $innerPage2Path = null;

    // Assign paths to the appropriate variables
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES["image$i"]['tmp_name'];
            $fileName = basename($_FILES["image$i"]['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                switch ($i) {
                    case 1:
                        $frontCoverPath = $filePath;
                        break;
                    case 2:
                        $backCoverPath = $filePath;
                        break;
                    case 3:
                        $innerPage1Path = $filePath;
                        break;
                    case 4:
                        $innerPage2Path = $filePath;
                        break;
                }
            } else {
                echo "Failed to upload image $i.";
                exit;
            }
        }
    }

    // Insert book details into the database
    try {
        $pdo->beginTransaction();

        // Insert book data
        $stmt = $pdo->prepare("INSERT INTO books (title, author, genre, description, price, `condition`) VALUES (:title, :author, :genre, :description, :price, :condition)");
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':genre' => $genre,
            ':description' => $description,
            ':price' => $price,
            ':condition' => $condition
        ]);
        $bookId = $pdo->lastInsertId();

        // Insert image data
        $stmt = $pdo->prepare("INSERT INTO book_images (book_id, front_cover_path, back_cover_path, inner_page1_path, inner_page2_path) VALUES (:book_id, :front_cover_path, :back_cover_path, :inner_page1_path, :inner_page2_path)");
        $stmt->execute([
            ':book_id' => $bookId,
            ':front_cover_path' => $frontCoverPath,
            ':back_cover_path' => $backCoverPath,
            ':inner_page1_path' => $innerPage1Path,
            ':inner_page2_path' => $innerPage2Path
        ]);

        $pdo->commit();
        echo "Book successfully listed!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo 'Error: ' . $e->getMessage();
    }
}
?>
