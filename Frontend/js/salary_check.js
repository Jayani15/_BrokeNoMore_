window.addEventListener("DOMContentLoaded", function () {
  if (!Array.isArray(budgetCheckData)) return;

  budgetCheckData.forEach(budget => {
    if (budget.income < budget.allocated) {
      alert(`⚠Warning: In budget "${budget.name}", your salary (₹${budget.income}) is less than the total allocated amount (₹${budget.allocated})`);
    }
  });
});
