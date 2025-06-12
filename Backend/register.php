<?php

include("includes/db.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$userName = $_POST['username'];
$name = $_POST['name'];
$email = trim($_POST['email']);
$password = trim($_POST['password']);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT * FROM user_details WHERE userName = ? OR email = ?");
$check->bind_param("ss", $userName, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Username or email already exists.";
} else {
    $stmt = $conn->prepare("INSERT INTO user_details (userName, name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $userName, $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "Registration successful! Redirecting to login...";
        header("Location: /login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}



$check->close();
$conn->close();
?>
