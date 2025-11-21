<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: addnew_expense.html");
    exit();
}

$date = $_POST["date"] ?? "";
$categoryId = $_POST["category_id"] ?? "";
$paymentMethodId = $_POST["payment_method_id"] ?? "";
$amount = floatval($_POST["amount"] ?? 0);
$desc = trim($_POST["description"] ?? "");

if (!$date || !$categoryId || !$paymentMethodId || $amount <= 0 || !$desc) {
    echo "<script>alert('Fill all fields correctly.'); window.location='addnew_expense.html';</script>";
    exit();
}

try {
    $pdo->beginTransaction();

    // Find budget
    $stmt = $pdo->prepare("
        SELECT overall_budget_id 
        FROM overall_budget
        WHERE user_id = :uid AND start_date <= :d AND end_date >= :d
    ");
    $stmt->execute([':uid' => $userId, ':d' => $date]);
    $budgets = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$budgets) {
        echo "<script>alert('No active budget for this date.'); window.location='addnew_expense.html';</script>";
        exit();
    }

    // Match category
    $matching = null;
    foreach ($budgets as $bid) {
        $c = $pdo->prepare("
            SELECT detail_id FROM budget_details
            WHERE overall_budget_id = :bid AND category_id = :cid
        ");
        $c->execute([':bid' => $bid, ':cid' => $categoryId]);
        if ($c->fetch()) {
            $matching = $bid;
            break;
        }
    }

    if (!$matching) {
        echo "<script>alert('Category not found inside budget.'); window.location='addnew_expense.html';</script>";
        exit();
    }

    // Insert expense
    $insert = $pdo->prepare("
        INSERT INTO expense (user_id, overall_budget_id, category_id, payment_method_id, amount, description, date)
        VALUES (:u, :b, :c, :p, :a, :dsc, :dt)
    ");
    $insert->execute([
        ':u' => $userId,
        ':b' => $matching,
        ':c' => $categoryId,
        ':p' => $paymentMethodId,
        ':a' => $amount,
        ':dsc' => $desc,
        ':dt' => $date
    ]);

    // Update budget
    $bd = $pdo->prepare("
        SELECT allocated_amount, spent_amount 
        FROM budget_details 
        WHERE overall_budget_id = :b AND category_id = :c
        FOR UPDATE
    ");
    $bd->execute([':b' => $matching, ':c' => $categoryId]);
    $row = $bd->fetch(PDO::FETCH_ASSOC);

    $newSpent = $row['spent_amount'] + $amount;
    $newRemaining = $row['allocated_amount'] - $newSpent;

    $upd = $pdo->prepare("
        UPDATE budget_details 
        SET spent_amount = :s, remaining_amount = :r
        WHERE overall_budget_id = :b AND category_id = :c
    ");
    $upd->execute([
        ':s' => $newSpent,
        ':r' => $newRemaining,
        ':b' => $matching,
        ':c' => $categoryId
    ]);

    $pdo->commit();

    header("Location: expenses.php");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>
