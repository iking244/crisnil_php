// orders.js ────────────────────────────────────────────────────────────────
// Features:
//   - Live search
//   - Select-all checkbox
//   - CSV export
//   - Edit modal with item loading
//   - Column sorting
//   - Date range filter
//   - Status lock logic
//   - Add/remove item rows
//   - Product dropdown with stock lookup

document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('ordersTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const searchInput = document.getElementById('searchInput');
    const selectAll = document.getElementById('selectAll');
    const exportBtn = document.querySelector('.export-btn');
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');

    // ── Live search ────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    // ── Select All checkbox ────────────────────────────────────────────────
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            tbody.querySelectorAll('.row-check').forEach(cb => {
                cb.checked = selectAll.checked;
            });
        });
    }

    // ── Edit icons → open modal with restrictions ─────────────────────────
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

            // Map status text → value
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

            // Load job order items
            loadJobItems(id);

            // Lock modal if not editable
            const lockedStatuses = ['in_transit', 'completed', 'cancelled'];
            const isLocked = lockedStatuses.includes(statusValue);

            const modal = document.getElementById('editOrderModal');
            const inputs = modal.querySelectorAll('input, select, button');

            inputs.forEach(el => {
                if (el.type !== "button" || el.textContent.includes("Close")) {
                    el.disabled = isLocked;
                }
            });

            // Allow cancel if pending
            const statusSelect = document.getElementById('editStatus');
            if (statusValue === 'pending') {
                statusSelect.disabled = false;
            }

            const editModal = new bootstrap.Modal(modal);
            editModal.show();
        });
    });

    // ── Export visible rows to CSV ─────────────────────────────────────────
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const visibleRows = Array.from(tbody.querySelectorAll('tr'))
                .filter(row => row.style.display !== 'none');

            if (visibleRows.length === 0) {
                alert('No visible rows to export.');
                return;
            }

            const headers = ['ID', 'Origin', 'Destination', 'Status', 'Created At', 'ETA'];
            const csvRows = [headers.join(',')];

            visibleRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [
                    cells[1].textContent.trim().replace('#', ''),
                    `"${cells[2].textContent.trim().replace(/"/g, '""')}"`,
                    `"${cells[3].textContent.trim().replace(/"/g, '""')}"`,
                    cells[4].textContent.trim(),
                    cells[5].textContent.trim(),
                    cells[6].textContent.trim()
                ];
                csvRows.push(rowData.join(','));
            });

            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `orders_export_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        });
    }

    // ── Column sorting ─────────────────────────────────────────────────────
    const headers = table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if (index < 1 || index > 6) return;

        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const dir = header.classList.contains('asc') ? 'desc' : 'asc';
            headers.forEach(h => h.classList.remove('asc', 'desc'));
            header.classList.add(dir);

            const rowsArray = Array.from(tbody.querySelectorAll('tr'));
            rowsArray.sort((a, b) => {
                let aVal = a.cells[index].textContent.trim();
                let bVal = b.cells[index].textContent.trim();

                if (index === 5 || index === 6) {
                    aVal = new Date(aVal) || 0;
                    bVal = new Date(bVal) || 0;
                } else if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
                    aVal = parseFloat(aVal);
                    bVal = parseFloat(bVal);
                }

                if (aVal < bVal) return dir === 'asc' ? -1 : 1;
                if (aVal > bVal) return dir === 'asc' ? 1 : -1;
                return 0;
            });

            rowsArray.forEach(row => tbody.appendChild(row));
        });
    });

    // ── Date range filter ──────────────────────────────────────────────────
    function filterTable() {
        const from = fromDate?.value ? new Date(fromDate.value) : null;
        const to = toDate?.value ? new Date(toDate.value) : null;

        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const createdCell = row.cells[5]?.textContent.trim();
            if (!createdCell) return;

            const rowDate = new Date(createdCell.split(' ')[0]);
            let show = true;

            if (from && rowDate < from) show = false;
            if (to && rowDate > to) show = false;

            row.style.display = show ? '' : 'none';
        });

        if (searchInput?.value) {
            const filter = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        }
    }

    if (fromDate && toDate) {
        fromDate.addEventListener('change', filterTable);
        toDate.addEventListener('change', filterTable);
    }

    console.log('Orders page features loaded');
});


// ── Load job order items (AJAX) ───────────────────────────────────────────
function loadJobItems(jobId) {
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
                        <td>
                            <button type="button" 
                                    class="btn btn-danger btn-sm"
                                    onclick="removeRow(this)">X</button>
                        </td>
                    </tr>
                `);
            });
        })
        .catch(err => console.error("Error loading items:", err));
}


// ── Add item row ──────────────────────────────────────────────────────────
function addItemRow() {
    const tbody = document.querySelector("#itemsTable tbody");
    if (!tbody || !window.productList) {
        console.error("productList not loaded");
        return;
    }

    let options = window.productList.map(p =>
        `<option value="${p.id}">${p.name} (${p.unit})</option>`
    ).join("");

    tbody.insertAdjacentHTML("beforeend", `
        <tr>
            <td>
                <select name="product_id[]" class="form-control product-select">
                    <option value="">Select product</option>
                    ${options}
                </select>
            </td>
            <td class="stock-cell">0</td>
            <td>
                <input type="number" 
                       name="quantity[]" 
                       class="form-control" 
                       value="1" min="1">
            </td>
            <td>
                <button type="button" 
                        class="btn btn-danger btn-sm"
                        onclick="removeRow(this)">X</button>
            </td>
        </tr>
    `);
}



// ── Remove item row ───────────────────────────────────────────────────────
function removeRow(btn) {
    btn.closest("tr").remove();
}


// ── Stock lookup when product changes ─────────────────────────────────────
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

function addCreateItemRow() {
    const tbody = document.querySelector("#createItemsTable tbody");
    if (!tbody || !window.productList) return;

    let options = window.productList.map(p =>
        `<option value="${p.id}">${p.name} (${p.unit})</option>`
    ).join("");

    tbody.insertAdjacentHTML("beforeend", `
        <tr>
            <td>
                <select name="product_id[]" class="form-control">
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" name="quantity[]" class="form-control" value="1" min="1">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button>
            </td>
        </tr>
    `);
}




