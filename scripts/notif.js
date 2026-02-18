function toggleNotificationPopup() {
    var popup = document.getElementById("notificationPopup");
    if (popup.style.display === "block") {
        popup.style.display = "none";
    } else {
        popup.style.display = "block";
    }
}

// Optional: Close the popup when clicking outside of it
window.onclick = function(event) {
    var popup = document.getElementById("notificationPopup");
    if (!event.target.matches('.notification-icon') && popup.style.display === 'block') {
        popup.style.display = 'none';
    }
}