let assignModal = new bootstrap.Modal(document.getElementById('assignBoxesModal'));

document.querySelectorAll(".assignBtn").forEach(btn => {

    btn.addEventListener("click", function (e) {

        if (this.dataset.loading === "true") return;

        this.dataset.loading = "true";

        let palletSelect = this.closest(".card-body").querySelector(".palletSelect");

        if (!palletSelect.value) {

            e.preventDefault();
            e.stopImmediatePropagation();

            alert("Please select a pallet first.");
            this.dataset.loading = "false";
            return;
        }

        let qty = this.dataset.qty;
        let product = this.dataset.product;
        let id = this.dataset.id;

        let palletId = palletSelect.value;

        document.getElementById("pallet_id").value = palletId;

        let palletCode = palletSelect.options[palletSelect.selectedIndex].text;

        document.getElementById("delivery_item_id").value = id;
        document.getElementById("assign_product").value = product;

        let container = document.getElementById("boxesContainer");

        container.innerHTML = "";

        fetch("../controllers/warehouse_controller.php?action=get_boxes&delivery_item_id=" + id)

            .then(res => res.json())

            .then(boxes => {

                let existing = boxes.length;

                boxes.forEach(box => {

                    container.innerHTML += `
<tr>

<td>
<input type="hidden" name="box_id[]" value="${box.box_id}">
<input type="number" step="0.01" name="weight[]" value="${box.box_weight}" class="form-control weight">
</td>

<td>
<input type="text" name="size[]" value="${box.box_size}" class="form-control size">
</td>

<td>
<input type="text" name="batch[]" value="${box.batch_code}" class="form-control">
</td>

<td>
<input type="hidden" name="pallet[]" value="${palletCode}">
<input type="text" class="form-control" value="${palletCode}" readonly>
</td>

<td>
<input type="date" name="expiry[]" value="${box.expiry_date}" class="form-control">
</td>

</tr>
`;

                });

                let remaining = qty - existing;

                for (let i = 0; i < remaining; i++) {

                    container.innerHTML += `
<tr>

<td>
<input type="hidden" name="box_id[]" value="">
<input type="number" step="0.01" name="weight[]" class="form-control weight">
</td>

<td>
<input type="text" name="size[]" class="form-control size" readonly>
</td>

<td>
<input type="text" name="batch[]" class="form-control">
</td>

<td>
<input type="hidden" name="pallet[]" value="${palletCode}">
<input type="text" class="form-control" value="${palletCode}" readonly>
</td>

<td>
<input type="date" name="expiry[]" class="form-control">
</td>

</tr>
`;

                }

                assignModal.show();
                btn.dataset.loading = "false";

            });

    });

});


document.addEventListener("input", function (e) {

    if (e.target.classList.contains("weight")) {

        let weight = parseFloat(e.target.value);

        let sizeField = e.target.closest("tr").querySelector(".size");

        if (weight >= 18 && weight <= 22)
            sizeField.value = "SMALL";

        else if (weight >= 23 && weight <= 26)
            sizeField.value = "MEDIUM";

        else if (weight >= 27)
            sizeField.value = "LARGE";

        else
            sizeField.value = "";

    }

});

let palletModal = new bootstrap.Modal(document.getElementById("viewPalletModal"));

document.querySelectorAll(".viewPalletBtn").forEach(card => {

    card.addEventListener("click", function () {

        let palletId = this.dataset.id;
        let palletCode = this.dataset.code;

        document.getElementById("palletCodeTitle").innerText = palletCode;

        fetch("../controllers/warehouse_controller.php?action=get_pallet_boxes&pallet_id=" + palletId)

            .then(res => res.json())

            .then(boxes => {

                boxes.sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date));

                let container = document.getElementById("palletBoxesContainer");
                container.innerHTML = "";

                let grouped = {};
                let totalWeight = 0;

                boxes.forEach(box => {

                    let product = box.product_name;
                    let weight = parseFloat(box.box_weight);

                    totalWeight += weight;

                    if (!grouped[product]) {

                        grouped[product] = {
                            boxes: [],
                            totalWeight: 0
                        };

                    }

                    grouped[product].boxes.push(box);
                    grouped[product].totalWeight += weight;

                });

                document.getElementById("palletBoxCount").innerText = boxes.length;
                document.getElementById("palletTotalWeight").innerText = totalWeight.toFixed(2);

                if (boxes.length === 0) {

                    container.innerHTML = `
        <tr>
            <td colspan="3" class="text-center text-muted">
                No boxes on this pallet
            </td>
        </tr>
        `;

                } else {

                    Object.keys(grouped).forEach((product, index) => {

                        let group = grouped[product];
                        let collapseId = "productBoxes" + index;

                        container.innerHTML += `
            <tr class="product-row" data-bs-toggle="collapse" data-bs-target="#${collapseId}" style="cursor:pointer;">
                <td><strong>${product}</strong></td>
                <td>${group.boxes.length}</td>
                <td>${group.totalWeight.toFixed(2)} kg</td>
            </tr>
            `;

                        group.boxes.forEach(box => {

                            container.innerHTML += `
                <tr class="collapse box-row" id="${collapseId}">
                    <td colspan="3" class="ps-4 text-muted">
                        └ ${parseFloat(box.box_weight).toFixed(2)} kg | Batch ${box.batch_code} | Exp ${box.expiry_date}
                    </td>
                </tr>
                `;

                        });

                    });

                }

                palletModal.show();

            });

    });

});