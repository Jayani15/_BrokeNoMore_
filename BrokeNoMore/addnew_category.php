<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access: User not logged in.");
}

if (!isset($_GET['id'])) {
    die("No budget ID provided.");
}

$overallBudgetId = $_GET['id'];
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check'])) {

    $sql = "SELECT total_amount 
            FROM overall_budget 
            WHERE overall_budget_id = :bid AND user_id = :uid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':bid' => $overallBudgetId,
        ':uid' => $userId
    ]);
    $totalAmount = $stmt->fetchColumn();

    $sql = "SELECT SUM(allocated_amount) 
            FROM budget_details 
            WHERE overall_budget_id = :bid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':bid' => $overallBudgetId]);
    $allocatedSum = $stmt->fetchColumn();

    echo json_encode([
        'income' => $totalAmount ?? 0,
        'allocated' => $allocatedSum ?? 0
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $categoryName = trim($_POST["category_name"] ?? "");
    $allocatedAmount = floatval($_POST["allocated_amount"] ?? 0);

    if ($categoryName === "" || $allocatedAmount <= 0) {
        die("Invalid category name or allocated amount.");
    }

    $sql = "INSERT INTO category (category_name)
            VALUES (:cname)
            RETURNING category_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cname' => $categoryName]);

    $categoryId = $stmt->fetchColumn();  // UUID of newly created category

    if (!$categoryId) {
        die("Failed to create category.");
    }

    $spentAmount = 0;
    $remainingAmount = $allocatedAmount;

    $sql = "INSERT INTO budget_details 
            (overall_budget_id, category_id, allocated_amount, spent_amount, remaining_amount)
            VALUES (:bid, :cid, :alloc, :spent, :remain)
            RETURNING detail_id";

    $stmt = $pdo->prepare($sql);

    $success = $stmt->execute([
        ':bid'    => $overallBudgetId,
        ':cid'    => $categoryId,
        ':alloc'  => $allocatedAmount,
        ':spent'  => $spentAmount,
        ':remain' => $remainingAmount
    ]);

    if ($success) {
        header("Location: budget_details.php?id=" . $overallBudgetId);
        exit();
    } else {
        die("Error inserting category allocation.");
    }
}

?>
