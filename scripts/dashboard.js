function updateDateTime() {
    const dateEl = document.getElementById("currentDate");
    const timeEl = document.getElementById("currentTime");

    if (!dateEl || !timeEl) return;

    const now = new Date();

    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    };

    dateEl.textContent = now.toLocaleDateString('en-US', options);
    timeEl.textContent = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
}

updateDateTime();
setInterval(updateDateTime, 1000);

