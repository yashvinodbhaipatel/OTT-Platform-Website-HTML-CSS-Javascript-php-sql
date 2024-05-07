<?php
// Start session and check if the user is logged in
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Check if series ID is provided in the URL
if (!isset($_GET['series_id'])) {
    header("Location: home.php"); // Redirect to home page if series ID is not provided
    exit();
}

// Database configuration
$host = "localhost";
$dbname = "ott";
$username = "root";
$password = "";

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch series details based on the provided series ID from seasons table
$seriesId = $_GET['series_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = :id");
    $stmt->execute(['id' => $seriesId]);
    $series = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching series data: " . $e->getMessage());
}


// Fetch episode details based on the series ID from episodes table
// Fetch episode details based on the series ID from episodes table
try {
    $stmt = $pdo->prepare("SELECT e.file_name, e.episode_number, s.season_number 
                            FROM episodes e 
                            JOIN seasons s ON e.season_id = s.id 
                            WHERE s.series_id = :series_id");
    $stmt->execute(['series_id' => $seriesId]);
    $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching episode data: " . $e->getMessage());
}


// Database configuration
$host = "localhost";
$dbname = "ott";
$username = "root";
$password = "";

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// Fetch series details based on the provided series ID
$seriesId = $_GET['series_id'];
$stmt = $pdo->prepare("SELECT * FROM series WHERE series_id = :series_id");
$stmt->execute(['series_id' => $seriesId]);
$series = $stmt->fetch(PDO::FETCH_ASSOC);



// Check if episode exists
if (!$episodes || !is_array($episodes)) {
    // Handle the case where no episode is found for the series
    // For example, you could display a message indicating that no episodes are available
    $episodes = array(); // Set episodes to an empty array to avoid errors
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($series['title']); ?> - Series Detail</title>
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* Add your CSS styles here */
        /* Example styles for demonstration */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #000;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
        }

        header img {
            max-width: 50px;
        }

        nav ul {
            display: flex;
        }

        nav ul li {
            margin-right: 15px;
        }

        nav a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            font-size: 24px;
        }

        main {
            padding: 20px;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-top: 80px;
        }

        .series-poster {
            margin-right: 20px;
            max-width: 300px;
        }

        .series-poster img {
            max-width: 100%;
        }

        .series-description {
            max-width: 50%;
        }

        .series-description h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .series-description p {
            font-size: 16px;
            color: #888;
        }

        .watch-series-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }

        .watch-series-btn:hover {
            background-color: #0056b3;
        }

        header {
            position: fixed;
            width: 98%;
            z-index: 1000;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        nav a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            font-size: 15px;
        }

        nav ul {
            text-align: center;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            display: inline-block;
            margin: 0 10px;
        }

        nav ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            /* Adjust color as needed */
        }

        nav ul li a i {
            margin-right: 5px;
            font-size: 24px;
        }

        @media screen and (max-width: 600px) {

            header {
                position: fixed;
                width: 98%;
                z-index: 1000;
                background-color: #000;
                color: #fff;
                padding: 7px 5px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            nav a {
                text-decoration: none;
                color: #fff;
                font-weight: bold;
                font-size: 10px;
            }

            nav ul li a i {
                font-size: 17px;
            }

            nav ul li {
                display: inline-block;
                margin: 0px 2px;
            }

            nav a {
                text-decoration: none;
                color: #fff;
                font-weight: bold;
                font-size: 8px;
            }
        }

        /* Add CSS styles for the dropdown */
        #episodeSelect {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Style for option groups */
        .season-group {
            font-weight: bold;
            color: #007bff;
            /* You can adjust the color as needed */
        }

        /* Style for individual options */
        .episode-option {
            padding: 5px;
        }
         label {
            font-size: 18px;
            margin-bottom: 10px;
            display: block;
            color: #333; /* Adjust the color as needed */
        }
        
    </style>
</head>

<body>
    <header>
        <img src="your-logo.png" alt="Your Logo">
        <nav>
            <ul>
                <li><a href="home.php"><i class='bx bx-home'></i> home</a></li>
                <li><a href="series.php"><i class='bx bx-tv'></i> Series</a></li>
                <li><a href="movie.php"><i class='bx bx-movie-play'></i> Movie</a></li>
                <li><a href="premium.php"><i class='bx bx-wallet-alt'></i> Premium</a></li>
                <li><a href="profile.php"><i class='bx bx-user'></i> Profile</a></li>
                <li><a href="search.php"><i class='bx bx-search'></i> Search</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Display series poster -->
        <div class="series-poster">
            <img src="<?php echo htmlspecialchars($series['image_path']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>">
        </div>
        <!-- Display series description -->
        <div class="series-description">
            <h2><?php echo htmlspecialchars($series['title']); ?></h2>
            <p><?php echo htmlspecialchars($series['description']); ?></p>
            <!-- Display episode dropdown -->
            <label for="episodeSelect">Select Episode:</label>
            <select id="episodeSelect">
                <?php foreach ($episodes as $episode) : ?>
                    <?php if (!isset($currentSeason) || $currentSeason != $episode['season_number']) : ?>
                        <?php if (isset($currentSeason)) : ?>
                            </optgroup> <!-- Close previous season group -->
                        <?php endif; ?>
                        <optgroup label="Season <?php echo htmlspecialchars($episode['season_number']); ?>" class="season-group">
                        <?php endif; ?>
                        <option value="<?php echo htmlspecialchars($episode['file_name']); ?>" class="episode-option">
                            Episode <?php echo htmlspecialchars($episode['episode_number']); ?>
                        </option>
                        <?php $currentSeason = $episode['season_number']; ?>
                    <?php endforeach; ?>
                        </optgroup> <!-- Close the last season group -->
            </select>
            <!-- Add watch series button -->
            <button class="watch-series-btn" onclick="playEpisode()">Watch Episode</button>
        </div>
    </main>


    <script>
        // JavaScript function to play the selected episode
        function playEpisode() {
            var select = document.getElementById("episodeSelect");
            var selectedFileName = select.options[select.selectedIndex].value;
            // Redirect to the selected episode's file
            window.open(selectedFileName, "_blank");
        }
    </script>
</body>

</html>