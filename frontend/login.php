<?php
session_start();
include("includes/db.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get form inputs
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Connect to database
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute the query to get user data
$stmt = $conn->prepare("SELECT id, userName, name, email, password FROM user_details WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['userName'] = $user['userName'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        header("refresh:2; url=dashboard.html"); // Update to your actual dashboard page
        exit();
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "No account found with that email.";
}

$stmt->close();
$conn->close();
?>