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

// Function to fetch videos with pagination and search
function fetchVideos($pdo, $offset, $limit, $searchQuery = null)
{
    // Base query
    $query = "SELECT id, title, video_link, image_link, description, current_status, uploading_date FROM links";

    // If a search query is provided, add the WHERE clause
    if ($searchQuery !== null) {
        $query .= " WHERE title LIKE :searchQuery OR description LIKE :searchQuery";
    }

    // Add LIMIT and OFFSET clauses for pagination
    $query .= " LIMIT :offset, :limit";

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);

    // Bind parameters
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    // If a search query is provided, bind the search parameter
    if ($searchQuery !== null) {
        $searchParam = "%$searchQuery%";
        $stmt->bindParam(':searchQuery', $searchParam, PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pagination parameters
$limit = 15; // Number of videos per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get search query if provided
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;

// Fetch videos from the database
$videos = fetchVideos($pdo, $offset, $limit, $searchQuery);

// Check if $videos is not null
if ($videos !== null && !empty($videos)) {
    // Proceed with displaying the videos
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - User Management</title>
        <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <style>
            /* Reset CSS */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            /* Global styles */
            body {
                width: 100%;
                font-family: Arial, sans-serif;
                background-color: #f5f5f5;
                color: #333;
                line-height: 1.6;
                padding-top: 60px;
                /* Adjusted padding to accommodate the fixed header */
            }

            .container {
                max-width: 95%;
                /* Adjusted max-width for better fit on smaller screens */
                margin: 0 auto;
                padding: 20px 10px;
                /* Adjusted padding for better spacing on smaller screens */
            }

            /* Header styles */
            header {
                background-color: #000;
                color: #fff;
                padding: 10px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                position: fixed;
                /* Fixed position */
                top: 0;
                /* Fixed to the top */
                width: 100%;
                /* Full width */
                z-index: 1000;
                /* Ensure it's above other content */
            }

            header img {
                max-width: 50px;
            }

            nav ul {
                list-style: none;
                display: flex;
            }

            nav ul li {
                margin-right: 15px;
            }

            nav a {
                text-decoration: none;
                color: #fff;
                font-weight: bold;
            }

            nav ul li:hover::before {
                content: attr(title);
                position: absolute;
                background-color: #000;
                color: #fff;
                padding: 5px;
                border-radius: 5px;
                font-size: 12px;
                white-space: nowrap;
                top: -30px;
                left: 50%;
                transform: translateX(-50%);
            }

            nav ul li a i {
                font-size: 24px;
            }



            /* Button styles */
            button {
                padding: 8px 15px;
                border: none;
                background-color: #007bff;
                color: #fff;
                cursor: pointer;
                border-radius: 4px;
                transition: background-color 0.3s;
            }

            button:hover {
                background-color: #0056b3;
            }

            button.save {
                background-color: #28a745;
            }

            button.save:hover {
                background-color: #218838;
            }

            button.delete {
                background-color: #dc3545;
            }

            button.delete:hover {
                background-color: #c82333;
            }

            /* Pagination styles */
            .pagination {
                margin-top: 20px;
                text-align: center;
            }

            .pagination a {
                color: #007bff;
                display: inline-block;
                padding: 8px 16px;
                text-decoration: none;
                transition: background-color 0.3s;
            }

            .pagination a.active {
                background-color: #007bff;
                color: white;
            }

            .pagination a:hover:not(.active) {
                background-color: #ddd;
            }

            /* Search form styles */
            .search-form {
                margin-bottom: 20px;
                text-align: center;
            }

            .search-form input[type="text"] {
                padding: 8px;
                width: 150px;
                border-radius: 4px;
                border: 1px solid #ccc;
            }

            .search-form button {
                padding: 8px 15px;
                border: none;

                background-color: #007bff;
                color: #fff;
                cursor: pointer;
                border-radius: 4px;
                transition: background-color 0.3s;
            }

            .search-form button:hover {
                background-color: #0056b3;
            }

            .des {
                height: 30px;
                /* Set the height of the input field */
                width: 100%;
                /* Set the width of the input field to fill its container */
                padding: 8px;
                /* Add padding to provide space inside the input field */
                border: 1px solid #ccc;
                /* Add a border with a light gray color */
                border-radius: 4px;
                /* Add rounded corners to the input field */
                box-sizing: border-box;
                /* Ensure that padding and border are included in the width */
                font-size: 14px;
                /* Set the font size of the text in the input field */
                margin-top: 5px;
                /* Add some space at the top of the input field */
                margin-bottom: 5px;
                /* Add some space at the bottom of the input field */
            }

            /* Style for when the input field is focused */
            .des:focus {
                border-color: #007bff;
                /* Change the border color to blue when the input field is focused */
                outline: none;
                /* Remove the default focus outline */
            }

            .ti {
                height: 30px;
                /* Set the height of the input field */
                width: 100%;
                /* Set the width of the input field to fill its container */
                padding: 8px;
                /* Add padding to provide space inside the input field */
                border: 1px solid #ccc;
                /* Add a border with a light gray color */
                border-radius: 4px;
                /* Add rounded corners to the input field */
                box-sizing: border-box;
                /* Ensure that padding and border are included in the width */
                font-size: 14px;
                /* Set the font size of the text in the input field */
                margin-top: 5px;
                /* Add some space at the top of the input field */
                margin-bottom: 5px;
                /* Add some space at the bottom of the input field */
            }

            /* Style for when the input field is focused */
            .ti:focus {
                border-color: #007bff;
                /* Change the border color to blue when the input field is focused */
                outline: none;
                /* Remove the default focus outline */
            }

            main.table {
                width: 99vw;
                height: 90vh;
                background-color: #fff5;
                margin-top: 0.5%;
                margin-left: 0.5%;
                backdrop-filter: blur(7px);
                box-shadow: 0 0.4rem 0.8rem #0005;
                border-radius: .8rem;
                overflow: hidden;
            }
        </style>

    </head>

    <body>
        <header>
            <img src="../your-logo.png" alt="Your Logo">
            <nav>
                <ul>
                    <!-- Add navigation links -->
                    <li><a href="admin_home.php"><i class='bx bxs-home-alt-2'></i></a></li>
                    <li><a href="upload.html"><i class='bx bx-upload'></i></a></li>
                    <li><a href="user.php"><i class='bx bxs-user'></i></i></a></li>
                    <li><a href="video_management.php"><i class='bx bxs-videos'></i></i></a></li>
                    <li><a href="payment_managemant.php"><i class='bx bxs-purchase-tag'></i></i></i></a></li>
                    <li><a href="ad_upload.php"><i class='bx bx-cloud-upload'></i></a></li>
                    <li><a href="../signout.php"><i class='bx bx-log-out-circle'></i></a></li>
                </ul>
            </nav>
        </header>

        <main class="table" id="customers_table">
            <section class="table__header">
                <h1>Video Management</h1>
                <button class="toggle-button" onclick="switchToMovieUpload()">Go To Series Management</button>
                <div class="input-group">
                    <input type="search" placeholder="Search Data...">
                    <img src="images/search.png" alt="">
                </div>
                <div class="export__file">
                    <label for="export-file" class="export__file-btn" title="Export File"></label>
                    <input type="checkbox" id="export-file">
                    <div class="export__file-options">
                        <label>Export As &nbsp; &#10140;</label>
                        <label for="export-file" id="toJSON">JSON <img src="images/json.png" alt=""></label>
                        <label for="export-file" id="toCSV">CSV <img src="images/csv.png" alt=""></label>
                    </div>
                </div>

            </section>

            <section class="table__body">

                <table>
                    <thead>
                        <tr>
                            <th>ID<span class="icon-arrow">&UpArrow;</span></th>
                            <th>Title<span class="icon-arrow">&UpArrow;</span></th>
                            <th>Description<span class="icon-arrow">&UpArrow;</span></th>
                            <th>Edit</th> <!-- Separate column for Edit button -->
                            <th>Delete</th> <!-- Separate column for Delete button -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videos as $video) : ?>
                            <tr data-id="<?php echo $video['id']; ?>">
                                <td><?php echo $video['id']; ?></td>
                                <td class="title"><?php echo $video['title']; ?></td>
                                <td class="description"><?php echo $video['description']; ?></td>
                                <td> <!-- Edit button column -->
                                    <button class="edit" onclick="editVideo(this)">Edit</button>
                                    <button class="save" style="display:none;" onclick="saveVideo(this)">Save</button>
                                </td>
                                <td>
                                    <form method="post" action="video_delete.php">
                                        <input type="hidden" name="videoId" value="<?php echo $video['id']; ?>">
                                        <button class="delete" type="submit">Delete</button>
                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>


                </table>
                <?php if (!empty($videos)) : ?>
                    <div class="pagination">
                        <a href="?page=<?php echo $page - 1 . ($searchQuery !== null ? '&search=' . urlencode($searchQuery) : ''); ?>" <?php if ($page == 1) echo "style='display:none;'" ?>>Previous</a>
                        <a href="?page=<?php echo $page + 1 . ($searchQuery !== null ? '&search=' . urlencode($searchQuery) : ''); ?>">Next</a>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <script>
            function switchToMovieUpload() {
                window.location.href = 'series_man.php';
            }

            function editVideo(button) {
                var row = button.parentElement.parentElement;
                var titleCell = row.querySelector('.title');
                var descriptionCell = row.querySelector('.description');

                // Check if input fields already exist, if so, update their values
                var titleInput = titleCell.querySelector('input[type="text"]');
                var descriptionInput = descriptionCell.querySelector('input[type="text"]');

                if (!titleInput) {
                    var title = titleCell.textContent;
                    titleCell.innerHTML = '<input type="text"  class="ti" value="' + title + '">';
                }

                if (!descriptionInput) {
                    var description = descriptionCell.textContent;
                    descriptionCell.innerHTML = '<input type="text" class="des" value="' + description + '">';
                }

                button.style.display = 'none';
                row.querySelector('button.save').style.display = 'inline';
            }

            function deleteVideo(videoId) {
                // Send an AJAX request to delete the video
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "video_delete.php", true); // Corrected the PHP script filename
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            // Handle the response from the server if needed
                            console.log(xhr.responseText);
                            // Remove the row from the table upon successful deletion
                            var row = document.querySelector('tr[data-id="' + videoId + '"]');
                            row.parentNode.removeChild(row);
                        } else {
                            // Handle errors here
                            console.error("Error deleting video: " + xhr.status);
                        }
                    }
                };
                // Prepare the data to be sent in the request
                var data = "videoId=" + videoId;
                // Send the request
                xhr.send(data);
            }


            function saveVideo(button) {
                var row = button.parentElement.parentElement;
                var videoId = row.getAttribute('data-id'); // Retrieve video ID using data-id attribute
                var title = row.querySelector('.title input').value;
                var description = row.querySelector('.description input').value;

                // Send an AJAX request to update the video details
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "update_video.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        // Handle the response from the server if needed
                        console.log(xhr.responseText);
                    }
                };
                // Prepare the data to be sent in the request
                var data = "videoId=" + videoId + "&title=" + encodeURIComponent(title) + "&description=" + encodeURIComponent(description);
                // Send the request
                xhr.send(data);

                // Update the table cell contents with the new values
                row.querySelector('.title').textContent = title;
                row.querySelector('.description').textContent = description;

                // Toggle button visibility
                button.style.display = 'none';
                row.querySelector('button.edit').style.display = 'inline';
            }

            // JavaScript function to delete a video
        </script>
        <script src="script.js"></script>

    </body>

    </html>
<?php
} else {
    // Display a message or take any other appropriate action when no videos are found
    echo "No videos found.";
    exit();
}
?>