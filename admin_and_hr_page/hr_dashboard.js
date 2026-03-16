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
      const defaultSection = document.getElementById("dashboard");
      if (defaultSection) {
        defaultSection.classList.add("active-section");
        sectionId = "dashboard"; // Update ID for nav highlighting
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
        alert("Exporting file as ".concat(format, "...")); // Placeholder for actual export logic
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

  // --- Employee Details Modal Logic ---
  const employeeModal = document.getElementById("employee-details-modal");
  const closeEmployeeModalBtn = document.getElementById("close-employee-modal");
  const employeeDetailsContent = document.getElementById(
    "employee-details-content",
  );
  const modalEmployeeName = document.getElementById("modal-employee-name");

  const viewEmployeeButtons = document.querySelectorAll("#employees .view-btn");

  if (employeeModal && viewEmployeeButtons.length > 0) {
    viewEmployeeButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const employeeId = this.dataset.id;
        const employee = window.employeesData[employeeId];

        if (employee) {
          // Set modal title
          modalEmployeeName.textContent = `Details for ${employee.name}`;

          // Build the table content
          const detailsHtml = `
            <table style="table-layout: fixed;">
                <tbody>
                    <tr><th style="width: 35%;">Full Name</th><td>${employee.name || "N/A"}</td></tr>
                    <tr><th>Employee ID</th><td>${employee.employee_id_number || "N/A"}</td></tr>
                    <tr><th>Position</th><td>${employee.position || "N/A"}</td></tr>
                    <tr><th>Personal Email</th><td>${employee.personal_email || "N/A"}</td></tr>
                    <tr><th>Work Email</th><td>${employee.work_email || "N/A"}</td></tr>
                    <tr><th>Personal Contact No.</th><td>${employee.personal_no || "N/A"}</td></tr>
                    <tr><th>Date of Birth</th><td>${
                      employee.date_of_birth
                        ? new Date(employee.date_of_birth).toLocaleDateString(
                            "en-US",
                            { year: "numeric", month: "long", day: "numeric" },
                          )
                        : "N/A"
                    }</td></tr>
                    <tr><th>Gender</th><td>${employee.gender || "N/A"}</td></tr>
                    <tr><th>Civil Status</th><td>${employee.civil_status || "N/A"}</td></tr>
                    <tr><th>Permanent Address</th><td>${employee.permanent_address || "N/A"}</td></tr>
                    <tr><th>Current Address</th><td>${employee.current_address || "N/A"}</td></tr>
                    <tr><th>Emergency Contact Person</th><td>${employee.contact_person || "N/A"}</td></tr>
                    <tr><th>Relationship</th><td>${employee.relationship || "N/A"}</td></tr>
                    <tr><th>Emergency Contact No.</th><td>${employee.contact_number || "N/A"}</td></tr>
                </tbody>
            </table>
        `;

          employeeDetailsContent.innerHTML = detailsHtml;
          employeeModal.classList.add("visible");
        }
      });
    });

    // Close modal functionality
    const closeEmployeeModal = () => {
      employeeModal.classList.remove("visible");
    };

    closeEmployeeModalBtn.addEventListener("click", closeEmployeeModal);
    employeeModal.addEventListener("click", (event) => {
      if (event.target === employeeModal) closeEmployeeModal();
    });
  }
});
