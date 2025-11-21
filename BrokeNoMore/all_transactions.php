<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* ---------------------------------------
   HANDLE NEW TRANSACTION INSERT
---------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $savingId = $_POST['saving_id'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $type = $_POST['transaction_type'] ?? '';

    if ($savingId == '' || $amount <= 0 || !in_array($type, ['deposit', 'withdraw'])) {
        die("Invalid input.");
    }

    // Insert transaction
    $sql = "INSERT INTO transaction (saving_id, amount, transaction_type)
            VALUES (:sid, :amt, :type)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sid' => $savingId,
        ':amt' => $amount,
        ':type' => $type
    ]);

    // Update total saved amount
    if ($type === 'deposit') {
        $sql = "UPDATE savings SET saved_amount = saved_amount + :amt WHERE saving_id = :sid";
    } else {
        $sql = "UPDATE savings SET saved_amount = saved_amount - :amt WHERE saving_id = :sid";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':amt' => $amount, ':sid' => $savingId]);

    header("Location: all_transactions.php");
    exit();
}

/* ---------------------------------------
   GET ALL TRANSACTIONS (JOIN WITH GOALS)
---------------------------------------- */
$sql = "
    SELECT 
        t.transaction_id,
        t.amount, 
        t.transaction_type, 
        t.transaction_date,
        s.goal_name,
        s.saving_id
    FROM transaction t
    JOIN savings s ON t.saving_id = s.saving_id
    WHERE s.user_id = :uid
    ORDER BY t.transaction_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------
   GET ALL SAVINGS GOALS (for dropdown)
---------------------------------------- */
$sql = "SELECT saving_id, goal_name FROM savings WHERE user_id = :uid ORDER BY goal_name";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* LOAD TEMPLATE */
$template = file_get_contents("all_transactions.html");

$rows = "";
foreach ($transactions as $t) {
    $color = ($t['transaction_type'] === 'deposit') ? 'green' : 'red';
    $rows .= "
        <tr>
            <td>".htmlspecialchars($t['goal_name'])."</td>
            <td style='color:$color;'>".htmlspecialchars($t['transaction_type'])."</td>
            <td>₹".number_format($t['amount'], 2)."</td>
            <td>".htmlspecialchars($t['transaction_date'])."</td>

            <td>
                <button class='delete-btn' style='background:#d9534f;color:white;border:none;padding:5px 10px;cursor:pointer;'
                        onclick=\"if(confirm('Delete this transaction?')) 
                        window.location.href='delete_all_transaction.php?id={$t['transaction_id']}&sid={$t['saving_id']}'\">
                    Delete
                </button>
            </td>
        </tr>
    ";
}

$dropdown = "";
foreach ($goals as $g) {
    $dropdown .= "<option value='{$g['saving_id']}'>".htmlspecialchars($g['goal_name'])."</option>";
}

$output = str_replace(
    ["<!-- #ROWS# -->", "<!-- #GOAL_OPTIONS# -->"],
    [$rows, $dropdown],
    $template
);

echo $output;
?>
