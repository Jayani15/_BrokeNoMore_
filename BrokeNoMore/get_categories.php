<?php
session_start();
require_once("includes/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}
$userId = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');

/* find budgets covering the date for this user */
$sql = "SELECT overall_budget_id
        FROM overall_budget
        WHERE user_id = :uid
          AND start_date <= :d
          AND end_date >= :d";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId, ':d' => $date]);
$budgetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($budgetIds)) {
    echo json_encode([]);
    exit();
}

/* get distinct categories allocated inside these budgets */
$sql = "SELECT DISTINCT c.category_id, c.category_name
        FROM budget_details bd
        JOIN category c ON bd.category_id = c.category_id
        WHERE bd.overall_budget_id = ANY(:bids::uuid[])";
$stmt = $pdo->prepare($sql);
$stmt->execute([':bids' => '{' . implode(',', $budgetIds) . '}']); // pass as text array
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
