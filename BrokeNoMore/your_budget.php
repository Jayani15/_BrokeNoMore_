<?php
session_start();
require_once("includes/db.php");  // PDO connection

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$userId = $_SESSION['user_id'];

$sql = "SELECT overall_budget_id, budget_name, total_amount, start_date, end_date
        FROM overall_budget
        WHERE user_id = :uid
        ORDER BY start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);

$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = "";

foreach ($budgets as $row) {
    $id     = htmlspecialchars($row['overall_budget_id']);
    $name   = htmlspecialchars($row['budget_name']);
    $amount = number_format($row['total_amount'], 2);
    $start  = htmlspecialchars($row['start_date']);
    $end    = htmlspecialchars($row['end_date']);

    $rows .= "
        <tr id='row-$id'>
            <td data-label='Budget Name'>$name</td>
            <td data-label='Total Amount'>₹$amount</td>
            <td data-label='Start Date'>$start</td>
            <td data-label='End Date'>$end</td>
            <td data-label='Details'>
                <button onclick=\"window.location.href='budget_details.php?id=$id'\" 
                        class='details-btn'>View</button>
            </td>
            <td data-label='Delete'>
                <button class='delete-btn' onclick='deleteItem(\"$id\")'>Delete</button>
            </td>
        </tr>
    ";
}

$template = file_get_contents('your_budget.html');
$output = str_replace('<!-- #BUDGET_ROWS# -->', $rows, $template);

$sqlAlloc = "
    SELECT 
        bd.overall_budget_id,
        SUM(bd.allocated_amount) AS allocated
    FROM budget_details bd
    JOIN overall_budget ob ON bd.overall_budget_id = ob.overall_budget_id
    WHERE ob.user_id = :uid
    GROUP BY bd.overall_budget_id
";
$stmtAlloc = $pdo->prepare($sqlAlloc);
$stmtAlloc->execute([':uid' => $userId]);
$allocations = $stmtAlloc->fetchAll(PDO::FETCH_KEY_PAIR);

$budgetCheckData = [];

foreach ($budgets as $row) {
    $bid = $row['overall_budget_id'];
    $budgetCheckData[] = [
        'budget_name' => $row['budget_name'],
        'amount' => floatval($row['total_amount']),
        'allocated' => floatval($allocations[$bid] ?? 0)
    ];
}

$script = "<script>
            const budgetCheckData = " . json_encode($budgetCheckData) . ";
          </script>";

$output = str_replace("</body>", $script . "\n</body>", $output);


echo $output;

?>
