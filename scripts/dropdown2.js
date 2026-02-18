document.addEventListener("DOMContentLoaded", function () {

  function setupDropdown(className) {
    var dropdowns = document.getElementsByClassName(className);

    for (var i = 0; i < dropdowns.length; i++) {
      dropdowns[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var dropdownContent = this.nextElementSibling;

        if (dropdownContent.style.display === "block") {
          dropdownContent.style.display = "none";
        } else {
          dropdownContent.style.display = "block";
        }
      });
    }
  }

  // Apply to all dropdown types
  setupDropdown("logistics-dropdown");
  setupDropdown("inventory-dropdown");
  setupDropdown("sales-dropdown");
  setupDropdown("settings-dropdown");

});
