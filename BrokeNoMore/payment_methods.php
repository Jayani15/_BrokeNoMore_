<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $method  = trim($_POST['method_name'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if ($method === '') {
        die("Method name is required.");
    }

    $sql = "INSERT INTO payment_method (method_name, details)
            VALUES (:m, :d)
            RETURNING payment_method_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':m' => $method,
        ':d' => $details
    ]);

    header("Location: payment_methods.php");
    exit();
}

$sql = "SELECT payment_method_id, method_name, details
        FROM payment_method
        ORDER BY method_name ASC";

$stmt = $pdo->query($sql);
$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$template = file_get_contents('payment_methods.html');

$rows = "";
foreach ($methods as $m) {
    $rows .= "<tr>
                <td>" . htmlspecialchars($m['method_name']) . "</td>
                <td>" . htmlspecialchars($m['details']) . "</td>
              </tr>";
}

echo str_replace('<!-- #PM_ROWS# -->', $rows, $template);

?>