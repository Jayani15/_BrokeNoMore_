<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, budget_name, monthly_income, start_date, end_date FROM your_budgets WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$rows = '';
while ($row = $result->fetch_assoc()) {
    $id = htmlspecialchars($row['id']);
    $name = htmlspecialchars($row['budget_name']);
    $income = htmlspecialchars($row['monthly_income']);
    $start = htmlspecialchars($row['start_date']);
    $end = htmlspecialchars($row['end_date']);

    $rows .= "<tr id='row-$id'>
                <td>$name</td>
                <td>$income</td>
                <td>$start</td>
                <td>$end</td>
                <td><button onclick=\"window.location.href='budget_details.php?id=$id'\" class='details-btn'>View</button></td>
                <td><button class='delete-btn' onclick='deleteItem($id)'>Delete</button></td>
              </tr>";
}

$template = file_get_contents('your_budget.html');
$output = str_replace('<!-- #BUDGET_ROWS# -->', $rows, $template);

echo $output;

$stmt->close();
$conn->close();
?>
