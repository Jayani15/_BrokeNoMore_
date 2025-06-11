<?php
include("includes/db.php");
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'expense_tracker';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare("DELETE FROM your_budgets WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}

$conn->close();
?>
