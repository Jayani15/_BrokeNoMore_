<?php
session_start();
require_once("includes/db.php");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Make sure id is provided
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    die("Invalid request.");
}

$savingId = $_GET['id'];

try {
    // Check if this saving goal belongs to the logged-in user
    $check = $pdo->prepare("
        SELECT saving_id 
        FROM savings 
        WHERE saving_id = :sid AND user_id = :uid
    ");

    $check->execute([
        ':sid' => $savingId,
        ':uid' => $userId
    ]);

    if (!$check->fetch()) {
        die("Unauthorized or goal not found.");
    }

    // Delete the saving goal
    $delete = $pdo->prepare("
        DELETE FROM savings 
        WHERE saving_id = :sid AND user_id = :uid
    ");

    $delete->execute([
        ':sid' => $savingId,
        ':uid' => $userId
    ]);

    // Redirect back to savings page
    header("Location: savings.php");
    exit();

} catch (Exception $e) {
    die("Error deleting goal: " . $e->getMessage());
}
?>
