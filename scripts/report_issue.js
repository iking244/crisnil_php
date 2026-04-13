document.querySelectorAll(".reportIssueBtn").forEach(btn => {

btn.addEventListener("click", function(){

let deliveryItemId = this.dataset.id;
let productName = this.dataset.product;

document.getElementById("issue_delivery_item_id").value = deliveryItemId;
document.getElementById("issue_product_name").value = productName;

let modal = new bootstrap.Modal(document.getElementById("reportIssueModal"));
modal.show();

});

});

document.getElementById("submitIssueBtn").addEventListener("click", function(){

let form = document.getElementById("reportIssueForm");
let formData = new FormData(form);

fetch("../controllers/warehouse_controller.php?action=report_issue", {

method: "POST",
body: formData

})
.then(res => res.json())
.then(data => {

if(data.status === "success"){

location.reload();

}else{

alert(data.message);

}

});

});