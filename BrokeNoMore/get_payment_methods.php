<?php
session_start();
require_once("includes/db.php");

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); 
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT payment_method_id, method_name 
        FROM payment_method
        WHERE user_id = :uid
        ORDER BY method_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
