<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

$userId = $_SESSION['user_id'];

/* ADD NEW METHOD */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $method  = trim($_POST['method_name'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if ($method === '') {
        die("Method name is required.");
    }

    $sql = "INSERT INTO payment_method (method_name, details, user_id)
            VALUES (:m, :d, :u)
            RETURNING payment_method_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':m' => $method,
        ':d' => $details,
        ':u' => $userId
    ]);

    header("Location: payment_methods.php");
    exit();
}

/* FETCH USER-SPECIFIC METHODS */
$sql = "SELECT payment_method_id, method_name, details
        FROM payment_method
        WHERE user_id = :u
        ORDER BY method_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':u' => $userId]);
$methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* BUILD HTML */
$template = file_get_contents('payment_methods.html');

$rows = "";
foreach ($methods as $m) {
    $rows .= "
        <tr>
            <td>".htmlspecialchars($m['method_name'])."</td>
            <td>".htmlspecialchars($m['details'])."</td>
            <td>
                <button class='details-btn'
                    onclick=\"window.location.href='edit_payment_method.php?id={$m['payment_method_id']}'\">
                    Edit
                </button>
            </td>
        </tr>
    ";
}

echo str_replace('{{PAYMENT_ROWS}}', $rows, $template);
?>
