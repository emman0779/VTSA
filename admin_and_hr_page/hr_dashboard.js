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
    // Use dynamic data if available, else fallback to static defaults
    const trendLabels = window.recruitmentTrendsData
      ? window.recruitmentTrendsData.labels
      : ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
    const trendApplicants = window.recruitmentTrendsData
      ? window.recruitmentTrendsData.applicants
      : [0, 0, 0, 0, 0, 0];
    const trendHired = window.recruitmentTrendsData
      ? window.recruitmentTrendsData.hired
      : [0, 0, 0, 0, 0, 0];

    const jobLabels = window.openJobsData
      ? window.openJobsData.labels
      : ["Job A", "Job B", "Job C"];
    const jobCounts = window.openJobsData
      ? window.openJobsData.counts
      : [5, 10, 3];

    // Chart 1: Applicants vs Hires Trend (Line Chart)
    new Chart(applicantCtx, {
      type: "line",
      data: {
        labels: trendLabels,
        datasets: [
          {
            label: "Total Applicants",
            data: trendApplicants,
            borderColor: "#203864",
            backgroundColor: "rgba(32, 56, 100, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Hired",
            data: trendHired,
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

    // Chart 2: Applicants per Open Position (Doughnut)
    // Generate a palette that repeats if there are many jobs
    const baseColors = [
      "#203864",
      "#ffc107",
      "#dc3545",
      "#17a2b8",
      "#6610f2",
      "#fd7e14",
      "#28a745",
      "#6f42c1",
    ];
    const bgColors = jobLabels.map(
      (_, index) => baseColors[index % baseColors.length],
    );

    new Chart(jobCtx, {
      type: "doughnut",
      data: {
        labels: jobLabels,
        datasets: [
          {
            label: "Applicants",
            data: jobCounts,
            backgroundColor: bgColors,
            borderColor: "#ffffff",
            borderWidth: 2,
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: "right", labels: { boxWidth: 15 } },
          title: { display: true, text: "Applicants per Open Position" },
        },
      },
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

  // --- Export Applicants Modal Logic ---
  const exportApplicantsBtn = document.getElementById("export-applicants-btn");
  const exportApplicantsModal = document.getElementById(
    "export-applicants-modal",
  );
  const cancelExportApplicantsBtn = document.getElementById(
    "cancel-export-applicants",
  );

  if (exportApplicantsBtn && exportApplicantsModal) {
    exportApplicantsBtn.addEventListener("click", function () {
      exportApplicantsModal.classList.add("visible");
    });

    cancelExportApplicantsBtn.addEventListener("click", function () {
      exportApplicantsModal.classList.remove("visible");
    });

    exportApplicantsModal.addEventListener("click", function (event) {
      if (event.target === exportApplicantsModal) {
        exportApplicantsModal.classList.remove("visible");
      }
    });
  }

  // --- Export Employees Modal Logic ---
  const exportEmployeesBtn = document.getElementById("export-employees-btn");
  const exportEmployeesModal = document.getElementById(
    "export-employees-modal",
  );
  const cancelExportEmployeesBtn = document.getElementById(
    "cancel-export-employees",
  );

  if (exportEmployeesBtn && exportEmployeesModal) {
    exportEmployeesBtn.addEventListener("click", function () {
      exportEmployeesModal.classList.add("visible");
    });

    cancelExportEmployeesBtn.addEventListener("click", function () {
      exportEmployeesModal.classList.remove("visible");
    });

    exportEmployeesModal.addEventListener("click", function (event) {
      if (event.target === exportEmployeesModal) {
        exportEmployeesModal.classList.remove("visible");
      }
    });
  }
});
