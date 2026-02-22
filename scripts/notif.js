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

// scripts/notif.js (or append to existing file)

// Function to show success/error toast
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');

    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0 shadow`;
    toastEl.role = 'alert';
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);

    // Initialize and show the toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 5000  // 5 seconds
    });
    toast.show();

    // Auto-remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}