document.addEventListener("DOMContentLoaded", () => {

    const modalEl = document.getElementById("deliveryModal");
    const deliveryModal = new bootstrap.Modal(modalEl);

    const form = document.getElementById("deliveryForm");
    const title = modalEl.querySelector(".modal-title");

    const loadBtn = document.getElementById("loadDRBtn");
    const drInput = document.getElementById("dr_number");

    const modeInput = document.getElementById("delivery_mode");
    const receiptIdInput = document.getElementById("delivery_receipt_id");

    const tbody = document.querySelector("#itemsTable tbody");

    const createBtn = document.getElementById("openCreateDelivery");
    const editBtn = document.getElementById("openEditDelivery");

    console.log("tubmagos");

    // -----------------------------------
    // PRODUCT OPTIONS GENERATOR
    // -----------------------------------
    function productOptions(selectedId = null) {

        if (!window.PRODUCTS || !Array.isArray(window.PRODUCTS)) {
            console.warn("PRODUCTS array not found");
            return "";
        }

        return window.PRODUCTS.map(p => `
            
            <option value="${p.product_id}" ${p.product_id == selectedId ? "selected" : ""}>
                ${p.product_name}
            </option>
        `).join("");

    }



    // -----------------------------------
    // RESET MODAL
    // -----------------------------------
    function resetModal() {

        form.reset();
        tbody.innerHTML = "";
        receiptIdInput.value = "";

    }



    // -----------------------------------
    // OPEN CREATE DELIVERY
    // -----------------------------------
    if (createBtn) {

        createBtn.addEventListener("click", () => {

            modeInput.value = "create";

            title.innerHTML =
                `<i class="fa fa-truck me-2 text-success"></i> Receive Delivery`;

            loadBtn.classList.add("d-none");

            resetModal();

            deliveryModal.show();

        });

    }



    // -----------------------------------
    // OPEN EDIT DELIVERY
    // -----------------------------------
    if (editBtn) {

        editBtn.addEventListener("click", () => {

            modeInput.value = "edit";

            form.reset

            resetModal();
            
            loadBtn.classList.remove("d-none");
            
            title.innerHTML =
                `<i class="fa fa-edit me-2 text-warning"></i> Edit Delivery`;
           

            deliveryModal.show();

        });

    }



    // -----------------------------------
    // LOAD DELIVERY BY DR
    // -----------------------------------
    if (loadBtn) {

        loadBtn.addEventListener("click", async () => {

            const dr = drInput.value.trim();

            if (!dr) {
                alert("Please enter a DR number first.");
                return;
            }

            try {

                const res = await fetch(
                    `../controllers/stock_controller.php?action=get_delivery_by_dr&dr=${dr}`
                );

                const data = await res.json();

                if (!data || !data.items || data.items.length === 0) {

                    alert("Delivery Receipt not found.");
                    return;

                }

                receiptIdInput.value = data.delivery_receipt_id;

                tbody.innerHTML = "";

                data.items.forEach(item => {

                    const row = `
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

                    tbody.insertAdjacentHTML("beforeend", row);

                });

            } catch (err) {

                console.error(err);
                alert("Failed to load delivery receipt.");

            }

        });

    }

});