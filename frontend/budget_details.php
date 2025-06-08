<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];
$budgetId = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch budget info
$stmt = $conn->prepare("SELECT budget_name, monthly_income, start_date, end_date FROM your_budgets WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $budgetId, $userId);
$stmt->execute();
$budgetResult = $stmt->get_result();

if ($budgetResult->num_rows === 0) {
    die("No budget found.");
}
$budget = $budgetResult->fetch_assoc();
$stmt->close();

// Fetch category data
$stmt2 = $conn->prepare("SELECT id, category, allocated_amount, spent_amount FROM budget_details WHERE budget_id = ? AND user_id = ?");
$stmt2->bind_param("ii", $budgetId, $userId);
$stmt2->execute();
$categories = $stmt2->get_result();

// Read template
$template = file_get_contents("budget_details.html");

// Replace budget details
$template = str_replace("<!-- #BUDGET_NAME# -->", htmlspecialchars($budget['budget_name']), $template);
$template = str_replace("<!-- #MONTHLY_INCOME# -->", number_format($budget['monthly_income'], 2), $template);
$template = str_replace("<!-- #START_DATE# -->", htmlspecialchars($budget['start_date']), $template);
$template = str_replace("<!-- #END_DATE# -->", htmlspecialchars($budget['end_date']), $template);

// Build category rows
$rows = "";
while ($cat = $categories->fetch_assoc()) {
    $remaining = $cat['allocated_amount'] - $cat['spent_amount'];

    if ($remaining < 0) {
        echo "<script>alert('Warning: The category " . htmlspecialchars($cat['category']) . " has exceeded its allocated amount!');</script>";
    }

    $rows .= "<tr id='row-{$cat['id']}'>
        <td>" . htmlspecialchars($cat['category']) . "</td>
        <td>" . number_format($cat['allocated_amount'], 2) . "</td>
        <td>" . number_format($cat['spent_amount'], 2) . "</td>
        <td>" . number_format($remaining, 2) . "</td>
        <td><button class='delete-btn' onclick='deleteItem({$cat['id']})'>Delete</button></td>
    </tr>";
}
$template = str_replace("<!-- #CATEGORY_ROWS# -->", $rows, $template);

// Add category button
$addBtn = "<button class='add-btn' onclick=\"window.location.href='addnew_category.html?id={$budgetId}'\">Add New Category</button>";
$template = str_replace("<!-- #ADD_CATEGORY_BTN# -->", $addBtn, $template);

echo $template;

$stmt2->close();
$conn->close();
?>
