<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $dbname = "ott";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->bindParam(':email', $_POST['username']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($_POST['password'], $user['password'])) {
                if ($user['email'] === 'pyash4329@gmail.com') {
                    $_SESSION['user_email'] = $user['email'];
                    header("Location: ../admin/admin_home.php");
                    exit();
                } else {
                    $_SESSION['user_email'] = $user['email'];
                    header("Location: ../ad_page.php");
                    exit();
                }
            } else {
                // Incorrect password - Pass error message to login page
                header("Location: ../login.php?error=Incorrect password. Please try again.");
                exit();
            }
        } else {
            // User not found - Pass error message to login page
            header("Location: ../login.php?error=Incorrect email or user not found. Please check your credentials.");
            exit();
        }
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
