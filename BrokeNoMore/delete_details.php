<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized request.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $detailId = $_POST['id'] ?? null;

    if (!$detailId) {
        echo "error";
        exit();
    }

    try {
        // Delete only if this detail belongs to a budget owned by the user
        // (prevents other users from deleting your categories)
        $sql = "DELETE FROM budget_details 
                USING overall_budget 
                WHERE budget_details.detail_id = :detail_id
                AND budget_details.overall_budget_id = overall_budget.overall_budget_id
                AND overall_budget.user_id = :uid";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':detail_id' => $detailId,
            ':uid'       => $_SESSION['user_id']
        ]);

        if ($stmt->rowCount() > 0) {
            echo "success";
        } else {
            echo "error";
        }

    } catch (Exception $e) {
        echo "error";
    }
}
?>
