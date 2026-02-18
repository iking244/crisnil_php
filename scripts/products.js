// products.js ─ product-specific logic

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.edit-product').forEach(icon => {
        icon.addEventListener('click', () => {

            // Fill product fields
            document.getElementById('editProductId').value = icon.dataset.id;
            document.getElementById('editCode').value = icon.dataset.code;
            document.getElementById('editName').value = icon.dataset.name;

            // Stock is now batch-based (display only)
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

