let assignModal = new bootstrap.Modal(document.getElementById('assignBoxesModal'));
document.querySelectorAll(".assignBtn").forEach(btn => {

    btn.addEventListener("click", function () {
        let qty = this.dataset.qty;
        let product = this.dataset.product;
        let id = this.dataset.id;

        document.getElementById("delivery_item_id").value = id;
        document.getElementById("assign_product").value = product;

        let container = document.getElementById("boxesContainer");
        container.innerHTML = "";

        fetch("../controllers/warehouse_controller.php?action=get_boxes&delivery_item_id=" + id)
            .then(res => res.json())
            .then(boxes => {



                let existing = boxes.length;

                /* show existing boxes */

                boxes.forEach(box => {

                    container.innerHTML += `
                <tr>
                

                    <td>
                        <input type="hidden" name="box_id[]" value="${box.box_id}">
                        <input type="number" step="0.01" name="weight[]" 
                        value="${box.box_weight}" class="form-control weight">
                    </td>

                    <td>
                        <input type="text" name="size[]" 
                        value="${box.box_size}" class="form-control size">
                    </td>

                    <td>
                        <input type="text" name="batch[]" 
                        value="${box.batch_code}" class="form-control">
                    </td>

                    <td>
                        <input type="text" name="pallet[]" 
                        value="${box.pallet_code}" class="form-control">
                    </td>

                    <td>
                        <input type="date" name="expiry[]" 
                        value="${box.expiry_date}" class="form-control">
                    </td>

                </tr>
                `;

                });

                /* add missing rows */

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
                        <input type="text" name="pallet[]" class="form-control">
                    </td>

                    <td>
                        <input type="date" name="expiry[]" class="form-control">
                    </td>

                </tr>
                `;

                }


                assignModal.show();

            });

    });

});


/* AUTO SIZE FROM WEIGHT */

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


/* SUBMIT FORM VIA AJAX */

document.getElementById("assignBoxesForm").addEventListener("submit", function (e) {

    e.preventDefault(); // stop page reload 


    let form = this;
    let formData = new FormData(form);

    fetch(form.action, {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {

            if (data.status === "success") {

                alert("Boxes saved successfully");

                location.reload();

            } else {

                alert(data.message);

            }

        })
        .catch(err => {
            console.error("Error:", err);
            alert("Something went wrong");

        });



});