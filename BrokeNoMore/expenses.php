<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];

/* ---------------------------------------------------------
   FETCH ALL EXPENSES FOR THIS USER
--------------------------------------------------------- */

$sql = "SELECT 
            e.expense_id,
            e.date,
            e.amount,
            e.description,
            c.category_name
        FROM expense e
        LEFT JOIN category c ON e.category_id = c.category_id
        WHERE e.user_id = :uid
        ORDER BY e.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------------
   BUILD HTML ROWS
--------------------------------------------------------- */

$expenseRows = "";

foreach ($expenses as $row) {

    $expenseId = htmlspecialchars($row['expense_id']);
    $date = htmlspecialchars($row['date']);
    $categoryName = htmlspecialchars($row['category_name'] ?? 'Unknown');
    $amount = number_format($row['amount'], 2);
    $desc = htmlspecialchars($row['description']);

    $expenseRows .= "
        <tr id='row-$expenseId'>
            <td>$date</td>
            <td>$categoryName</td>
            <td>₹$amount</td>
            <td>$desc</td>
            <td>
                <button class='delete-btn' onclick='deleteItem(\"$expenseId\")'>Delete</button>
            </td>
        </tr>";
}

/* ---------------------------------------------------------
   LOAD TEMPLATE
--------------------------------------------------------- */

$template = file_get_contents("expenses.html");

$finalPage = str_replace("<!-- #EXPENSE_ROWS# -->", $expenseRows, $template);

echo $finalPage;

?>
