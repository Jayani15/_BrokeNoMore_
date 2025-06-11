<?php
include("includes/db.php");
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'expense_tracker';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("SELECT amount, category FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "error";
        exit;
    }

    $expense = $result->fetch_assoc();
    $amount = $expense['amount'];
    $category = $expense['category'];
    $stmt->close();

    $stmt = $conn->prepare("UPDATE budget_details SET spent_amount = spent_amount - ? WHERE category = ?");
    $stmt->bind_param("ds", $amount, $category);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
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
