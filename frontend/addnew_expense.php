<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login or show error
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"] ?? '';
    $category = trim($_POST["category"] ?? '');
    $amount = floatval($_POST["amount"] ?? 0);
    $description = trim($_POST["description"] ?? '');

    if (!empty($date) && !empty($category) && $amount > 0 && !empty($description)) {
        // Insert the expense
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, date, category, amount, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $userId, $date, $category, $amount, $description);
        if (!$stmt->execute()) {
            // You can customize error handling here
            die("Error adding expense: " . $stmt->error);
        }
        $stmt->close();

        // Update related budgets as you did (optional)
        $stmt = $conn->prepare("SELECT id FROM your_budgets WHERE user_id = ? AND start_date <= ? AND end_date >= ?");
        $stmt->bind_param("iss", $userId, $date, $date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $budgetId = $row['id'];
            $update = $conn->prepare("UPDATE budget_details SET 
                spent_amount = spent_amount + ?, 
                remaining_amount = allocated_amount - (spent_amount + ?) 
                WHERE budget_id = ? AND category = ?");
            $update->bind_param("ddis", $amount, $amount, $budgetId, $category);
            $update->execute();
            $update->close();
        }
        $stmt->close();

        $conn->close();

        // Redirect back to expenses list page after adding
        header("Location: expenses.php");
        exit();
    } else {
        // Handle missing fields
        die("Please fill all fields correctly.");
    }
} else {
    // Not a POST request, redirect to form
    header("Location: addnew_expense.html");
    exit();
}
?>
