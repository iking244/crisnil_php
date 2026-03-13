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

// get table body
const tableBody = document.querySelector("#itemsTable tbody");

// store template row
const templateRow = tableBody.querySelector("tr").cloneNode(true);

// ADD ITEM
document.getElementById("addRow").addEventListener("click", function () {

    let newRow = templateRow.cloneNode(true);

    // clear inputs
    newRow.querySelectorAll("input").forEach(input => input.value = "");

    // reset dropdowns
    newRow.querySelectorAll("select").forEach(select => select.selectedIndex = 0);

    // add row
    tableBody.appendChild(newRow);

    // scroll to bottom
    document.querySelector(".items-container").scrollTop =
        document.querySelector(".items-container").scrollHeight;

    // focus first field
    newRow.querySelector("select").focus();

});


// REMOVE ITEM
document.addEventListener("click", function (e) {

    if (e.target.classList.contains("removeRow")) {

        e.target.closest("tr").remove();

        // if no rows left, add empty row
        if (tableBody.children.length === 0) {

            let newRow = templateRow.cloneNode(true);

            tableBody.appendChild(newRow);

        }

    }

});

document.addEventListener("input", function (e) {

    if (e.target.classList.contains("weight") || e.target.classList.contains("price")) {

        let row = e.target.closest("tr");

        let weight = parseFloat(row.querySelector(".weight").value) || 0;
        let price = parseFloat(row.querySelector(".price").value) || 0;

        row.querySelector(".amount").value = (weight * price).toFixed(2);
    }

});

document.getElementById("deliveryForm").addEventListener("submit", function (e) {

    e.preventDefault();

    let form = this;
    let formData = new FormData(form);
    let errorBox = document.getElementById("deliveryError");

    errorBox.classList.add("d-none");

    fetch(form.action, {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            if (data.status === "error") {

                errorBox.innerText = data.message;
                errorBox.classList.remove("d-none");

            } else {

                location.reload();

            }

        })
        .catch(err => {

            errorBox.innerText = "Unexpected error occurred.";
            errorBox.classList.remove("d-none");

        });

});


document.getElementById("loadDRBtn").addEventListener("click", function () {

    let dr = document.getElementById("edit_dr_number").value;

    fetch("../controllers/stock_controller.php?action=get_delivery_by_dr&dr=" + dr)

        .then(res => res.json())

        .then(data => {

            let tbody = document.querySelector("#editItemsTable tbody");

            tbody.innerHTML = "";

            data.items.forEach(item => {

                tbody.innerHTML += `
            <tr class="item-row">

                <input type="hidden" name="item_id[]" value="${item.delivery_item_id}">

                <td>
                    <select name="product_id[]" class="form-control">
                        <option value="">Select product</option>
                        ${data.products.map(p => `
                            <option value="${p.product_id}" ${p.product_id == item.product_id ? "selected" : ""}>
                                ${p.product_name}
                            </option>
                        `)}
                            
                    </select>
                </td>

                <td>
                    <input type="number" name="qty[]" value="${item.qty}" class="form-control">
                </td>

                <td>
                    <input type="text" name="unit[]" value="BOX" class="form-control" readonly>
                </td>

                <td>
                    <input type="number" step="0.01" name="weight[]" value="${item.total_weight}" class="form-control weight">
                </td>

                <td>
                    <input type="number" step="0.01" name="price[]" value="${item.price_per_kg}" class="form-control price">
                </td>

                <td>
                    <input type="number" step="0.01" name="amount[]" value="${item.total_amount}" class="form-control amount" readonly>
                </td>

                <td>
                    <button type="button" class="btn btn-danger removeRow">X</button>
                </td>

            </tr>
            `;

            });

            document.getElementById("edit_delivery_receipt_id").value = data.delivery_receipt_id;

        });

});