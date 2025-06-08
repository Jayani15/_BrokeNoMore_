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

// Connect to DB
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process POST form submission
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
        // Redirect back to budget details or any page you want after success
        header("Location: budget_details.php?id=" . $budgetId);
        exit();
    } else {
        echo "Error inserting category: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
