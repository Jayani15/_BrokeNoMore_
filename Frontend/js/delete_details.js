function deleteItem(id) {
  if (confirm("Are you sure you want to delete this category?")) {
    fetch('delete_details.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.text())
    .then(data => {
      if (data === "success") {
        const row = document.getElementById("row-" + id);
        if (row) {
          row.style.transition = "opacity 0.3s";
          row.style.opacity = 0;
          setTimeout(() => row.remove(), 300);
        }
      } else {
        alert("Failed to delete category.");
      }
    });
  }
}
