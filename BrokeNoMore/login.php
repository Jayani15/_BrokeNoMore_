<?php
session_start();
require_once("includes/db.php"); // Contains $pdo PDO connection

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        die("❌ Email and password are required.");
    }

    // Fetch user by email
    $sql = "SELECT user_id, name, email, password 
            FROM app_user 
            WHERE email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // Verify hashed password
        if (password_verify($password, $user['password'])) {

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];

            header("Location: dashboard.html");
            exit();

        } else {
            echo "❌ Incorrect password.";
            exit();
        }

    } else {
        echo "❌ No account found with that email.";
        exit();
    }

} else {
    echo "Invalid request method.";
}
?>
