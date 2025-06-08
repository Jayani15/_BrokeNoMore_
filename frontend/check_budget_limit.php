<?php
session_start();
include("includes/db.php");
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['exceeds' => false]); // fallback
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$date = $data['date'];
$category = $data['category'];
$amount = floatval($data['amount']);
$userId = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    echo json_encode(['exceeds' => false]);
    exit();
}

// Find matching budget
$stmt = $conn->prepare("SELECT id FROM your_budgets WHERE user_id = ? AND start_date <= ? AND end_date >= ?");
$stmt->bind_param("iss", $userId, $date, $date);
$stmt->execute();
$result = $stmt->get_result();

$exceeds = false;

while ($row = $result->fetch_assoc()) {
    $budgetId = $row['id'];
    
    $stmt2 = $conn->prepare("SELECT allocated_amount, spent_amount FROM budget_details WHERE budget_id = ? AND category = ?");
    $stmt2->bind_param("is", $budgetId, $category);
    $stmt2->execute();
    $details = $stmt2->get_result();

    if ($details->num_rows > 0) {
        $data = $details->fetch_assoc();
        $remaining = $data['allocated_amount'] - $data['spent_amount'];
        if ($amount > $remaining) {
            $exceeds = true;
            break;
        }
    }

    $stmt2->close();
}

$stmt->close();
$conn->close();

echo json_encode(['exceeds' => $exceeds]);
?>
