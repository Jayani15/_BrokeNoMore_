<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$transactionId = $_GET['id'] ?? '';
$savingId = $_GET['sid'] ?? '';

if ($transactionId === '' || $savingId === '') {
    die("Invalid transaction.");
}

// 1. Fetch transaction details
$sql = 'SELECT amount, transaction_type FROM transaction WHERE transaction_id = :tid';
$stmt = $pdo->prepare($sql);
$stmt->execute([':tid' => $transactionId]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trx) {
    die("Transaction not found.");
}

$amount = $trx['amount'];
$type   = $trx['transaction_type'];

try {
    // 2. Reverse the savings amount
    if ($type === "deposit") {
        $update = "UPDATE savings SET saved_amount = saved_amount - :amt WHERE saving_id = :sid";
    } else {
        $update = "UPDATE savings SET saved_amount = saved_amount + :amt WHERE saving_id = :sid";
    }

    $stmt = $pdo->prepare($update);
    $stmt->execute([':amt' => $amount, ':sid' => $savingId]);

    // 3. Delete the transaction
    $del = $pdo->prepare("DELETE FROM transaction WHERE transaction_id = :tid");
    $del->execute([':tid' => $transactionId]);

    header("Location: all_transactions.php");
    exit();

} catch (Exception $e) {
    die("Error deleting transaction: " . $e->getMessage());
}
?>
