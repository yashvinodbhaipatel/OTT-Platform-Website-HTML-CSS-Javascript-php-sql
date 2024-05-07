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

// Function to fetch users with pagination and search
function fetchUsers($pdo, $offset, $limit, $searchQuery = '')
{
    $searchCondition = '';
    if (!empty($searchQuery)) {
        $searchCondition = " WHERE email LIKE :searchQuery OR mobile_number LIKE :searchQuery";
    }
    $stmt = $pdo->prepare("SELECT user_id, email, mobile_number, account_type FROM user $searchCondition LIMIT :offset, :limit");
    if (!empty($searchQuery)) {
        $stmt->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Pagination parameters
$limit = 15; // Number of users per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search query
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch users from the database
$users = fetchUsers($pdo, $offset, $limit, $searchQuery);

// Handle case where no users are retrieved
if (!$users) {
    // You can display a message or take any other appropriate action
    echo "No users found.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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
            <h1>User Detail</h1>
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
                        <th>User ID<span class="icon-arrow">&UpArrow;</span></th>
                        <th>Email<span class="icon-arrow">&UpArrow;</span></th>
                        <th>Phone Number<span class="icon-arrow">&UpArrow;</span></th>
                        <th>Account Type<span class="icon-arrow">&UpArrow;</span></th>
                        <th>Action<span class="icon-arrow">&UpArrow;</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr data-id="<?php echo $user['user_id']; ?>">
                            <td><?php echo $user['user_id']; ?></td>
                            <td class="email"><?php echo $user['email']; ?></td>
                            <td class="phone"><?php echo $user['mobile_number']; ?></td>
                            <td><?php echo $user['account_type']; ?></td>
                            <td>
                                <button class="edit" onclick="editUser(this)">Edit</button>
                                <button class="save" style="display:none;" onclick="saveUser(this)">Save</button>
                                <button class="delete" onclick="deleteUser(<?php echo $user['user_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!empty($users)) : ?>
                <div class="pagination">
                    <a href="?page=<?php echo $page - 1; ?>" <?php if ($page == 1) echo "style='display:none;'" ?>>Previous</a>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                </div>
            <?php endif; ?>
        </section>
    </main>



    <script>
        // Get a reference to the search input field
        var searchInput = document.getElementById('searchInput');

        // Listen for the 'input' event, which triggers whenever the user types in the search field
        searchInput.addEventListener('input', function() {
            // Get the current search query entered by the user
            var searchQuery = this.value.trim();

            // Perform an AJAX request to fetch users based on the current search query
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_users.php?search=' + encodeURIComponent(searchQuery), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // Update the user list with the results returned from the server
                        var userListContainer = document.querySelector('tbody');
                        userListContainer.innerHTML = xhr.responseText;
                    } else {
                        // Handle any errors
                        console.error('Error fetching users:', xhr.status, xhr.statusText);
                    }
                }
            };
            xhr.send();
        });

        function editUser(button) {
            var row = button.parentElement.parentElement;
            var userId = row.getAttribute('data-id'); // Retrieve user ID using data-id attribute
            var emailCell = row.querySelector('.email');
            var phoneCell = row.querySelector('.phone');

            // Check if input fields already exist, if so, update their values
            var emailInput = emailCell.querySelector('input[type="text"]');
            var phoneInput = phoneCell.querySelector('input[type="text"]');

            if (!emailInput) {
                var email = emailCell.textContent;
                emailCell.innerHTML = '<input type="text" value="' + email + '">';
            }

            if (!phoneInput) {
                var phone = phoneCell.textContent;
                phoneCell.innerHTML = '<input type="text" value="' + phone + '">';
            }

            // Update the userId if it exists
            var userIdInput = row.querySelector('.user_id');
            if (userIdInput) {
                userId = userIdInput.value;
            }

            button.style.display = 'none';
            row.querySelector('button.save').style.display = 'inline';
        }

        function saveUser(button) {
            var row = button.parentElement.parentElement;
            var userId = row.getAttribute('data-id'); // Retrieve user ID using data-id attribute
            var email = row.querySelector('.email input').value;
            var phone = row.querySelector('.phone input').value;

            // Send an AJAX request to update the user details
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response from the server if needed
                    console.log(xhr.responseText);
                }
            };
            // Prepare the data to be sent in the request
            var data = "userId=" + userId + "&email=" + encodeURIComponent(email) + "&phone=" + encodeURIComponent(phone);
            // Send the request
            xhr.send(data);

            // Update the table cell contents with the new values
            row.querySelector('.email').textContent = email;
            row.querySelector('.phone').textContent = phone;

            // Toggle button visibility
            button.style.display = 'none';
            row.querySelector('button.edit').style.display = 'inline';
        }

        function deleteUser(userId) {
            // Send an AJAX request to delete the user
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response from the server if needed
                    console.log(xhr.responseText);
                    // Remove the row from the table upon successful deletion
                    var row = document.querySelector('tr[data-id="' + userId + '"]');
                    row.parentNode.removeChild(row);
                }
            };
            // Prepare the data to be sent in the request
            var data = "userId=" + userId;
            // Send the request
            xhr.send(data);
        }
    </script>
    <script src="script.js"></script>



</body>

</html>