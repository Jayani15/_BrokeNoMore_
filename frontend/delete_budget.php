<?php
include("includes/db.php");
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'expense_tracker';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $user_id = intval($_POST['user_id']);
    
    $stmt = $conn->prepare("DELETE FROM budget_details WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id,$user_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}

$conn->close();
?>
