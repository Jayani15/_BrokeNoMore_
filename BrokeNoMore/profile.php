<?php
session_start();
require_once("includes/db.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Get session data
$name  = $_SESSION['name']  ?? '';
$email = $_SESSION['email'] ?? '';

// Load the HTML template
$template = file_get_contents('profile.html');

// Replace placeholders
$template = str_replace('<!-- #NAME# -->', htmlspecialchars($name), $template);
$template = str_replace('<!-- #EMAIL# -->', htmlspecialchars($email), $template);

// No USERNAME anymore in your schema — remove placeholder


// Output final HTML
echo $template;
?>
