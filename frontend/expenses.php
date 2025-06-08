<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];

// DB Connection
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch expenses
$stmt = $conn->prepare("SELECT id, date, category, amount, description FROM expenses WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Build dynamic HTML rows
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

// Load the HTML template
$template = file_get_contents("expenses.html");

// Inject the expense rows
$finalPage = str_replace("<!-- #EXPENSE_ROWS# -->", $expenseRows, $template);

// Output the page
echo $finalPage;

$stmt->close();
$conn->close();
?>
