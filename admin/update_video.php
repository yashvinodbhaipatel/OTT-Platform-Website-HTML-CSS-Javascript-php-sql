 <?php
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
    // Return an error response if database connection fails
    http_response_code(500);
    die("Database connection failed: " . $e->getMessage());
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the POST request
    $videoId = $_POST['videoId'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    try {
        // Prepare SQL statement to update the video details
        $stmt = $pdo->prepare("UPDATE links SET title = :title, description = :description WHERE id = :videoId");
        // Bind parameters
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':videoId', $videoId);
        // Execute the update query
        $stmt->execute();

        // Return a success response
        echo "Video details updated successfully.";
    } catch (PDOException $e) {
        // Return an error response if the update query fails
        http_response_code(500);
        echo "Error updating video details: " . $e->getMessage();
    }
} else {
    // Return an error response if the request method is not POST
    http_response_code(405); // Method Not Allowed
    echo "Invalid request method. Only POST requests are allowed.";
}
?>
