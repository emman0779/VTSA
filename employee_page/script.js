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
  // Guard against missing Chart.js (e.g., when this script is shared across pages)
  if (typeof Chart !== "undefined") {
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
  } else {
    console.warn(
      "Chart.js is not loaded; dashboard charts will not be rendered.",
    );
  }

  // --- Supplies Overview Chart ---
  if (typeof Chart !== "undefined") {
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

  // --- Request History Modal ---
  const viewRequestsBtn = document.getElementById("view-requests-btn");
  const historyModal = document.getElementById("request-history-modal");
  const closeHistoryModalBtn = document.getElementById("close-history-modal");
  const historyContent = document.getElementById("request-history-content");

  if (viewRequestsBtn && historyModal && historyContent) {
    viewRequestsBtn.addEventListener("click", () => {
      let tableHtml = `
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

      if (
        typeof window.requestHistory !== "undefined" &&
        window.requestHistory &&
        window.requestHistory.length > 0
      ) {
        window.requestHistory.forEach((req) => {
          const reqDate = new Date(req.date_time_requested).toLocaleString(
            "en-US",
            {
              year: "numeric",
              month: "short",
              day: "numeric",
              hour: "numeric",
              minute: "2-digit",
            },
          );
          const statusClass =
            (req.status || "").toLowerCase() === "pending"
              ? "status-pending"
              : "status-hired"; // Using hired for approved
          tableHtml += `
                        <tr>
                            <td>${reqDate}</td>
                            <td>${req.item}</td>
                            <td>${req.quantity}</td>
                            <td><span class="status-pill ${statusClass}">${req.status}</span></td>
                        </tr>
                    `;
        });
      } else {
        tableHtml += `<tr><td colspan="4" style="text-align: center;">You have no past requests.</td></tr>`;
      }

      tableHtml += `</tbody></table>`;
      historyContent.innerHTML = tableHtml;
      historyModal.classList.add("visible");
    });

    const closeHistoryModal = () => {
      historyModal.classList.remove("visible");
    };
    if (closeHistoryModalBtn) {
      closeHistoryModalBtn.addEventListener("click", closeHistoryModal);
    }
    historyModal.addEventListener("click", (e) => {
      if (e.target === historyModal) closeHistoryModal();
    });
  }

  // --- Conference Schedule Modal ---
  const viewScheduleBtn = document.getElementById("view-schedule-btn");
  const scheduleModal = document.getElementById("schedule-modal");
  const closeScheduleModalBtn = document.getElementById("close-schedule-modal");
  let scheduleContent = document.getElementById("schedule-history-content");

  console.log("Schedule modal elements:", {
    viewScheduleBtn,
    scheduleModal,
    closeScheduleModalBtn,
    scheduleContent,
  });

  const ensureScheduleContent = () => {
    if (!scheduleContent && scheduleModal) {
      scheduleContent = scheduleModal.querySelector(
        "#schedule-history-content",
      );
    }
    return scheduleContent;
  };

  const buildScheduleTableHtml = () => {
    let tableHtml = `
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Department</th>
                            <th>Participants</th>
                            <th>Booked By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

    const scheduleData = Array.isArray(window.conferenceSchedule)
      ? window.conferenceSchedule
      : [];

    console.log("Schedule data:", scheduleData);

    if (scheduleData.length > 0) {
      scheduleData.forEach((booking) => {
        let date = "N/A";
        if (booking.booking_date) {
          const dateObj = new Date(booking.booking_date);
          if (!isNaN(dateObj.getTime())) {
            date = dateObj.toLocaleDateString("en-US", {
              year: "numeric",
              month: "short",
              day: "numeric",
            });
          }
        }

        const start = booking.start_time
          ? String(booking.start_time).slice(0, 5)
          : "";
        const end = booking.end_time
          ? String(booking.end_time).slice(0, 5)
          : "";
        const status = booking.status || "Pending";
        const statusClass =
          status.toLowerCase() === "pending"
            ? "status-pending"
            : "status-hired";

        tableHtml += `
                        <tr>
                            <td>${date}</td>
                            <td>${start} - ${end}</td>
                            <td>${booking.department}</td>
                            <td>${booking.participants || "-"}</td>
                            <td>${booking.employee_name}</td>
                            <td><span class="status-pill ${statusClass}">${status}</span></td>
                        </tr>
                    `;
      });
    } else {
      tableHtml += `
                        <tr>
                            <td colspan="6" style="text-align: center;">No schedule entries found.</td>
                        </tr>
                    `;
    }

    tableHtml += `</tbody></table>`;
    return tableHtml;
  };

  // Expose a global helper so that inline handlers can invoke the same logic
  window.showScheduleModal = () => {
    console.log("showScheduleModal called");
    const contentEl = ensureScheduleContent();
    if (!contentEl) {
      console.warn("Schedule modal content container not found.");
      return;
    }

    try {
      contentEl.innerHTML = buildScheduleTableHtml();
    } catch (error) {
      console.error("Error building schedule table:", error);
      contentEl.innerHTML =
        "<div style='padding: 1rem; text-align:center;'>Unable to load schedule. Please refresh the page.</div>";
    }

    if (scheduleModal) {
      scheduleModal.classList.add("visible");
    }
  };

  if (viewScheduleBtn && scheduleModal) {
    console.log("Attaching click handler to viewScheduleBtn");
    viewScheduleBtn.addEventListener("click", window.showScheduleModal);

    const closeScheduleModal = () => {
      scheduleModal.classList.remove("visible");
    };

    if (closeScheduleModalBtn) {
      closeScheduleModalBtn.addEventListener("click", closeScheduleModal);
    }

    scheduleModal.addEventListener("click", (e) => {
      if (e.target === scheduleModal) closeScheduleModal();
    });
  } else {
    console.warn("viewScheduleBtn or scheduleModal not found");
  }
});
