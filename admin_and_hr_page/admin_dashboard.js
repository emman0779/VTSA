document.addEventListener("DOMContentLoaded", function () {
  // --- Sidebar Toggle Logic ---
  const sidebarToggle = document.getElementById("sidebar-toggle");
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function () {
      document.body.classList.toggle("sidebar-collapsed");
    });
  }

  // --- Admin Dashboard Navigation ---
  const navLinks = document.querySelectorAll(".sidebar-nav .nav-link");
  const sections = document.querySelectorAll(".dashboard-section");

  // Function to handle section switching
  const showSection = (hash) => {
    if (sections.length === 0) return;

    // Default to #dashboard if hash is empty or invalid
    let sectionId = hash ? hash.substring(1) : "dashboard";
    let sectionFound = false;

    sections.forEach((section) => {
      if (section.id === sectionId) {
        section.classList.add("active-section");
        sectionFound = true;
      } else {
        section.classList.remove("active-section");
      }
    });

    // If no section matches, show the dashboard
    if (!sectionFound) {
      if (document.getElementById("dashboard")) {
        document.getElementById("dashboard").classList.add("active-section");
      } else if (sections.length > 0) {
        sections[0].classList.add("active-section");
        sectionId = sections[0].id;
      }
    }

    navLinks.forEach((link) => {
      if (link.getAttribute("href") === `#${sectionId}`) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  };

  // Handle clicks on nav links
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const hash = this.getAttribute("href");
      if (hash && hash.startsWith("#")) {
        e.preventDefault();
        window.location.hash = hash;
        showSection(hash);
      }
    });
  });

  // Listen for hash changes
  window.addEventListener("hashchange", function () {
    showSection(window.location.hash);
  });

  // Show initial section on page load
  showSection(window.location.hash);

  // --- Supplies Overview Charts ---
  const bondPaperCtx = document.getElementById("bondPaperChart");
  if (bondPaperCtx) {
    const bpData = window.supplyChartData
      ? window.supplyChartData.bondPaper
      : { requested: 15, remaining: 285 };

    new Chart(bondPaperCtx, {
      type: "pie",
      data: {
        labels: ["Requested Bond Paper", "Remaining Bond Paper"],
        datasets: [
          {
            label: "Reams",
            data: [bpData.requested, bpData.remaining],
            backgroundColor: [
              "rgba(255, 159, 64, 0.8)",
              "rgba(54, 162, 235, 0.8)",
            ],
            borderColor: ["#fff", "#fff"],
            borderWidth: 2,
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: true,
            text: "Bond Paper Usage (Reams)",
            font: { size: 16 },
          },
        },
      },
    });
  }

  const otherSuppliesCtx = document.getElementById("otherSuppliesChart");
  if (otherSuppliesCtx) {
    const osData = window.supplyChartData
      ? window.supplyChartData.otherSupplies
      : { requested: 27, remaining: 1173 };

    new Chart(otherSuppliesCtx, {
      type: "pie",
      data: {
        labels: ["Requested Other Items", "Remaining Other Items"],
        datasets: [
          {
            label: "Items",
            data: [osData.requested, osData.remaining],
            backgroundColor: [
              "rgba(255, 99, 132, 0.8)",
              "rgba(75, 192, 192, 0.8)",
            ],
            borderColor: ["#fff", "#fff"],
            borderWidth: 2,
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: true,
            text: "Other Supplies Usage (Items)",
            font: { size: 16 },
          },
        },
      },
    });
  }

  // --- Export Requests Modal Logic ---
  const exportRequestsBtn = document.getElementById("export-requests-btn");
  const exportRequestsModal = document.getElementById("export-requests-modal");
  const cancelExportRequestsBtn = document.getElementById(
    "cancel-export-requests",
  );

  if (exportRequestsBtn && exportRequestsModal) {
    exportRequestsBtn.addEventListener("click", function () {
      exportRequestsModal.classList.add("visible");
    });

    cancelExportRequestsBtn.addEventListener("click", function () {
      exportRequestsModal.classList.remove("visible");
    });

    exportRequestsModal.addEventListener("click", function (event) {
      if (event.target === exportRequestsModal) {
        exportRequestsModal.classList.remove("visible");
      }
    });
  }

  // --- Export Conference Modal Logic ---
  const exportConferenceBtn = document.getElementById("export-conference-btn");
  const exportConferenceModal = document.getElementById(
    "export-conference-modal",
  );
  const cancelExportConferenceBtn = document.getElementById(
    "cancel-export-conference",
  );

  if (exportConferenceBtn && exportConferenceModal) {
    exportConferenceBtn.addEventListener("click", function () {
      exportConferenceModal.classList.add("visible");
    });

    cancelExportConferenceBtn.addEventListener("click", function () {
      exportConferenceModal.classList.remove("visible");
    });

    exportConferenceModal.addEventListener("click", function (event) {
      if (event.target === exportConferenceModal) {
        exportConferenceModal.classList.remove("visible");
      }
    });
  }

  // --- Add Inventory Modal Logic ---
  const addInventoryBtn = document.getElementById("add-inventory-btn");
  const addInventoryModal = document.getElementById("add-inventory-modal");
  const closeInventoryModalBtn = document.getElementById(
    "close-inventory-modal",
  );
  const cancelInventoryBtn = document.getElementById("cancel-inventory");

  if (addInventoryBtn && addInventoryModal) {
    addInventoryBtn.addEventListener("click", function () {
      addInventoryModal.classList.add("visible");
    });

    const closeInventoryModal = () => {
      addInventoryModal.classList.remove("visible");
    };

    if (closeInventoryModalBtn)
      closeInventoryModalBtn.addEventListener("click", closeInventoryModal);
    if (cancelInventoryBtn)
      cancelInventoryBtn.addEventListener("click", closeInventoryModal);

    addInventoryModal.addEventListener("click", function (event) {
      if (event.target === addInventoryModal) {
        addInventoryModal.classList.remove("visible");
      }
    });
  }

  // --- Edit Inventory Modal Logic ---
  const editInventoryModal = document.getElementById("edit-inventory-modal");
  const closeEditInventoryModalBtn = document.getElementById(
    "close-edit-inventory-modal",
  );
  const cancelEditInventoryBtn = document.getElementById(
    "cancel-edit-inventory",
  );
  const inventorySection = document.getElementById("inventory");

  if (editInventoryModal && inventorySection) {
    // Use event delegation to catch clicks on edit buttons
    inventorySection.addEventListener("click", function (event) {
      const editBtn = event.target.closest(".edit-inventory-btn");
      if (editBtn) {
        // Get data from the button's data attributes
        const id = editBtn.dataset.id;
        const name = editBtn.dataset.name;
        const category = editBtn.dataset.category;
        const quantity = editBtn.dataset.quantity;
        const unit = editBtn.dataset.unit;

        // Populate the modal form
        document.getElementById("edit_item_id").value = id;
        document.getElementById("edit_item_name").value = name;
        document.getElementById("edit_category").value = category;
        document.getElementById("edit_stock_quantity").value = quantity;
        document.getElementById("edit_unit").value = unit;

        // Show the modal
        editInventoryModal.classList.add("visible");
      }
    });

    const closeEditModal = () => {
      editInventoryModal.classList.remove("visible");
    };

    if (closeEditInventoryModalBtn)
      closeEditInventoryModalBtn.addEventListener("click", closeEditModal);
    if (cancelEditInventoryBtn)
      cancelEditInventoryBtn.addEventListener("click", closeEditModal);

    editInventoryModal.addEventListener("click", function (event) {
      if (event.target === editInventoryModal) {
        closeEditModal();
      }
    });
  }
});
