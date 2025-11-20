<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $goal = trim($_POST['goal_name'] ?? '');
    $target = floatval($_POST['target_amount'] ?? 0);
    $start = trim($_POST['start_date'] ?? '');

    if ($goal === '' || $target <= 0 || $start === '') {
        die("All fields are required.");
    }

    $sql = "INSERT INTO savings (user_id, goal_name, target_amount, saved_amount, start_date)
            VALUES (:uid, :goal, :target, 0, :start)
            RETURNING saving_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid' => $userId,
        ':goal' => $goal,
        ':target' => $target,
        ':start' => $start
    ]);

    header("Location: savings.php");
    exit();
}

$sql = "SELECT saving_id, goal_name, target_amount, saved_amount, start_date 
        FROM savings
        WHERE user_id = :uid
        ORDER BY start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$savings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$template = file_get_contents("savings.html");
$rows = "";

foreach ($savings as $s) {

    $progress = ($s['saved_amount'] / $s['target_amount']) * 100;
    $progress = round($progress, 2);

    $rows .= "
        <tr>
            <td>".htmlspecialchars($s['goal_name'])."</td>
            <td>₹".number_format($s['target_amount'], 2)."</td>
            <td>₹".number_format($s['saved_amount'], 2)."</td>
            <td>$progress%</td>
            <td>".htmlspecialchars($s['start_date'])."</td>
            <td>
                <button onclick=\"window.location.href='transactions.php?id={$s['saving_id']}'\">View</button>
            </td>
        </tr>
    ";
}

echo str_replace("<!-- #SAVINGS_ROWS# -->", $rows, $template);

?>
