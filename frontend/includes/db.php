<?php
$servername = "sqlXXX.epizy.com";  // Replace with your actual host
$username = "epiz_XXXX";          // Your InfinityFree DB username
$password = "your_password";      // Your DB password
$dbname = "epiz_XXXX_expensetracker";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
