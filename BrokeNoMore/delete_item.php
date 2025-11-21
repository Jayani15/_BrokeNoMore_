<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized request.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $expenseId = $_POST['id'] ?? null;

    if (!$expenseId) {
        echo "error";
        exit();
    }

    try {
        $pdo->beginTransaction();

        /* ---------------------------------------------------------
           1) Fetch the expense (only if it belongs to the user)
        --------------------------------------------------------- */
        $sql = "SELECT amount, category_id, overall_budget_id
                FROM expense
                WHERE expense_id = :eid AND user_id = :uid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':eid' => $expenseId,
            ':uid' => $_SESSION['user_id']
        ]);

        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense) {
            echo "error";
            $pdo->rollBack();
            exit();
        }

        $amount       = $expense['amount'];
        $categoryId   = $expense['category_id'];
        $budgetId     = $expense['overall_budget_id'];

        /* ---------------------------------------------------------
           2) Update budget_details (subtract spent)
        --------------------------------------------------------- */
        $sql = "UPDATE budget_details
                SET spent_amount = spent_amount - :amt,
                    remaining_amount = remaining_amount + :amt
                WHERE category_id = :cid
                AND overall_budget_id = :bid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':amt' => $amount,
            ':cid' => $categoryId,
            ':bid' => $budgetId
        ]);

        /* ---------------------------------------------------------
           3) Delete the expense itself
        --------------------------------------------------------- */
        $sql = "DELETE FROM expense
                WHERE expense_id = :eid AND user_id = :uid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':eid' => $expenseId,
            ':uid' => $_SESSION['user_id']
        ]);

        $pdo->commit();
        echo "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "error";
    }
}
?>
