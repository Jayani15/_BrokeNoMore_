<?php
session_start();
require_once("includes/db.php");

// Make PDO throw exceptions
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Path to the template HTML file
$templatePath = __DIR__ . DIRECTORY_SEPARATOR . 'savings.html';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];


// -------------------------------------------------------
// 1. INSERT NEW SAVING GOAL
// -------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $goal   = trim($_POST['goal_name'] ?? '');
    $target = floatval($_POST['target_amount'] ?? 0);
    $start  = trim($_POST['start_date'] ?? '');

    if ($goal === '' || $target <= 0 || $start === '') {
        die("All fields are required.");
    }

    $sql = "INSERT INTO savings (user_id, goal_name, target_amount, saved_amount, start_date)
            VALUES (:uid, :goal, :target, 0, :start)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid'   => $userId,
        ':goal'  => $goal,
        ':target'=> $target,
        ':start' => $start
    ]);

    header("Location: savings.php");
    exit();
}


// -------------------------------------------------------
// 2. FETCH SAVING GOALS
// -------------------------------------------------------
$sql = "SELECT saving_id, goal_name, target_amount, saved_amount, start_date 
        FROM savings
        WHERE user_id = :uid
        ORDER BY start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$savings = $stmt->fetchAll(PDO::FETCH_ASSOC);


// -------------------------------------------------------
// 3. BUILD TABLE ROWS (WITH DELETE BUTTON)
// -------------------------------------------------------
$rows = "";

foreach ($savings as $s) {

    $progress = 0;
    if ($s['target_amount'] > 0) {
        $progress = round(($s['saved_amount'] / $s['target_amount']) * 100, 2);
    }

    $rows .= "
        <tr>
            <td>" . htmlspecialchars($s['goal_name']) . "</td>
            <td>₹" . number_format($s['target_amount'], 2) . "</td>
            <td>₹" . number_format($s['saved_amount'], 2) . "</td>
            <td>{$progress}%</td>
            <td>" . htmlspecialchars($s['start_date']) . "</td>

            <td>
                <button onclick=\"window.location.href='transactions.php?id={$s['saving_id']}'\"class='details-btn'>
                    View
                </button>
            </td>

            <td>
                <button class='delete-btn' style='background:#d9534f;color:white;border:none;padding:5px 10px;cursor:pointer;'
                        onclick=\"if(confirm('Delete this goal?')) window.location.href='delete_saving.php?id={$s['saving_id']}'\">
                    Delete
                </button>
            </td>
        </tr>
    ";
}


// -------------------------------------------------------
// 4. LOAD TEMPLATE AND INJECT ROWS
// -------------------------------------------------------
if (!file_exists($templatePath)) {
    die("Error: savings.html not found at: " . htmlspecialchars($templatePath));
}

$template = file_get_contents($templatePath);

$finalPage = str_replace("<!-- #SAVINGS_ROWS# -->", $rows, $template);

echo $finalPage;
?>
