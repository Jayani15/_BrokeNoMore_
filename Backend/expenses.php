<?php
session_start();

include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, date, category, amount, description FROM expenses WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$expenseRows = "";
while ($row = $result->fetch_assoc()) {
    $expenseRows .= "<tr id='row-{$row['id']}'>";
    $expenseRows .= "<td>" . htmlspecialchars($row['date']) . "</td>";
    $expenseRows .= "<td>" . htmlspecialchars($row['category']) . "</td>";
    $expenseRows .= "<td>" . number_format($row['amount'], 2) . "</td>";
    $expenseRows .= "<td>" . htmlspecialchars($row['description']) . "</td>";
    $expenseRows .= "<td><button class='delete-btn' onclick='deleteItem({$row['id']})'>Delete</button></td>";
    $expenseRows .= "</tr>";
}

$template = file_get_contents("expenses.html");

$finalPage = str_replace("<!-- #EXPENSE_ROWS# -->", $expenseRows, $template);

echo $finalPage;

$stmt->close();
$conn->close();
?>
