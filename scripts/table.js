// table.js ─ shared table features
// Used by: orders, products, and any table-based pages

function initTableFeatures() {
    const table = document.getElementById('ordersTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const searchInput = document.getElementById('searchInput');
    const selectAll = document.getElementById('selectAll');
    const exportBtn = document.querySelector('.export-btn');

    // ── Live search ─────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();

            tbody.querySelectorAll('tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // ── Select all checkbox ─────────────────────────────────
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            tbody.querySelectorAll('.row-check').forEach(cb => {
                cb.checked = selectAll.checked;
            });
        });
    }

    // ── CSV export ──────────────────────────────────────────
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const visibleRows = Array.from(tbody.querySelectorAll('tr'))
                .filter(row => row.style.display !== 'none');

            if (visibleRows.length === 0) {
                alert('No visible rows to export.');
                return;
            }

            const csvRows = [];

            visibleRows.forEach(row => {
                const cols = row.querySelectorAll('td');
                const rowData = Array.from(cols).map(td =>
                    `"${td.textContent.trim()}"`
                );
                csvRows.push(rowData.join(','));
            });

            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = url;
            link.download = `export_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();

            URL.revokeObjectURL(url);
        });
    }

    // ── Fade-in animation ───────────────────────────────────
    const tableContainer = document.querySelector('.table-container');
    if (tableContainer) {
        tableContainer.style.opacity = '0';
        tableContainer.style.transition = 'opacity 0.6s ease';
        setTimeout(() => {
            tableContainer.style.opacity = '1';
        }, 100);
    }

    // ── Column sorting ──────────────────────────────────────
    const headers = table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if (index === 0 || index === headers.length - 1) return;

        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const dir = header.classList.contains('asc') ? 'desc' : 'asc';
            headers.forEach(h => h.classList.remove('asc', 'desc'));
            header.classList.add(dir);

            const rowsArray = Array.from(tbody.querySelectorAll('tr'));

            rowsArray.sort((a, b) => {
                let aVal = a.cells[index].textContent.trim();
                let bVal = b.cells[index].textContent.trim();

                if (!isNaN(parseFloat(aVal)) && !isNaN(parseFloat(bVal))) {
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

    console.log('Shared table features initialized');
}

// run on page load
document.addEventListener('DOMContentLoaded', initTableFeatures);
