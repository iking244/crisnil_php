const deliveryModal =
    new bootstrap.Modal(document.getElementById("addStockModal"));

const loadBtn = document.getElementById("loadDRBtn");
const modeInput = document.getElementById("delivery_mode");
const form = document.getElementById("deliveryForm");
const title = document.querySelector("#addStockModal .modal-title");


// OPEN CREATE DELIVERY
document.getElementById("openCreateDelivery").addEventListener("click", () => {

    modeInput.value = "create";

    title.innerHTML =
        `<i class="fa fa-truck me-2 text-success"></i> Receive Delivery`;

    loadBtn.classList.add("d-none");

    form.reset();

    document.querySelector("#itemsTable tbody").innerHTML = "";

    deliveryModal.show();

});


// OPEN EDIT DELIVERY
document.getElementById("openEditDelivery").addEventListener("click", () => {

    modeInput.value = "edit";

    title.innerHTML =
        `<i class="fa fa-edit me-2 text-warning"></i> Edit Delivery`;

    loadBtn.classList.remove("d-none");

    form.reset();

    document.querySelector("#itemsTable tbody").innerHTML = "";

    deliveryModal.show();

});