<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$userId = $_SESSION['user_id'];
$savingId = $_GET['id'] ?? '';

if ($savingId === '') { 
    die("Invalid savings ID."); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $amount = floatval($_POST['amount'] ?? 0);
    $type = $_POST['transaction_type'] ?? '';

    if ($amount <= 0 || !in_array($type, ['deposit', 'withdraw'])) {
        die("Invalid transaction.");
    }

    // 1. Insert transaction
    $sql = 'INSERT INTO "transaction" (saving_id, amount, transaction_type)
            VALUES (:sid, :amt, :type)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sid' => $savingId,
        ':amt' => $amount,
        ':type' => $type
    ]);

    // 2. Update total savings
    $updateSql = ($type === 'deposit')
        ? "UPDATE savings SET saved_amount = saved_amount + :amt WHERE saving_id = :sid"
        : "UPDATE savings SET saved_amount = saved_amount - :amt WHERE saving_id = :sid";

    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([':amt' => $amount, ':sid' => $savingId]);

    // 3. Check for goal completion
    $sqlCheck = "SELECT target_amount, saved_amount, goal_name 
                 FROM savings 
                 WHERE saving_id = :sid";
    $stmt = $pdo->prepare($sqlCheck);
    $stmt->execute([':sid' => $savingId]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($s && $s['saved_amount'] >= $s['target_amount']) {
        require_once("notify.php");
        sendNotification(
            $pdo,
            $userId,
            "🎉 Congratulations! You reached your savings goal: {$s['goal_name']}!"
        );
    }

    header("Location: transactions.php?id=$savingId");
    exit();
}


// ---------------- FETCH DATA ----------------

$sql = "SELECT goal_name
        FROM savings WHERE saving_id = :sid";
$stmt = $pdo->prepare($sql);
$stmt->execute([':sid' => $savingId]);
$saving = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = 'SELECT transaction_id, amount, transaction_type, transaction_date
        FROM "transaction"
        WHERE saving_id = :sid
        ORDER BY transaction_date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute([':sid' => $savingId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$template = file_get_contents("transactions.html");


// ---------------- BUILD ROWS ----------------

$rows = "";
foreach ($transactions as $t) {

    $color = $t['transaction_type'] === 'deposit' ? 'green' : 'red';

    $rows .= "
        <tr>
            <td style='color:$color;'>".htmlspecialchars($t['transaction_type'])."</td>
            <td>₹".number_format($t['amount'], 2)."</td>
            <td>".htmlspecialchars($t['transaction_date'])."</td>

            <td>
                <button class='delete-btn' style='background:#d9534f;color:white;border:none;padding:5px 10px;cursor:pointer;'
                        onclick=\"if(confirm('Delete this transaction?')) window.location.href='delete_transaction.php?id={$t['transaction_id']}&sid=$savingId'\">
                    Delete
                </button>
            </td>
        </tr>";
}


// ---------------- RENDER ----------------

$output = str_replace(
    ["<!-- #GOAL# -->", "<!-- #ROWS# -->"],
    [htmlspecialchars($saving['goal_name']), $rows],
    $template
);

echo $output;
?>
