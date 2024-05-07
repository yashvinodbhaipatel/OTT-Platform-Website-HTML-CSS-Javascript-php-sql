<?php
// Start the session
session_start();

$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "ott";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone = $_POST["phone"];

    // Check if any of the form fields is empty
    if (empty($email) || empty($password) || empty($phone)) {
        // Redirect to index.php if any field is empty
        header("Location: ../index.php");
        exit();
    }

    // Validate password criteria
    $uppercase = preg_match('@[A-Z]@', $password);
    $numeric = preg_match('@[0-9]@', $password);
    $symbol = preg_match('@[^A-Za-z0-9]@', $password);

    // Check if password criteria are not met
    if (strlen($password) < 6 || !$uppercase || !$numeric || !$symbol) {
        echo "<script>alert('Password must be at least 6 characters long and contain at least one uppercase letter, one numeric character, and one symbol.');</script>";
        // Redirect to index.php as password criteria are not met
        header("Location: ../index.php");
        exit();
    }

    // Check if the email already exists
    $check_email_sql = "SELECT * FROM User WHERE email = '$email'";
    $result = $conn->query($check_email_sql);

    if ($result->num_rows > 0) {
        // Email already exists, redirect to index.php
        header("Location: ../index.php");
        exit();
    }

    // Hash the password before storing it in the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the User table
    $sql = "INSERT INTO User (email, password, mobile_number, account_type) VALUES ('$email', '$hashed_password', '$phone', 'Basic')";


    if ($conn->query($sql) === TRUE) {
        // Set session variables upon successful registration
        $_SESSION['user_email'] = $email;

        // Redirect to the dashboard or another page after registration
        header("Location: ../home.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
