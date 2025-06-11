<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$budgetName = $_POST['budget_name'] ?? null;
$monthlyIncome = $_POST['monthly_income'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;

if (!$budgetName || !$monthlyIncome || !$startDate || !$endDate) {
    die("All fields are required.");
}

$userId = $_SESSION['user_id'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO your_budgets (user_id, budget_name, monthly_income, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isdss", $userId, $budgetName, $monthlyIncome, $startDate, $endDate);

if ($stmt->execute()) {
    header("Location: your_budget.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
