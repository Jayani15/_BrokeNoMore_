<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];
$overallBudgetId = $_GET['id']; // UUID

/* ---------------------------------------------------------
   1) FETCH THE BUDGET (overall_budget)
--------------------------------------------------------- */
$sql = "SELECT budget_name, total_amount, start_date, end_date
        FROM overall_budget
        WHERE overall_budget_id = :bid
        AND user_id = :uid";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':bid' => $overallBudgetId,
    ':uid' => $userId
]);

$budget = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$budget) {
    die("No budget found.");
}

/* ---------------------------------------------------------
   2) FETCH CATEGORY ALLOCATIONS (budget_details + category table)
--------------------------------------------------------- */
$sql = "SELECT 
            bd.detail_id,
            bd.allocated_amount,
            bd.spent_amount,
            c.category_name
        FROM budget_details bd
        JOIN category c ON bd.category_id = c.category_id
        WHERE bd.overall_budget_id = :bid";

$stmt2 = $pdo->prepare($sql);
$stmt2->execute([':bid' => $overallBudgetId]);

$categories = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------------
   3) LOAD TEMPLATE
--------------------------------------------------------- */
$template = file_get_contents("budget_details.html");

/* Replace placeholders with budget info */
$template = str_replace("<!-- #BUDGET_NAME# -->", htmlspecialchars($budget['budget_name']), $template);
$template = str_replace("<!-- #MONTHLY_INCOME# -->", number_format($budget['total_amount'], 2), $template);
$template = str_replace("<!-- #START_DATE# -->", htmlspecialchars($budget['start_date']), $template);
$template = str_replace("<!-- #END_DATE# -->", htmlspecialchars($budget['end_date']), $template);

/* ---------------------------------------------------------
   4) BUILD CATEGORY ROWS
--------------------------------------------------------- */
$rows = "";

foreach ($categories as $cat) {
    $remaining = $cat['allocated_amount'] - $cat['spent_amount'];

    $rows .= "
        <tr id='row-{$cat['detail_id']}'>
            <td data-label='Category'>" . htmlspecialchars($cat['category_name']) . "</td>
            <td data-label='Allocated'>" . number_format($cat['allocated_amount'], 2) . "</td>
            <td data-label='Spent'>" . number_format($cat['spent_amount'], 2) . "</td>
            <td data-label='Remaining'>" . number_format($remaining, 2) . "</td>
            <td data-label='Delete'>
                <button class='delete-btn' onclick='deleteItem(\"{$cat['detail_id']}\")'>Delete</button>
            </td>
        </tr>
    ";
}

$template = str_replace("<!-- #CATEGORY_ROWS# -->", $rows, $template);

/* ---------------------------------------------------------
   5) Add Category Button
--------------------------------------------------------- */
$addBtn = "<button class='add-btn' onclick=\"window.location.href='addnew_category.html?id={$overallBudgetId}'\">Add New Category</button>";
$template = str_replace("<!-- #ADD_CATEGORY_BTN# -->", $addBtn, $template);

/* ---------------------------------------------------------
   OUTPUT PAGE
--------------------------------------------------------- */
echo $template;
?>
