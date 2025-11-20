<?php
require_once("includes/db.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($name) || empty($email) || empty($password)) {
    die("All fields are required.");
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {

    /* ---------------------------------------------------------
       CHECK IF EMAIL ALREADY EXISTS
    --------------------------------------------------------- */
    $sql = "SELECT 1 FROM app_user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    if ($stmt->fetch()) {
        die("Email already exists.");
    }

    /* ---------------------------------------------------------
       INSERT NEW USER
    --------------------------------------------------------- */
    $sql = "INSERT INTO app_user (name, email, password)
            VALUES (:name, :email, :password)
            RETURNING user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hashedPassword
    ]);

    $newUserId = $stmt->fetchColumn();

    // Redirect to login
    header("Location: login.php");
    exit();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
