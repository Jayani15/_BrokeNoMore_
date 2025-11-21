function deleteItem(id) {

    if (!id) {
        alert("Invalid expense ID.");
        return;
    }

    if (confirm("Are you sure you want to delete this expense?")) {

        fetch('delete_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)   // UUID-safe
        })
        .then(response => response.text())
        .then(data => {

            if (data.trim() === "success") {

                const row = document.getElementById("row-" + id);
                if (row) {
                    row.style.transition = "opacity 0.3s";
                    row.style.opacity = 0;

                    setTimeout(() => row.remove(), 300);
                }

            } else {
                alert("Failed to delete expense.");
            }

        })
        .catch(err => {
            console.error("Delete error:", err);
            alert("Something went wrong while deleting.");
        });
    }
}
