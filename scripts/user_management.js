// user_management.js
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('#usersTable tbody');

    // ── 1. Live search ────────────────────────────────────────────────────────
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    }

    // ── 2. Select All checkbox ────────────────────────────────────────────────
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // ── 3. Edit icon → open modal with pre-filled data ────────────────────────
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const row = icon.closest('tr');
            const cells = row.querySelectorAll('td');

            document.getElementById('editUserId').value = cells[1].textContent.trim().replace('#', '');
            document.getElementById('editUserIdDisplay').textContent = cells[1].textContent.trim().replace('#', '');
            document.getElementById('editFirstName').value = cells[2].textContent.trim();
            document.getElementById('editLastName').value = cells[3].textContent.trim();
            // Add more pre-fills for other fields as needed (email, phone, etc.)

            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        });
    });

    // ── 4. Archive icon → confirm + fetch POST ────────────────────────────────
    document.querySelectorAll('.archive-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const id = icon.dataset.id;
            if (confirm(`Archive user #${id}? This cannot be undone easily.`)) {
                fetch(`../controllers/user_controller.php?action=archive&id=${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'confirm=1'  // optional extra security token if needed later
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Archive failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error('Archive error:', err);
                    alert('Network error while archiving');
                });
            }
        });
    });

    // ── 5. Registration form client-side validation ───────────────────────────
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', e => {
            const username = registerForm.querySelector('input[name="username"]').value.trim();
            const password = registerForm.querySelector('input[name="password"]').value;

            // Username: 4-20 chars, alphanumeric + _ -
            if (!/^[a-zA-Z0-9_-]{4,20}$/.test(username)) {
                alert('Username must be 4-20 characters long and contain only letters, numbers, underscores, or hyphens. No spaces or special characters.');
                e.preventDefault();
                return;
            }

            // Password strength (basic client-side check)
            if (password.length < 8 ||
                !/[A-Z]/.test(password) ||
                !/[a-z]/.test(password) ||
                !/[0-9]/.test(password) ||
                !/[\W_]/.test(password)) {
                alert('Password must be at least 8 characters and contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
                e.preventDefault();
                return;
            }

            // You can add more checks (email format, phone digits, etc.)
        });
    }

    console.log('User Management page scripts loaded');
});