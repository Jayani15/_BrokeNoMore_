<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$userId = $_SESSION['user_id'];

$sql = "
    SELECT e.expense_id, e.date, e.amount, e.description,
           c.category_name,
           pm.method_name AS payment_method_name
    FROM expense e
    LEFT JOIN category c ON e.category_id = c.category_id
    LEFT JOIN payment_method pm ON e.payment_method_id = pm.payment_method_id
    WHERE e.user_id = :u
    ORDER BY e.date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':u' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$table = "";
foreach ($rows as $r) {
    $table .= "
        <tr id='row-{$r['expense_id']}'>
            <td>{$r['date']}</td>
            <td>{$r['category_name']}</td>
            <td>₹" . number_format($r['amount'], 2) . "</td>
            <td>{$r['description']}</td>
            <td>{$r['payment_method_name']}</td>
            <td><button class='delete-btn' onclick='deleteItem(\"{$r['expense_id']}\")'>Delete</button></td>
        </tr>
    ";
}

$template = file_get_contents("expenses.html");
echo str_replace("{{EXPENSE_ROWS}}", $table, $template);
?>
