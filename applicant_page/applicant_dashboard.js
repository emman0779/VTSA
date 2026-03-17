document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".sidebar-nav .nav-link");
  const sections = document.querySelectorAll(".dashboard-section");

  const showSection = (hash) => {
    // Determine the target section ID. Default to 'applied'.
    let targetId = hash ? hash.substring(1) : "applied";
    let sectionFound = false;

    // Loop through sections to show the target and hide others.
    sections.forEach((section) => {
      if (section.id === targetId) {
        section.classList.add("active");
        sectionFound = true;
      } else {
        section.classList.remove("active");
      }
    });

    // If the hash is invalid or doesn't match any section, show the default.
    if (!sectionFound) {
      const defaultSection = document.getElementById("applied");
      if (defaultSection) {
        defaultSection.classList.add("active");
        targetId = "applied"; // Correct the targetId for nav highlighting
      }
    }

    // Update the active state for navigation links.
    navLinks.forEach((link) => {
      if (link.getAttribute("href") === `#${targetId}`) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  };

  // Handle clicks on navigation links.
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const hash = this.getAttribute("href");
      if (hash.startsWith("#")) {
        e.preventDefault();
        window.location.hash = hash;
        // The 'hashchange' event will handle showing the section.
      }
    });
  });

  // Listen for hash changes to support back/forward navigation.
  window.addEventListener("hashchange", () => {
    showSection(window.location.hash);
  });

  // Show the initial section on page load based on the URL hash.
  showSection(window.location.hash);

  // --- Update Info Modal Logic ---
  const updateModal = document.getElementById("update-modal");
  const openModalBtn = document.getElementById("open-update-modal");
  const closeModalBtn = document.getElementById("close-update-modal");
  const cancelUpdateBtn = document.getElementById("cancel-update");

  if (updateModal && openModalBtn) {
    // Ensure modal is hidden on load
    updateModal.classList.remove("visible");

    const closeModal = () => {
      updateModal.classList.remove("visible");
    };

    openModalBtn.addEventListener("click", (e) => {
      e.preventDefault();
      updateModal.classList.add("visible");
    });

    if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
    if (cancelUpdateBtn) cancelUpdateBtn.addEventListener("click", closeModal);

    // Close on click outside
    updateModal.addEventListener("click", (e) => {
      if (e.target === updateModal) closeModal();
    });
  }

  // --- Add Skill Modal Logic ---
  const skillModal = document.getElementById("skill-modal");
  const openSkillBtn = document.getElementById("open-skill-modal");
  const closeSkillBtn = document.getElementById("close-skill-modal");

  if (skillModal && openSkillBtn) {
    openSkillBtn.addEventListener("click", (e) => {
      e.preventDefault();
      skillModal.classList.add("visible");
    });

    if (closeSkillBtn) {
      closeSkillBtn.addEventListener("click", () => {
        skillModal.classList.remove("visible");
      });
    }

    skillModal.addEventListener("click", (e) => {
      if (e.target === skillModal) skillModal.classList.remove("visible");
    });
  }

  // --- Add Education Modal Logic ---
  const eduModal = document.getElementById("education-modal");
  const openEduBtn = document.getElementById("open-education-modal");
  const closeEduBtn = document.getElementById("close-education-modal");

  if (eduModal && openEduBtn) {
    openEduBtn.addEventListener("click", (e) => {
      e.preventDefault();
      eduModal.classList.add("visible");
    });

    if (closeEduBtn) {
      closeEduBtn.addEventListener("click", () => {
        eduModal.classList.remove("visible");
      });
    }
    eduModal.addEventListener("click", (e) => {
      if (e.target === eduModal) eduModal.classList.remove("visible");
    });
  }

  // --- Add Work Exp Modal Logic ---
  const workModal = document.getElementById("work-modal");
  const openWorkBtn = document.getElementById("open-work-modal");
  const closeWorkBtn = document.getElementById("close-work-modal");

  if (workModal && openWorkBtn) {
    openWorkBtn.addEventListener("click", (e) => {
      e.preventDefault();
      workModal.classList.add("visible");
    });

    if (closeWorkBtn) {
      closeWorkBtn.addEventListener("click", () => {
        workModal.classList.remove("visible");
      });
    }
    workModal.addEventListener("click", (e) => {
      if (e.target === workModal) workModal.classList.remove("visible");
    });
  }

  // --- Resume Upload Logic ---
  const resumeInput = document.getElementById("resume-upload");
  const resumeForm = document.getElementById("resume-upload-form");

  if (resumeInput && resumeForm) {
    resumeInput.addEventListener("change", () => {
      if (resumeInput.files.length > 0) {
        // The form will be submitted automatically when a file is chosen.
        resumeForm.submit();
      }
    });
  }

  // --- Profile Picture Upload Logic ---
  const pfpInput = document.getElementById("profile-pic-upload");
  const pfpForm = document.getElementById("pfp-upload-form");

  if (pfpInput && pfpForm) {
    pfpInput.addEventListener("change", () => {
      if (pfpInput.files.length > 0) {
        // Automatically submit the form when a file is chosen.
        pfpForm.submit();
      }
    });
  }
});
