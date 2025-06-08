<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$userName = $_POST['username'];
$name = $_POST['name'];
$email = trim($_POST['email']);
$password = trim($_POST['password']);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for existing user/email
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
        header("refresh:2; url= login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

echo "Password before hashing: " . $password . "<br>";
echo "Hashed password: " . $hashedPassword . "<br>";


$check->close();
$conn->close();
?>
