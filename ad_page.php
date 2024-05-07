<?php
// Database connection
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

// Fetch advertisements from the database
try {
    $stmt = $pdo->query("SELECT * FROM ad ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching advertisements: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advertisement Page</title>
    <style>
         ::-webkit-scrollbar {
                display: none;
            }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #111;
            color: #fff;
            padding: 20px;
        }
        .content-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 100px; /* Set the maximum width of the logo */
            height: auto; /* Maintain aspect ratio */
        }
        .ad-image {
            max-width: 65%;
            margin-bottom: 20px;
            position: relative; /* Add position relative to image */
        }
        .skip-button {
            background-color: #e50914;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            position: absolute; /* Set position absolute */
            top: 10px; /* Adjust top position as needed */
            right: 10px; /* Adjust right position as needed */
        }
    </style>
</head>
<body>

    <div class="content-container">
        <div class="latest-ad">
            <?php if ($row && isset($row['ad_image']) && isset($row['ad_title'])): ?>
                <!-- Display advertisement -->
                <img id="adImage" src="admin/<?php echo $row['ad_image']; ?>" class="ad-image" alt="<?php echo $row['ad_title']; ?>">
                <h3><?php echo $row['ad_title']; ?></h3>
                <button id="skipButton" class="skip-button">Skip</button>
            <?php else: ?>
                <!-- No advertisement available -->
                <p>No advertisement available.</p>                                              
            <?php endif; ?>
        </div>
        <div class="logo">
            <img src="OIP.png" alt="Website Logo">
        </div>
    </div>
    <script>
        document.getElementById("skipButton").addEventListener("click", function() {
            window.location.href = "home.php"; 
        });

        // Auto skip after 6 seconds
        setTimeout(function() {
            window.location.href = "home.php"; 
        }, 6000);
    </script>
</body>
</html>
