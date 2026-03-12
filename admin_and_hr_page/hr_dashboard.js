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
        // Fallback: Show the first section
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
    exportBtn.addEventListener("click", () =>
      exportModal.classList.add("visible"),
    );
    confirmExportBtn.addEventListener("click", () => {
      const format = exportFormatSelect.value;
      if (format) {
        alert(`Exporting file as ${format}...`); // Placeholder
        exportModal.classList.remove("visible");
        exportFormatSelect.value = "";
      } else {
        alert("Please select a file format.");
      }
    });
    cancelExportBtn.addEventListener("click", () => {
      exportModal.classList.remove("visible");
      exportFormatSelect.value = "";
    });
  }
});
