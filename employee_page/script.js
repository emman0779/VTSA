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
        // Fallback: Show the first section (e.g. for employee page with #profile)
        sections[0].classList.add("active-section");
        sectionId = sections[0].id; // Update ID so nav highlights correctly
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

  // Listen for hash changes (activates navigation from non-sidebar buttons)
  window.addEventListener("hashchange", function () {
    showSection(window.location.hash);
  });

  // Show initial section based on URL hash on page load
  showSection(window.location.hash);

  // --- Admin Dashboard Charts ---
  const applicantCtx = document.getElementById("applicantChart");
  const jobCtx = document.getElementById("jobChart");

  // Only initialize if the elements exist (to avoid errors on other pages)
  if (applicantCtx && jobCtx) {
    // Chart 1: Applicants vs Hires Trend (Line Chart)
    new Chart(applicantCtx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
          {
            label: "Total Applicants",
            data: [45, 59, 80, 81, 105, 125],
            borderColor: "#203864",
            backgroundColor: "rgba(32, 56, 100, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Hired",
            data: [5, 12, 15, 20, 25, 29], // Cumulative hires
            borderColor: "#28a745",
            backgroundColor: "rgba(40, 167, 69, 0.1)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "top" },
          title: { display: true, text: "Recruitment Trends (6 Months)" },
        },
        scales: {
          y: { beginAtZero: true },
        },
      },
    });

    // Chart 2: Job Openings Distribution (Doughnut)
    new Chart(jobCtx, {
      type: "doughnut",
      data: {
        labels: ["Technician", "Admin Support", "Sales", "Engineering"],
        datasets: [
          {
            data: [2, 1, 1, 2], // Adds up to 6 open positions
            backgroundColor: ["#203864", "#ffc107", "#dc3545", "#17a2b8"],
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "right" },
          title: { display: true, text: "Open Positions by Department" },
        },
      },
    });
  }

  // --- Supplies Overview Chart ---
  const suppliesCtx = document.getElementById("suppliesChart");
  if (suppliesCtx) {
    new Chart(suppliesCtx, {
      type: "pie",
      data: {
        labels: ["Requested Supplies (Month)", "Remaining Supplies"],
        datasets: [
          {
            label: "Count",
            data: [42, 1458], // Mock data: 42 requested, 1458 remaining
            backgroundColor: [
              "rgba(255, 193, 7, 0.8)", // Warning Color for requests
              "rgba(40, 167, 69, 0.8)", // Success Color for remaining
            ],
            borderColor: ["rgba(255, 255, 255, 1)", "rgba(255, 255, 255, 1)"],
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
            text: "Monthly Supply Usage Overview",
            font: { size: 16 },
          },
        },
      },
    });
  }

  // --- Employee Form Logic (Address) ---
  const radioYes = document.getElementById("is_permanent_yes");
  const radioNo = document.getElementById("is_permanent_no");
  const currentAddressGroup = document.getElementById("current_address_group");

  // Check if these elements exist on the page to avoid errors on other pages
  if (radioYes && radioNo && currentAddressGroup) {
    const handleAddressRadioChange = () => {
      if (radioNo.checked) {
        currentAddressGroup.style.display = "block";
      } else {
        currentAddressGroup.style.display = "none";
      }
    };

    radioYes.addEventListener("change", handleAddressRadioChange);
    radioNo.addEventListener("change", handleAddressRadioChange);
  }

  // --- Admin Export Modal Logic ---
  const exportBtn = document.getElementById("export-btn");
  const exportModal = document.getElementById("export-modal");
  const confirmExportBtn = document.getElementById("confirm-export");
  const cancelExportBtn = document.getElementById("cancel-export");
  const exportFormatSelect = document.getElementById("export-format");

  if (exportBtn && exportModal) {
    exportBtn.addEventListener("click", function () {
      exportModal.classList.add("visible");
    });

    // Handle Confirm
    confirmExportBtn.addEventListener("click", function () {
      const format = exportFormatSelect.value;
      if (format) {
        alert("Exporting file as " + format + "..."); // Placeholder for actual export logic
        exportModal.classList.remove("visible");
        exportFormatSelect.value = ""; // Reset selection
      } else {
        alert("Please select a file format.");
      }
    });

    // Handle Cancel
    cancelExportBtn.addEventListener("click", function () {
      exportModal.classList.remove("visible");
      exportFormatSelect.value = ""; // Reset selection
    });
  }

  // --- Request Page Form Toggle ---
  const requestTypeSelect = document.getElementById("request_type_select");
  const bondPaperForm = document.getElementById("bond_paper_form_container");
  const otherSuppliesForm = document.getElementById(
    "other_supplies_form_container",
  );

  if (requestTypeSelect && bondPaperForm && otherSuppliesForm) {
    requestTypeSelect.addEventListener("change", function () {
      if (this.value === "bond_paper") {
        bondPaperForm.style.display = "block";
        otherSuppliesForm.style.display = "none";
      } else if (this.value === "other_supplies") {
        bondPaperForm.style.display = "none";
        otherSuppliesForm.style.display = "block";
      }
    });
  }

  // --- Employee Update Modal Logic ---
  const openUpdateModalBtn = document.getElementById("open-update-modal");
  const updateModal = document.getElementById("update-modal");
  const closeUpdateModalBtn = document.getElementById("close-update-modal");
  const cancelUpdateBtn = document.getElementById("cancel-update");

  if (openUpdateModalBtn && updateModal) {
    openUpdateModalBtn.addEventListener("click", function () {
      updateModal.classList.add("visible");
    });
    const closeModal = () => {
      updateModal.classList.remove("visible");
    };

    if (closeUpdateModalBtn)
      closeUpdateModalBtn.addEventListener("click", closeModal);
    if (cancelUpdateBtn) cancelUpdateBtn.addEventListener("click", closeModal);
  }
});
