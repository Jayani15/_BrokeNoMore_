function deleteBudget(id) {
    if (confirm("Are you sure you want to delete this budget?")) {
        fetch('delete_budget.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                alert("Budget deleted!");
                location.reload(); 
            } else {
                alert(" Failed to delete budget.");
            }
        });
    }
}
