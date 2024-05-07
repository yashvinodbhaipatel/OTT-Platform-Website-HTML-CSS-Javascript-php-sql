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

// Check if userId is provided in the request
if (isset($_POST['userId'])) {
    // Sanitize the input
    $userId = $_POST['userId'];
    try {
        // Prepare and execute the delete query
        $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->execute([$userId]);
        // Check if the deletion was successful
        if ($stmt->rowCount() > 0) {
            echo "User deleted successfully";
        } else {
            echo "Failed to delete user: User not found";
        }
    } catch (PDOException $e) {
        // Log any errors that occur during the deletion process
        echo "Failed to delete user: " . $e->getMessage();
    }
} else {
    echo "User ID not provided";
}
?>
