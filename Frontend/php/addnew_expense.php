<?php
session_start();

include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"] ?? '';
    $category = trim($_POST["category"] ?? '');
    $amount = floatval($_POST["amount"] ?? 0);
    $description = trim($_POST["description"] ?? '');

    if (!empty($date) && !empty($category) && $amount > 0 && !empty($description)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO expenses (user_id, date, category, amount, description) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed for expense insertion: " . $conn->error);
            }
            $stmt->bind_param("issds", $userId, $date, $category, $amount, $description);
            if (!$stmt->execute()) {
                throw new Exception("Error adding expense: " . $stmt->error);
            }
            $stmt->close();

            $stmt = $conn->prepare("SELECT id FROM your_budgets WHERE user_id = ? AND start_date <= ? AND end_date >= ?");
            if (!$stmt) {
                throw new Exception("Prepare failed for budget selection: " . $conn->error);
            }
            $stmt->bind_param("iss", $userId, $date, $date);
            $stmt->execute();
            $result = $stmt->get_result();

            $exceeded_budget = false;
            $exceeded_category = '';

            while ($row = $result->fetch_assoc()) {
                $budgetId = $row['id'];

                $current_amounts_stmt = $conn->prepare("SELECT allocated_amount, spent_amount FROM budget_details WHERE budget_id = ? AND category = ? AND user_id = ?");
                if (!$current_amounts_stmt) {
                    throw new Exception("Prepare failed for current amounts: " . $conn->error);
                }
                $current_amounts_stmt->bind_param("isi", $budgetId, $category, $userId);
                $current_amounts_stmt->execute();
                $current_amounts_stmt->bind_result($allocated_amount, $current_spent_amount);
                $current_amounts_stmt->fetch();
                $current_amounts_stmt->close();

                $new_spent_amount = $current_spent_amount + $amount;
                $new_remaining_amount = $allocated_amount - $new_spent_amount;

                $update = $conn->prepare("UPDATE budget_details SET
                    spent_amount = ?,
                    remaining_amount = ?
                    WHERE budget_id = ? AND category = ? AND user_id = ?");
                if (!$update) {
                    throw new Exception("Prepare failed for budget details update: " . $conn->error);
                }
                $update->bind_param("ddisi", $new_spent_amount, $new_remaining_amount, $budgetId, $category, $userId);
                if (!$update->execute()) {
                    throw new Exception("Error updating budget details: " . $update->error);
                }
                $update->close();

                if ($new_spent_amount > $allocated_amount) {
                    $exceeded_budget = true;
                    $exceeded_category = $category;
                }
            }
            $stmt->close();

            $conn->commit();

            if ($exceeded_budget) {
                echo "<script>alert('⚠️ You have exceeded the allocated amount for the \"" . addslashes($exceeded_category) . "\" category!'); window.location.href = 'expenses.php';</script>";
            } else {
                header("Location: expenses.php");
                exit();
            }

        } catch (Exception $e) {
            $conn->rollback();
            die("Transaction failed: " . $e->getMessage());
        }

    } else {
        die("Please fill all fields correctly.");
    }
} else {
    header("Location: addnew_expense.html");
    exit();
}

$conn->close();
?>
