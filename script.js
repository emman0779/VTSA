document.addEventListener("DOMContentLoaded", () => {
    const applyLinks = document.querySelectorAll(
      ".hero-apply-button, .job-apply-button"
    );
    const modal = document.getElementById("confirmation-modal");
    const confirmYesBtn = document.getElementById("confirm-yes");
    const confirmNoBtn = document.getElementById("confirm-no");
  
    // A variable to hold the URL we want to navigate to
    let targetUrl = null;
  
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
        // 3. Show the custom modal
        showModal();
      });
    });
  
    // When the "Yes" button is clicked...
    confirmYesBtn.addEventListener("click", () => {
      // 1. Hide the modal
      hideModal();
      // 2. Navigate to the stored URL
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });
  
    // When the "No" button is clicked...
    confirmNoBtn.addEventListener("click", () => {
      // Just hide the modal
      hideModal();
    });
  
    // Optional: Close the modal if the user clicks on the background overlay
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        hideModal();
      }
    });
  });
  
  