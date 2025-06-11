function deleteItem(id) {
  if (confirm("Are you sure you want to delete this item?")) {
    fetch('delete_item.php', {
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
        if (row) row.remove(); 
      } else {
        alert("Failed to delete item.");
      }
    });
  }
}
function deleteItem(id) {
  if (confirm("Are you sure you want to delete this item?")) {
    fetch('delete_item.php', {
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
        if (row) row.remove(); 
      } else {
        alert("Failed to delete item.");
      }
    });
  }
}
