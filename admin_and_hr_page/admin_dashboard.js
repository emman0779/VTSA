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
    new Chart(bondPaperCtx, {
      type: "pie",
      data: {
        labels: ["Requested Bond Paper", "Remaining Bond Paper"],
        datasets: [
          {
            label: "Reams",
            data: [15, 285], // Mock data
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
    new Chart(otherSuppliesCtx, {
      type: "pie",
      data: {
        labels: ["Requested Other Items", "Remaining Other Items"],
        datasets: [
          {
            label: "Items",
            data: [27, 1173], // Mock data
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
});
