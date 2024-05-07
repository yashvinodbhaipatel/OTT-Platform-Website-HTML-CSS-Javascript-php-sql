<?php
session_start();
// Check if the user is logged in, redirect to login if not
if (!isset($_SESSION['user_email'])) {
    header("Location: ../login.php"); // Redirect to your login page
    exit();
}

// Database connection details
$host = "localhost";
$dbname = "ott";
$username = "root";
$password = "";

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function deleteVideo($pdo, $videoId)
{
    try {
        $pdo->beginTransaction();

        // Delete associated records from slideshow_images table
        $stmtImages = $pdo->prepare("DELETE FROM slideshow_images WHERE link_id = ?");
        $stmtImages->execute([$videoId]);

        // Delete the video record from the links table
        $stmt = $pdo->prepare("DELETE FROM links WHERE id = ?");
        $stmt->execute([$videoId]);

        $pdo->commit();

        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

// Check if videoId is provided via POST request
if (isset($_POST['videoId'])) {
    $videoId = $_POST['videoId'];

    // Call the deleteVideo function and handle the result
    $result = deleteVideo($pdo, $videoId);
    if ($result) {
        echo "Video deleted successfully.";
    } else {
        echo "Error deleting video.";
    }

    // Redirect to video_management.php after deletion
    header("Location: video_management.php");
    exit();
} else {
    echo "Invalid request.";
}
?>
