<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access: User not logged in.");
}

if (!isset($_GET['id'])) {
    die("No budget ID provided.");
}

$budgetId = intval($_GET['id']);
$userId = intval($_SESSION['user_id']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check'])) {
    $stmt = $conn->prepare("SELECT monthly_income FROM your_budgets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $budgetId, $userId);
    $stmt->execute();
    $stmt->bind_result($monthly_income);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT SUM(allocated_amount) FROM budget_details WHERE budget_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $budgetId, $userId);
    $stmt->execute();
    $stmt->bind_result($allocated_sum);
    $stmt->fetch();
    $stmt->close();

    echo json_encode([
        'income' => $monthly_income,
        'allocated' => $allocated_sum ?? 0
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = trim($_POST["category"] ?? '');
    $allocatedAmount = floatval($_POST["allocated_amount"] ?? 0);

    if ($category === '' || $allocatedAmount <= 0) {
        echo "Invalid category name or allocated amount.";
        exit;
    }

    $spentAmount = 0.00;
    $remainingAmount = $allocatedAmount;

    $stmt = $conn->prepare("INSERT INTO budget_details (user_id, budget_id, category, allocated_amount, spent_amount, remaining_amount) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iisddd", $userId, $budgetId, $category, $allocatedAmount, $spentAmount, $remainingAmount);

    if ($stmt->execute()) {
        header("Location: budget_details.php?id=" . $budgetId);
        exit();
    } else {
        echo "Error inserting category: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
