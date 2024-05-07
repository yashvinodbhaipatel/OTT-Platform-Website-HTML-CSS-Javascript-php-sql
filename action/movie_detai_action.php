<?php
// Connect to your database (replace with your database credentials)
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "ott";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch movie details based on the movie ID
if (isset($_GET['Id'])) {
    $Id = $_GET['Id'];
    $sql = "SELECT * FROM links WHERE id = $Id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data of each row
        $row = $result->fetch_assoc();
        echo "<h2>" . $row['title'] . "</h2>";
        echo "<p>Director: " . $row['director'] . "</p>";
        echo "<p>Release Year: " . $row['release_year'] . "</p>";
    } else {
        echo "Movie not found";
    }
}

$conn->close();
?>
