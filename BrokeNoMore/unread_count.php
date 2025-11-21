<?php
// unread_count.php
session_start();
require_once("includes/db.php");

header('Content-Type: application/json');

// If user not logged in, gracefully return 0
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $sql = "SELECT COUNT(*) FROM notification WHERE user_id = :uid AND status = 'unread'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $userId]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['count' => $count]);
} catch (Exception $e) {
    // Do not leak errors to client in production, but return 0 so UI is safe
    echo json_encode(['count' => 0]);
}
