document.addEventListener("DOMContentLoaded", function () {

    const deliveryModal =
        new bootstrap.Modal(document.getElementById("deliveryModal"));

    const loadBtn = document.getElementById("loadDRBtn");
    const modeInput = document.getElementById("delivery_mode");
    const form = document.getElementById("deliveryForm");
    const title = document.querySelector("#deliveryModal .modal-title");

    // CREATE
    document.getElementById("openCreateDelivery").addEventListener("click", () => {

        modeInput.value = "create";

        title.innerHTML =
            `<i class="fa fa-truck me-2 text-success"></i> Receive Delivery`;

        loadBtn.classList.add("d-none");

        form.reset();

        document.querySelector("#itemsTable tbody").innerHTML = "";

        deliveryModal.show();

    });

    // EDIT
    document.getElementById("openEditDelivery").addEventListener("click", () => {

        modeInput.value = "edit";

        title.innerHTML =
            `<i class="fa fa-edit me-2 text-warning"></i> Edit Delivery`;

        loadBtn.classList.remove("d-none");

        form.reset();

        document.querySelector("#itemsTable tbody").innerHTML = "";

        deliveryModal.show();

    });

});

function productOptions(selectedId) {

    if (!window.PRODUCTS) return "";

    return PRODUCTS.map(p => `
        <option value="${p.product_id}" ${p.product_id == selectedId ? "selected" : ""}>
            ${p.product_name}
        </option>
    `).join("");

}

document.getElementById("loadDRBtn").addEventListener("click", function () {

    let dr = document.getElementById("dr_number").value.trim();

    if (!dr) {
        alert("Please enter a DR number first.");
        return;
    }

    fetch("../controllers/stock_controller.php?action=get_delivery_by_dr&dr=" + dr)

        .then(res => res.json())

        .then(data => {

            if (!data || !data.items || data.items.length === 0) {

                alert("Delivery Receipt not found.");
                return;
            }

            // store receipt id
            document.getElementById("delivery_receipt_id").value =
                data.delivery_receipt_id;

            const tbody = document.querySelector("#itemsTable tbody");

            tbody.innerHTML = "";

            data.items.forEach(item => {

                tbody.innerHTML += `
<tr class="item-row">

<input type="hidden" name="item_id[]" value="${item.delivery_item_id}">

<td>
<select name="product_id[]" class="form-control">
<option value="">Select product</option>
${productOptions(item.product_id)}
</select>
</td>

<td>
<input type="number"
       name="qty[]"
       value="${item.qty}"
       class="form-control qty">
</td>

<td>
<input type="text"
       name="unit[]"
       value="BOX"
       class="form-control"
       readonly>
</td>

<td>
<input type="number"
       step="0.01"
       name="weight[]"
       value="${item.total_weight}"
       class="form-control weight">
</td>

<td>
<input type="number"
       step="0.01"
       name="price[]"
       value="${item.price_per_kg}"
       class="form-control price">
</td>

<td>
<input type="number"
       step="0.01"
       name="amount[]"
       value="${item.total_amount}"
       class="form-control amount"
       readonly>
</td>

<td>
<button type="button" class="btn btn-danger removeRow">X</button>
</td>

</tr>
`;

            });

        })

        .catch(err => {

            console.error(err);
            alert("Failed to load delivery receipt.");

        });

});