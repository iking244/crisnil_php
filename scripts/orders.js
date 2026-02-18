// orders.js ─ job order specific logic only

document.addEventListener('DOMContentLoaded', () => {

    // ── Edit icons → open modal ─────────────────────────────
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const row = icon.closest('tr');
            const cells = row.querySelectorAll('td');

            const id = cells[1].textContent.trim().replace('#', '');
            const origin = cells[2].textContent.trim();
            const destination = cells[3].textContent.trim();
            const statusText = cells[4].textContent.trim();
            const createdAt = cells[5].textContent.trim();
            const eta = cells[6].textContent.trim();

            const statusMap = {
                "Pending": "pending",
                "In Transit": "in_transit",
                "Completed": "completed",
                "Cancelled": "cancelled"
            };

            const statusValue = statusMap[statusText] || "pending";

            document.getElementById('editJobId').value = id;
            document.getElementById('editJobIdDisplay').textContent = id;
            document.getElementById('editOrigin').value = origin;
            document.getElementById('editDestination').value = destination;
            document.getElementById('editStatus').value = statusValue;
            document.getElementById('editCreatedAt').value = createdAt;
            document.getElementById('editETA').value = eta;

            loadJobItems(id);

            const editModal = new bootstrap.Modal(
                document.getElementById('editOrderModal')
            );
            editModal.show();
        });
    });
});


// ── Load job order items ───────────────────────────────────
function loadJobItems(jobId) {
    console.log("Loading items for job:", jobId);
    fetch(`../controllers/logistics_orders_controller.php?action=get_items&id=${jobId}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector("#itemsTable tbody");
            if (!tbody) return;

            tbody.innerHTML = "";

            data.forEach(item => {
                tbody.insertAdjacentHTML("beforeend", `
                    <tr>
                        <td>
                            <select name="product_id[]" class="form-control product-select">
                                <option value="${item.product_id}" selected>
                                    ${item.product_name}
                                </option>
                            </select>
                        </td>
                        <td class="stock-cell">${item.stock_qty ?? 0}</td>
                        <td>
                            <input type="number" 
                                   name="quantity[]" 
                                   class="form-control" 
                                   value="${item.quantity}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove item">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        })
        .catch(err => console.error("Error loading items:", err));
}


// ── Add item row ───────────────────────────────────────────
function addItemRow(tableId = 'itemsTable') {
    const tbody = document.getElementById(tableId).querySelector('tbody');
    const row = tbody.insertRow();

    row.innerHTML = `
        <td>
            <select name="product_id[]" class="form-select product-select" required>
                <option value="">Select Product</option>
                ${window.productList.map(p => 
                    `<option value="${p.id}">${p.name}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="number"
                   name="quantity[]"
                   class="form-control"
                   min="1"
                   value="1"
                   required>
        </td>
        <td class="text-center">
            <button type="button"
                    class="btn btn-sm btn-danger remove-row"
                    title="Remove item">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;

    row.querySelector('.remove-row').addEventListener('click', function () {
        this.closest('tr').remove();
    });
}

// ── Remove row ─────────────────────────────────────────────
function removeRow(btn) {
    btn.closest("tr").remove();
}

// ── Two-step cancel/delete logic ─────────────────────
document.addEventListener('click', function (e) {
    const icon = e.target.closest('.delete-icon');
    if (!icon) return;

    const jobId = icon.dataset.id;
    const status = icon.dataset.status;

    if (status === 'pending' || status === 'assigned') {
        if (confirm("This job order will be cancelled first.\nContinue?")) {
            window.location.href =
                `../controllers/logistics_orders_controller.php?action=delete&id=${jobId}`;
        }
        return;
    }

    if (status === 'cancelled') {
        if (confirm("Delete this cancelled job order permanently?")) {
            window.location.href =
                `../controllers/logistics_orders_controller.php?action=delete&id=${jobId}`;
        }
        return;
    }

    alert("This job order cannot be cancelled or deleted.");
});



// ── Stock lookup ───────────────────────────────────────────
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('product-select')) {
        const productId = e.target.value;
        const row = e.target.closest('tr');
        const stockCell = row.querySelector('.stock-cell');

        if (!productId) {
            stockCell.textContent = "0";
            return;
        }

        fetch(`../controllers/logistics_orders_controller.php?action=get_stock&product_id=${productId}`)
            .then(res => res.json())
            .then(data => {
                stockCell.textContent = data.quantity ?? 0;
            });
    }
});
