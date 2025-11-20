window.addEventListener("DOMContentLoaded", function () {

    // If backend didn't provide data, exit safely
    if (!Array.isArray(budgetCheckData)) return;

    budgetCheckData.forEach(budget => {

        const amount = Number(budget.amount);          // total budget amount
        const allocated = Number(budget.allocated);    // sum of allocated categories
        const name = budget.budget_name;               // budget name

        if (amount < allocated) {
            alert(
                `⚠️ Warning: In budget "${name}", your total budget amount (₹${amount}) is less ` +
                `than the allocated amount (₹${allocated}).`
            );
        }
    });
});
