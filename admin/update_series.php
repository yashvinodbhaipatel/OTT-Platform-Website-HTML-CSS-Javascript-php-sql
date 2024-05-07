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

// Check if required parameters are set
if (isset($_POST['serieId'], $_POST['title'], $_POST['description'])) {
    $serieId = $_POST['serieId'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Prepare and execute the query to update series details
    $stmt = $pdo->prepare("UPDATE series SET title = :title, description = :description WHERE series_id = :series_id");
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':series_id', $serieId, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        echo "Series details updated successfully.";
    } catch (PDOException $e) {
        echo "Error updating series details: " . $e->getMessage();
    }
} else {
    echo "Required parameters are missing.";
}
?>
