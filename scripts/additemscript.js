// Get modal elements
const modal = document.getElementById('productCodeModal');
const addItemBtn = document.getElementById('additem');
const closeModalBtn = document.getElementById('closeModalBtn');
const submitProductCodeBtn = document.getElementById('submitProductCodeBtn');
const productCodeInput = document.getElementById('productCodeInput');

const modal2 = document.getElementById('productCodeModal2');
const productCodeInput2 = document.getElementById('productCodeInput2');
const closeModalBtn2 = document.getElementById('closeModalBtn2');
const submitProductCodeBtn2 = document.getElementById('submitProductCodeBtn2');

// Function to show the modal
function openModal() {
  modal.style.display = "block";
}

function closeModal2() {
  modal.style.display = "none";
  modal2.style.display = "block";
}

// Function to close the modal
function closeModal3() {
  modal.style.display = "none";
  modal2.style.display = "none";
}

function closeModal() {
  modal.style.display = "none";
}

// Event listener for the 'Add Item' button
addItemBtn.addEventListener('click', openModal);

// Event listener for the 'Close' button (x)
closeModalBtn.addEventListener('click', closeModal);
closeModalBtn2.addEventListener('click', closeModal3);

// Event listener for the 'Submit' button
submitProductCodeBtn.addEventListener('click', function() {
  const productCode = productCodeInput.value;
  if (productCode) {
    alert(`Product Code: ${productCode} added to transaction!`);
    closeModal2();
  } else {
    alert("Please enter a product code.");
  }
});

// Close the modal if the user clicks anywhere outside of it
window.onclick = function(event) {
  if (event.target === modal || event.target === modal2) {
    closeModal();
  }
}
