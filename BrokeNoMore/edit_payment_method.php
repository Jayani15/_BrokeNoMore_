<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$methodId = $_GET['id'] ?? '';

if ($methodId === '') {
    die("Invalid payment method ID.");
}

$sql = "SELECT method_name, details 
        FROM payment_method 
        WHERE payment_method_id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $methodId]);
$method = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$method) {
    die("Payment method not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment Method</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/your_budget.css">
</head>

<body>

<nav>
    <h1><i class="fa-solid fa-paw"></i> BrokeNoMore</h1>
</nav>

<div class="container">
    <h2>Edit Payment Method</h2>

    <form action="update_payment_method.php" method="POST">
        <input type="hidden" name="method_id" value="<?php echo $methodId; ?>">

        <label>Method Name</label>
        <input type="text" name="method_name"
               value="<?php echo htmlspecialchars($method['method_name']); ?>" required>

        <label>Details (Optional)</label>
        <input type="text" name="details"
               value="<?php echo htmlspecialchars($method['details']); ?>">

        <button type="submit" class="add-btn">Update</button>

        <button type="button" 
                class="add-btn" 
                style="background:#6c757d"
                onclick="window.location.href='payment_methods.php'">
            Cancel
        </button>
    </form>
</div>

</body>
</html>
