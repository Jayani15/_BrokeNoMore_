<?php
session_start();
include("includes/db.php");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $conn->prepare("SELECT SUM(amount) as total_expenses FROM expenses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$expense_result = $stmt->get_result()->fetch_assoc();
$total_expenses = $expense_result['total_expenses'] ?? 0;

$stmt = $conn->prepare("SELECT SUM(allocated_amount) as total_allocated FROM budget_details WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$budget_result = $stmt->get_result()->fetch_assoc();
$total_allocated = $budget_result['total_allocated'] ?? 0;

$stmt = $conn->prepare("
    SELECT SUM(spent_amount - allocated_amount) AS total_overspent 
    FROM budget_details 
    WHERE user_id = ? AND spent_amount > allocated_amount
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$overspent_result = $stmt->get_result()->fetch_assoc();
$total_overspent = $overspent_result['total_overspent'] ?? 0;

$savings = $total_allocated - $total_expenses;

$template = file_get_contents("budget_summary.html");

$summaryHTML = "
    <h3>Total Expenses: ₹{$total_expenses}</h3>
    <h3>Total Allocated Amount: ₹{$total_allocated}</h3>
    <h3>Savings: ₹" . max($savings, 0) . "</h3>
    <h3>Over spending: ₹" . number_format($total_overspent, 2) . "</h3>
";
$template = str_replace("<!-- #SUMMARY_VALUES# -->", $summaryHTML, $template);

$chartScript = "
<script>
    const ctx = document.getElementById('budgetChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Allocated Amount', 'Spent Amount'],
            datasets: [{
                label: 'Budget Comparison (₹)',
                data: [$total_allocated, $total_expenses],
                backgroundColor: ['#4CAF50', '#FF6384'],
                borderColor: ['#388E3C', '#D32F2F'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (₹)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Overall Budget vs Expenses'
                }
            }
        }
    });
</script>
";
$template = str_replace("<!-- #SUMMARY_CHART# -->", $chartScript, $template);

echo $template;

$conn->close();
?>
