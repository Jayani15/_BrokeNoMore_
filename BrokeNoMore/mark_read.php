<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) { 
    echo "error"; 
    exit(); 
}

$userId = $_SESSION['user_id'];
$notifId = $_POST['id'] ?? '';

if ($notifId === '') {
    echo "error";
    exit();
}

/* ---------------------------------------------------------
   MARK AS READ
--------------------------------------------------------- */

$sql = "UPDATE notification 
        SET status = 'read' 
        WHERE notification_id = :nid AND user_id = :uid";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nid' => $notifId,
    ':uid' => $userId
]);

echo "ok";
?>