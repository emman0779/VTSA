const isEmployeeCheckbox = document.getElementById("is-employee");
const positionModal = document.getElementById("position-modal");
const confirmPosBtn = document.getElementById("confirm-position");
const cancelPosBtn = document.getElementById("cancel-position");
const positionSelect = document.getElementById("position-select");
const positionGroup = document.getElementById("position-group");
const employeePositionInput = document.getElementById("employee-position");
const employeeIdGroup = document.getElementById("employee-id-group");

if (isEmployeeCheckbox && positionModal) {
  // Open modal when checkbox is checked
  isEmployeeCheckbox.addEventListener("change", function () {
    if (this.checked) {
      positionModal.classList.add("visible");
    } else {
      // Hide fields if unchecked
      positionGroup.style.display = "none";
      employeeIdGroup.style.display = "none";
      employeePositionInput.value = "";
    }
  });

  // Handle Confirm
  confirmPosBtn.addEventListener("click", function () {
    const selectedValue = positionSelect.value;
    if (selectedValue) {
      employeePositionInput.value = selectedValue;
      positionGroup.style.display = "block";
      employeeIdGroup.style.display = "block";
      positionModal.classList.remove("visible");
    } else {
      alert("Please select a position.");
    }
  });

  // Handle Cancel
  cancelPosBtn.addEventListener("click", function () {
    positionModal.classList.remove("visible");
    isEmployeeCheckbox.checked = false; // Uncheck since they cancelled
  });
}