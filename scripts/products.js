// products.js ─ product-specific logic
document.addEventListener('DOMContentLoaded', () => {

    // Use event delegation for dynamically loaded icons
    document.addEventListener('click', function (e) {
        const icon = e.target.closest('.edit-product');
        if (!icon) return;

        // Fill product fields
        document.getElementById('editProductId').value = icon.dataset.id;
        document.getElementById('editCode').value = icon.dataset.code;
        document.getElementById('editName').value = icon.dataset.name;

        // Stock is display only
        const qtyField = document.getElementById('editQty');
        if (qtyField) {
            qtyField.value = icon.dataset.qty;
            qtyField.setAttribute('readonly', true);
        }

        // Unit & packaging
        document.getElementById('editUnit').value = icon.dataset.unitId;
        document.getElementById('editWeightPerUnit').value = icon.dataset.weightPerUnit;
        document.getElementById('editUnitsPerPallet').value = icon.dataset.unitsPerPallet;

        // Open modal
        new bootstrap.Modal(
            document.getElementById('editProductModal')
        ).show();
    });

    console.log("Products module loaded");
});


// Floating action button toggle
const fabMain = document.getElementById('fabMain');
const fabOptions = document.getElementById('fabOptions');

if (fabMain) {
    fabMain.addEventListener('click', () => {
        fabOptions.classList.toggle('show');
    });
}

let currentPage = 1;

function loadProducts(page = 1) {
    const pageSize = document.getElementById("pageSize").value;

    const warehouseSelect = document.querySelector('select[name="warehouse_id"]');
    const warehouse_id = warehouseSelect ? warehouseSelect.value : 0;

    fetch(`../controllers/products_controller_ajax.php?page=${page}&pageSize=${pageSize}&warehouse_id=${warehouse_id}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("productsTableContainer").innerHTML = html;
            initTableFeatures();
        });
}

document.addEventListener("DOMContentLoaded", function () {
    const pageSizeSelect = document.getElementById("pageSize");

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener("change", () => {
            loadProducts(1);
        });
    }

    loadProducts(1);
});


