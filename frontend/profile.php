<?php
session_start();
include("includes/db.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get session data
$name = $_SESSION['name'] ?? '';
$username = $_SESSION['userName'] ?? '';
$email = $_SESSION['email'] ?? '';

// Load the HTML template
$template = file_get_contents('profile.html');

// Replace placeholders with session data safely
$template = str_replace('<!-- #NAME# -->', htmlspecialchars($name), $template);
$template = str_replace('<!-- #USERNAME# -->', htmlspecialchars($username), $template);
$template = str_replace('<!-- #EMAIL# -->', htmlspecialchars($email), $template);

// Output the final HTML
echo $template;
?>
