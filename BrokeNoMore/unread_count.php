<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) { echo 0; exit(); }

$userId = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) 
        FROM notification 
        WHERE user_id = :uid AND status = 'unread'";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);

echo $stmt->fetchColumn();
?>