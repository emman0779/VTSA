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
});
