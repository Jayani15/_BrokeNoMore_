<?php
session_start();
require_once("includes/db.php"); // PDO connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* ---------------------------------------------------------
   1) TOTAL EXPENSES for this user
--------------------------------------------------------- */
$sql = "SELECT COALESCE(SUM(amount), 0) 
        FROM expense 
        WHERE user_id = :uid";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$total_expenses = $stmt->fetchColumn();

/* ---------------------------------------------------------
   2) TOTAL ALLOCATED AMOUNT (join budget_details + overall_budget)
--------------------------------------------------------- */
$sql = "SELECT COALESCE(SUM(bd.allocated_amount), 0)
        FROM budget_details bd
        JOIN overall_budget ob ON bd.overall_budget_id = ob.overall_budget_id
        WHERE ob.user_id = :uid";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$total_allocated = $stmt->fetchColumn();

/* ---------------------------------------------------------
   3) TOTAL OVERSPENT = SUM(spent - allocated) for categories user owns
--------------------------------------------------------- */
$sql = "SELECT COALESCE(SUM(bd.spent_amount - bd.allocated_amount), 0)
        FROM budget_details bd
        JOIN overall_budget ob ON bd.overall_budget_id = ob.overall_budget_id
        WHERE ob.user_id = :uid
        AND bd.spent_amount > bd.allocated_amount";

$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $userId]);
$total_overspent = $stmt->fetchColumn();

/* ---------------------------------------------------------
   4) Savings = allocated - spent (but never negative)
--------------------------------------------------------- */
$savings = max($total_allocated - $total_expenses, 0);

/* ---------------------------------------------------------
   LOAD TEMPLATE
--------------------------------------------------------- */
$template = file_get_contents("budget_summary.html");

/* ---------------------------------------------------------
   SUMMARY VALUES HTML
--------------------------------------------------------- */
$summaryHTML = "
    <h3>Total Expenses: ₹" . number_format($total_expenses, 2) . "</h3>
    <h3>Total Allocated Amount: ₹" . number_format($total_allocated, 2) . "</h3>
    <h3>Savings: ₹" . number_format($savings, 2) . "</h3>
    <h3>Over spending: ₹" . number_format($total_overspent, 2) . "</h3>
";

$template = str_replace("<!-- #SUMMARY_VALUES# -->", $summaryHTML, $template);

/* ---------------------------------------------------------
   SUMMARY CHART SCRIPT
--------------------------------------------------------- */
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

/* ---------------------------------------------------------
   OUTPUT PAGE
--------------------------------------------------------- */
echo $template;

?>
