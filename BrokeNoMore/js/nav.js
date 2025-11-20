async function loadNotifCount() {
    const res = await fetch('unread_count.php');
    const count = await res.text();

    let badge = document.getElementById('notifBadge');
    if (!badge) return;

    if (parseInt(count) > 0) {
        badge.textContent = count;
    } else {
        badge.textContent = "";
    }
}

loadNotifCount();

setInterval(loadNotifCount, 30000);