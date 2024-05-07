<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Replace these values with your actual database credentials
$host = "localhost";
$dbname = "ott";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contentTitle = $_POST["contentTitle"]; // Fetch the title from the form
    $uploadingDate = $_POST["uploadingDate"]; // Fetch the uploading date from the form
    $description = $_POST["description"]; // Fetch the description from the form
    $currentStatus = $_POST["currentStatus"]; // Fetch the current status from the form

    // Initialize variables for file paths
    $videoPath = "";
    $imagePath = "";
    $slideshowImagePaths = array();

    // Function to generate unique file name
    function generateUniqueFileName($fileName) {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $basename = pathinfo($fileName, PATHINFO_FILENAME);
        return $basename . '_' . uniqid() . '.' . $extension;
    }

    // Process video upload
    if ($currentStatus !== "upcoming") {
        $videoFile = $_FILES["videoFile"];
        if ($videoFile["error"] === UPLOAD_ERR_OK) {
            $uploadDir = "../uploads/";
            $videoFileName = generateUniqueFileName($videoFile["name"]);
            $videoPath = $uploadDir . $videoFileName;
            if (!move_uploaded_file($videoFile["tmp_name"], $videoPath)) {
                die("Error uploading video file");
            }
        } else {
            die("Error uploading video file: " . $videoFile["error"]);
        }
    }

    // Process image upload
    $imageFile = $_FILES["imageFile"];
    if ($imageFile["error"] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";
        $imageFileName = generateUniqueFileName($imageFile["name"]);
        $imagePath = $uploadDir . $imageFileName;
        if (!move_uploaded_file($imageFile["tmp_name"], $imagePath)) {
            die("Error uploading image file");
        }
    } else {
        die("Error uploading image file: " . $imageFile["error"]);
    }

    // Process slideshow image upload
    if ($currentStatus !== "upcoming") {
        $slideshowImages = $_FILES["slideshowImages"];
        if (!empty($slideshowImages['name'][0])) {
            foreach ($slideshowImages['name'] as $key => $imageName) {
                $uploadDir = "../uploads/";
                $slideshowImageFileName = generateUniqueFileName($imageName);
                $slideshowImagePath = $uploadDir . $slideshowImageFileName;
                $slideshowImagePaths[] = $slideshowImagePath;
                if (!move_uploaded_file($slideshowImages['tmp_name'][$key], $slideshowImagePath)) {
                    die("Error uploading slideshow image: " . $imageName);
                }
            }
        }
    }

    try {
        // Insert data into the database
        $stmt = $pdo->prepare("INSERT INTO links (video_link, image_link, title, uploading_date, description, current_status) 
                               VALUES (:videoLink, :imageLink, :title, :uploadingDate, :description, :currentStatus)");
        // Remove "../" from paths before insertion
        $videoPath = str_replace('../', '', $videoPath);
        $imagePath = str_replace('../', '', $imagePath);
        $stmt->bindParam(':videoLink', $videoPath);
        $stmt->bindParam(':imageLink', $imagePath);
        $stmt->bindParam(':title', $contentTitle);
        $stmt->bindParam(':uploadingDate', $uploadingDate);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':currentStatus', $currentStatus);
        $stmt->execute();

        // Get the last inserted ID
        $lastInsertId = $pdo->lastInsertId();

        // Insert slideshow image paths into the database
        if (!empty($slideshowImagePaths)) {
            foreach ($slideshowImagePaths as $slideshowImagePath) {
                // Remove "../" from paths before insertion
                $slideshowImagePath = str_replace('../', '', $slideshowImagePath);
                $stmt = $pdo->prepare("INSERT INTO slideshow_images (link_id, slideshow_image_path) VALUES (:linkId, :slideshowImagePath)");
                $stmt->bindParam(':linkId', $lastInsertId);
                $stmt->bindParam(':slideshowImagePath', $slideshowImagePath);
                $stmt->execute();
            }
        }

        header("Location: success.php");
        exit();
    } catch (PDOException $e) {
        // Handle the database error appropriately
        die("Database error: " . $e->getMessage());
    }
}
?>
