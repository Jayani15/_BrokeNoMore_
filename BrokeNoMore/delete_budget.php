<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized request.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $budgetId = $_POST['id'] ?? null;

    if (!$budgetId) {
        echo "error";
        exit();
    }

    try {
        $sql = "DELETE FROM overall_budget 
                WHERE overall_budget_id = :bid 
                AND user_id = :uid";

        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':bid' => $budgetId,
            ':uid' => $_SESSION['user_id']
        ]);

        if ($stmt->rowCount() > 0) {
            // Cascade deletes automatically remove budget_details
            echo "success";
        } else {
            echo "error";  // budget not found or not owned by user
        }

    } catch (Exception $e) {
        echo "error";
    }
}
?>
