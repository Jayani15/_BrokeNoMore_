<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$budgetName     = $_POST['budget_name'] ?? null;
$totalAmount    = $_POST['total_amount'] ?? null;   // FIXED
$startDate      = $_POST['start_date'] ?? null;
$endDate        = $_POST['end_date'] ?? null;

if (!$budgetName || !$totalAmount || !$startDate || !$endDate) {
    die("All fields are required.");
}

$userId = $_SESSION['user_id'];

$sql = "INSERT INTO overall_budget (user_id, budget_name, total_amount, start_date, end_date)
        VALUES (:user_id, :budget_name, :total_amount, :start_date, :end_date)
        RETURNING overall_budget_id";

$stmt = $pdo->prepare($sql);

$success = $stmt->execute([
    ':user_id'       => $userId,
    ':budget_name'   => $budgetName,
    ':total_amount'  => $totalAmount,
    ':start_date'    => $startDate,
    ':end_date'      => $endDate
]);

if ($success) {
    header("Location: your_budget.php");
    exit();
} else {
    echo "Error inserting budget.";
}
?>
