<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* ---------------------------------------------------------
   FETCH NOTIFICATIONS
--------------------------------------------------------- */

$sql = "SELECT notification_id, message, date, status
        FROM notification
        WHERE user_id = :uid
        ORDER BY date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);

$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------------------------------------------------
   LOAD TEMPLATE
--------------------------------------------------------- */

$template = file_get_contents("notifications.html");

$rows = "";

foreach ($notes as $n) {

    $statusColor = ($n['status'] === 'unread') ? "red" : "gray";
    $btn = "";

    if ($n['status'] === 'unread') {
        // Button only for unread notifications
        $btn = "<button class='details-btn' onclick=\"markRead('{$n['notification_id']}')\">Mark as read</button>";
    }

    $rows .= "
    <tr>
        <td>" . htmlspecialchars($n['message']) . "</td>
        <td>" . htmlspecialchars($n['date']) . "</td>
        <td style='color:$statusColor; font-weight:bold;'>" . htmlspecialchars($n['status']) . "</td>
        <td>$btn</td>
    </tr>
    ";
}

echo str_replace("<!-- #NOTIFICATION_ROWS# -->", $rows, $template);
?>