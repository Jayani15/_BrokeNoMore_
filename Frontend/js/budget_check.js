const urlParams = new URLSearchParams(window.location.search);
  const budgetId = urlParams.get('id');

  if (!budgetId) {
    alert("No budget ID provided in URL! Please add ?id=your_budget_id");
    document.getElementById('categoryForm').style.display = 'none';
  } else {
    document.getElementById('categoryForm').action = 'addnew_category.php?id=' + budgetId;

    document.getElementById('categoryForm').addEventListener('submit', function(e) {
      e.preventDefault(); 
      const allocatedInput = document.querySelector('input[name="allocated_amount"]');
      const allocated = parseFloat(allocatedInput.value);

      if (isNaN(allocated) || allocated <= 0) {
        alert("Please enter a valid allocated amount.");
        return;
      }

      fetch(`addnew_category.php?id=${budgetId}&check=true`)
        .then(res => res.json())
        .then(data => {
          const income = parseFloat(data.income);
          const allocatedSum = parseFloat(data.allocated);
          const newTotal = allocatedSum + allocated;

          if (newTotal > income) {
            alert("You've exceeded your budget limit for this month!");
          } else {
            e.target.submit();
          }
        })
        .catch(err => {
          console.error("Check failed", err);
          alert("Something went wrong while validating the budget.");
        });
    });
  }
