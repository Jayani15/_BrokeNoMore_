<?php
// get_categories.php
session_start();
require_once("includes/db.php");
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];
// Accept ?date=YYYY-MM-DD, otherwise today
$date = $_GET['date'] ?? date('Y-m-d');

// 1) Find budgets covering the date for this user
$sql = "SELECT overall_budget_id
        FROM overall_budget
        WHERE user_id = :uid
          AND start_date <= :d
          AND end_date >= :d";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId, ':d' => $date]);
$budgetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// If no budgets -> empty result
if (empty($budgetIds)) {
    echo json_encode([]);
    exit();
}

// 2) Build safe IN (...) clause with positional placeholders
$placeholders = implode(',', array_fill(0, count($budgetIds), '?'));

// Query distinct categories allocated inside these budgets
$sql = "
    SELECT DISTINCT c.category_id, c.category_name
    FROM budget_details bd
    JOIN category c ON bd.category_id = c.category_id
    WHERE bd.overall_budget_id IN ($placeholders)
    ORDER BY c.category_name ASC
";

$stmt = $pdo->prepare($sql);
// Execute with the positional array of budget IDs
$stmt->execute($budgetIds);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON (empty array if none)
echo json_encode($rows);
exit();
