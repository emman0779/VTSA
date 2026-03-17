window.addEventListener("scroll", function () {
  let navbar = document.getElementById("navbar");

  if (window.scrollY > 300) {
    navbar.style.top = "0";
  } else {
    navbar.style.top = "-100px";
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const applyLinks = document.querySelectorAll(".job-apply-button");
  const modal = document.getElementById("confirmation-modal");
  const confirmNoBtn = document.getElementById("confirm-no");
  const confirmYesBtn = document.getElementById("confirm-yes");
  const modalTitleEl = document.getElementById("modal-title");
  const modalMessageEl = document.getElementById("modal-message");

  // A variable to hold the URL we want to navigate to
  let targetUrl = null;
  let selectedPosition = null;

  const showModal = () => {
    modal.classList.add("visible");
  };

  const hideModal = () => {
    modal.classList.remove("visible");
  };

  // When an "Apply" link is clicked...
  applyLinks.forEach((link) => {
    link.addEventListener("click", function (event) {
      // 1. Prevent the default navigation
      event.preventDefault();
      // 2. Store the link's destination
      targetUrl = this.href;
      // 3. Get the position from the job card
      selectedPosition = this.closest(".jobCards")
        .querySelector("h4")
        .textContent.trim();

      // 4. Check Eligibility via AJAX
      fetch("check_application_eligibility.php")
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "blocked") {
            // Show modal with the blocking message
            if (modalTitleEl) modalTitleEl.textContent = "Application Notice";
            if (modalMessageEl)
              modalMessageEl.innerHTML =
                data.message ||
                "You cannot apply at this time due to an existing application or cooldown period.";
            if (confirmYesBtn) confirmYesBtn.style.display = "none";
            if (confirmNoBtn) confirmNoBtn.textContent = "Close";
            showModal();
          } else if (data.status === "allowed") {
            // Show confirmation modal
            if (modalTitleEl) modalTitleEl.textContent = "Confirm Application";
            if (modalMessageEl)
              modalMessageEl.innerHTML =
                "Do you want to apply for <strong>" +
                selectedPosition +
                "</strong>?";
            if (confirmYesBtn) confirmYesBtn.style.display = "inline-block";
            if (confirmNoBtn) confirmNoBtn.textContent = "Cancel";
            showModal();
          } else {
            // Login Required -> Proceed directly
            window.location.href = targetUrl;
          }
        })
        .catch((err) => {
          console.error(err);
          // Fallback: proceed if check fails (e.g. network error) to avoid blocking
          window.location.href = targetUrl;
        });
    });
  });

  // When the "Yes" button is clicked...
  if (confirmYesBtn) {
    confirmYesBtn.addEventListener("click", (e) => {
      e.preventDefault();
      hideModal();
      // Submit the application
      fetch("apply.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "position=" + encodeURIComponent(selectedPosition),
      })
        .then(() => {
          // Redirect to applied section
          window.location.href = targetUrl;
        })
        .catch((err) => {
          console.error(err);
          window.location.href = targetUrl;
        });
    });
  }

  // When the "Close" or "Cancel" button is clicked...
  if (confirmNoBtn) {
    confirmNoBtn.addEventListener("click", (e) => {
      e.preventDefault();
      hideModal();
    });
  }

  // Optional: Close the modal if the user clicks on the background overlay
  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      hideModal();
    }
  });
});
