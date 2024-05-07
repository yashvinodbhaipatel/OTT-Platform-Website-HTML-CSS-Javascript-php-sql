<?php
// Check if the necessary parameters are set
if (isset($_POST['userId'], $_POST['email'], $_POST['phone'])) {
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
        echo "Database connection failed: " . $e->getMessage();
        exit(); // Terminate the script if connection fails
    }

    // Prepare and execute the SQL UPDATE statement
    try {
        $stmt = $pdo->prepare("UPDATE user SET email = :email, mobile_number = :phone WHERE user_id = :userId");
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->bindParam(':userId', $_POST['userId']);
        $stmt->execute();
        echo "User details updated successfully";
    } catch (PDOException $e) {
        echo "Error updating user details: " . $e->getMessage();
    }
} else {
    echo "Missing parameters"; // Handle missing parameters
}
?>
