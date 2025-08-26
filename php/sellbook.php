<?php
session_start();
include 'db_connection.php'; // Include your PDO connection file

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not found!']);
    exit();
}

$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found!']);
    exit();
}

$user_id = $user['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $condition = $_POST['condition'];
    $price = $_POST['price'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle file uploads
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // Initialize image paths
    $frontCoverPath = null;
    $backCoverPath = null;
    $innerPage1Path = null;
    $innerPage2Path = null;

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
                echo json_encode(['status' => 'error', 'message' => "Failed to upload image $i."]);
                exit;
            }
        }
    }

    try {
        $pdo->beginTransaction();

        // Insert book data with user_id
        $stmt = $pdo->prepare("INSERT INTO books (user_id, title, author, genre, description, price, `condition`) VALUES (:user_id, :title, :author, :genre, :description, :price, :condition)");
        $stmt->execute([
            ':user_id' => $user_id,
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

        // Insert location data
        $stmt = $pdo->prepare("INSERT INTO locations (book_id, latitude, longitude) VALUES (:book_id, :latitude, :longitude)");
        $stmt->execute([
            ':book_id' => $bookId,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);

        $pdo->commit();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Order confirmed successfully!']);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        exit();
    }
}
?>
