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

// Function to delete series, seasons, and episodes
function deleteSeries($pdo, $serieId)
{
    // Start a transaction
    $pdo->beginTransaction();

    try {
        // Delete episodes belonging to seasons of the series
        $stmt = $pdo->prepare("DELETE episodes FROM episodes JOIN seasons ON episodes.season_id = seasons.id WHERE seasons.series_id = ?");
        $stmt->execute([$serieId]);

        // Delete seasons of the series
        $stmt = $pdo->prepare("DELETE FROM seasons WHERE series_id = ?");
        $stmt->execute([$serieId]);

        // Delete the series itself
        $stmt = $pdo->prepare("DELETE FROM series WHERE series_id = ?");
        $stmt->execute([$serieId]);

        // Commit the transaction
        $pdo->commit();

        // Return success message or indication
        return true;
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $pdo->rollback();

        // Return error message or indication
        return false;
    }
}

// Check if serieId is provided via POST request
if (isset($_POST['serieId'])) {
    $serieId = $_POST['serieId'];

    // Call the deleteSeries function and handle the result
    $result = deleteSeries($pdo, $serieId);
    if ($result) {
        echo "Series deleted successfully.";
    } else {
        echo "Error deleting series.";
    }
} else {
    echo "Invalid request.";
}
?>
