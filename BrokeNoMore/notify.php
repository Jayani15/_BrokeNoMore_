<?php
function sendNotification($pdo, $userId, $message) {
    $sql = "INSERT INTO notification (user_id, message, status)
            VALUES (:uid, :msg, 'unread')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid' => $userId,
        ':msg' => $message
    ]);
}
?>
