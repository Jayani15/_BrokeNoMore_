<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $date = $_POST["date"] ?? '';
    $categoryId = $_POST["category_id"] ?? null;  // MUST be UUID now
    $amount = floatval($_POST["amount"] ?? 0);
    $description = trim($_POST["description"] ?? '');

    if (empty($date) || empty($categoryId) || $amount <= 0 || empty($description)) {
        echo "<script>alert('⚠️ Please fill all fields correctly.'); window.location.href='addnew_expense.html';</script>";
        exit();
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        /* ---------------------------------------------------------
           1) Find which overall budget applies to this date
        --------------------------------------------------------- */
        $sql = "SELECT overall_budget_id 
                FROM overall_budget 
                WHERE user_id = :uid 
                AND start_date <= :d 
                AND end_date >= :d";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':d'   => $date
        ]);

        $budgetIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($budgetIds)) {
            echo "<script>alert('⚠️ No budget exists for the selected date!'); window.location.href='addnew_expense.html';</script>";
            $pdo->rollBack();
            exit();
        }

        /* ---------------------------------------------------------
           2) Check if the selected category_id exists in this budget
        --------------------------------------------------------- */
        $matchingBudget = null;

        foreach ($budgetIds as $bid) {

            $sql = "SELECT detail_id 
                    FROM budget_details 
                    WHERE overall_budget_id = :bid 
                    AND category_id = :cid";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':bid' => $bid,
                ':cid' => $categoryId
            ]);

            $detail = $stmt->fetchColumn();

            if ($detail) {
                $matchingBudget = $bid;
                break;
            }
        }

        if (!$matchingBudget) {
            echo "<script>alert('⚠️ Category not found inside this budget!'); window.location.href='addnew_expense.html';</script>";
            $pdo->rollBack();
            exit();
        }

        /* ---------------------------------------------------------
           3) Insert the new expense
        --------------------------------------------------------- */
        $sql = "INSERT INTO expense 
                (user_id, overall_budget_id, category_id, payment_method_id, amount, description, date)
                VALUES (:uid, :bid, :cid, NULL, :amount, :desc, :d)
                RETURNING expense_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid'    => $userId,
            ':bid'    => $matchingBudget,
            ':cid'    => $categoryId,
            ':amount' => $amount,
            ':desc'   => $description,
            ':d'      => $date
        ]);

        /* ---------------------------------------------------------
           4) Update budget_details (spent & remaining)
        --------------------------------------------------------- */
        $sql = "SELECT allocated_amount, spent_amount 
                FROM budget_details 
                WHERE overall_budget_id = :bid 
                AND category_id = :cid
                FOR UPDATE";   // lock row to prevent race condition

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':bid' => $matchingBudget,
            ':cid' => $categoryId
        ]);

        $detail = $stmt->fetch(PDO::FETCH_ASSOC);

        $allocated = $detail['allocated_amount'];
        $spent = $detail['spent_amount'];

        $newSpent = $spent + $amount;
        $newRemaining = $allocated - $newSpent;

        $sql = "UPDATE budget_details
                SET spent_amount = :spent,
                    remaining_amount = :remain
                WHERE overall_budget_id = :bid
                AND category_id = :cid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':spent'  => $newSpent,
            ':remain' => $newRemaining,
            ':bid'    => $matchingBudget,
            ':cid'    => $categoryId
        ]);

        /* ---------------------------------------------------------
           5) Commit and show warnings if overspent
        --------------------------------------------------------- */
        $pdo->commit();

        if ($new_spent > $allocated_amount) {
            $exceeded_budget = true;
            $exceeded_category = $category;

            require_once("notify.php");
            sendNotification(
                $pdo,
                $userId,
                "⚠️ You exceeded the budget for \"$category\" category."
            );
        }


        header("Location: expenses.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Transaction failed: " . $e->getMessage());
    }

} else {
    header("Location: addnew_expense.html");
    exit();
}

?>
