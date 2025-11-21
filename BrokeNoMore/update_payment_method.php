<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$methodId = $_POST['method_id'] ?? '';
$name = trim($_POST['method_name'] ?? '');
$details = trim($_POST['details'] ?? '');

if ($methodId === '' || $name === '') {
    die("Invalid input.");
}

$sql = "UPDATE payment_method
        SET method_name = :name, details = :details
        WHERE payment_method_id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => $name,
    ':details' => $details,
    ':id' => $methodId
]);

header("Location: payment_methods.php");
exit();
?>
