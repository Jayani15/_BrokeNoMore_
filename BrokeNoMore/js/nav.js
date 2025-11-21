// js/nav.js
// Keeps the notifications unread-count badge updated.
// Expects an element with id="notif-count" inside a .notif-wrapper link.

(function () {
  const BADGE_ID = 'notif-count';
  // Use relative path so it works inside a project subfolder
  const ENDPOINT = 'unread_count.php';
  const POLL_MS = 30000; // refresh every 30s

  function setBadge(n) {
    const badge = document.getElementById(BADGE_ID);
    if (!badge) return;
    if (!n || Number(n) <= 0) {
      badge.style.display = 'none';
      badge.textContent = '0';
    } else {
      badge.style.display = 'inline-block';
      badge.textContent = String(n);
    }
  }

  async function fetchCount() {
    try {
      const res = await fetch(ENDPOINT, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network response was not ok');
      // try parse JSON; fall back to text integer
      const text = await res.text();
      let count = 0;
      try {
        const js = JSON.parse(text);
        if (typeof js.count !== 'undefined') count = parseInt(js.count, 10) || 0;
        else count = parseInt(text, 10) || 0;
      } catch {
        count = parseInt(text.trim(), 10) || 0;
      }
      setBadge(count);
    } catch (err) {
      // On network errors do nothing — keep existing badge (or hide)
      console.error('Failed to fetch unread count', err);
    }
  }

  // Run once on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', fetchCount);
  } else {
    fetchCount();
  }

  // Poll
  setInterval(fetchCount, POLL_MS);

  // Expose a function other scripts can call after marking read
  window.updateNotifCount = fetchCount;
})();
