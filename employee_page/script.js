document.addEventListener('DOMContentLoaded', function() {
    // --- Sidebar Toggle Logic ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    // --- Admin Dashboard Navigation ---
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const sections = document.querySelectorAll('.dashboard-section');

    // Function to handle section switching
    const showSection = (hash) => {
        // Default to #dashboard if hash is empty or invalid
        let sectionId = hash ? hash.substring(1) : 'dashboard';
        let sectionFound = false;

        sections.forEach(section => {
            if (section.id === sectionId) {
                section.classList.add('active-section');
                sectionFound = true;
            } else {
                section.classList.remove('active-section');
            }
        });

        // If no section matches, show the dashboard
        if (!sectionFound) {
            if (document.getElementById('dashboard')) {
                document.getElementById('dashboard').classList.add('active-section');
            } else if (sections.length > 0) {
                // Fallback: Show the first section (e.g. for employee page with #profile)
                sections[0].classList.add('active-section');
                sectionId = sections[0].id; // Update ID so nav highlights correctly
            }
        }

        navLinks.forEach(link => {
            if (link.getAttribute('href') === `#${sectionId}`) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    };

    // Handle clicks on nav links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const hash = this.getAttribute('href');
            // Update URL hash for bookmarking/history
            window.location.hash = hash;
            showSection(hash);
        });
    });

    // Listen for hash changes (activates navigation from non-sidebar buttons)
    window.addEventListener('hashchange', function() {
        showSection(window.location.hash);
    });

    // Show initial section based on URL hash on page load
    showSection(window.location.hash);

    // --- Admin Dashboard Charts ---
    const applicantCtx = document.getElementById('applicantChart');
    const jobCtx = document.getElementById('jobChart');

    // Only initialize if the elements exist (to avoid errors on other pages)
    if (applicantCtx && jobCtx) {
        // Chart 1: Applicants vs Hires Trend (Line Chart)
        new Chart(applicantCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Total Applicants',
                        data: [45, 59, 80, 81, 105, 125],
                        borderColor: '#203864',
                        backgroundColor: 'rgba(32, 56, 100, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Hired',
                        data: [5, 12, 15, 20, 25, 29], // Cumulative hires
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Recruitment Trends (6 Months)' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Chart 2: Job Openings Distribution (Doughnut)
        new Chart(jobCtx, {
            type: 'doughnut',
            data: {
                labels: ['Technician', 'Admin Support', 'Sales', 'Engineering'],
                datasets: [{
                    data: [2, 1, 1, 2], // Adds up to 6 open positions
                    backgroundColor: ['#203864', '#ffc107', '#dc3545', '#17a2b8'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' },
                    title: { display: true, text: 'Open Positions by Department' }
                }
            }
        });
    }

    // --- Employee Form Logic (Address) ---
    const radioYes = document.getElementById('is_permanent_yes');
    const radioNo = document.getElementById('is_permanent_no');
    const currentAddressGroup = document.getElementById('current_address_group');

    // Check if these elements exist on the page to avoid errors on other pages
    if (radioYes && radioNo && currentAddressGroup) {
        const handleAddressRadioChange = () => {
            if (radioNo.checked) {
                currentAddressGroup.style.display = 'block';
            } else {
                currentAddressGroup.style.display = 'none';
            }
        };
        
        radioYes.addEventListener('change', handleAddressRadioChange);
        radioNo.addEventListener('change', handleAddressRadioChange);
    }

    // --- Registration Page Logic (Position Popup) ---
    const isEmployeeCheckbox = document.getElementById('is-employee');
    const positionModal = document.getElementById('position-modal');
    const confirmPosBtn = document.getElementById('confirm-position');
    const cancelPosBtn = document.getElementById('cancel-position');
    const positionSelect = document.getElementById('position-select');
    const positionGroup = document.getElementById('position-group');
    const employeePositionInput = document.getElementById('employee-position');
    const employeeIdGroup = document.getElementById('employee-id-group');

    if (isEmployeeCheckbox && positionModal) {
        // Open modal when checkbox is checked
        isEmployeeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                positionModal.classList.add('visible');
            } else {
                // Hide fields if unchecked
                positionGroup.style.display = 'none';
                employeeIdGroup.style.display = 'none';
                employeePositionInput.value = '';
            }
        });

        // Handle Confirm
        confirmPosBtn.addEventListener('click', function() {
            const selectedValue = positionSelect.value;
            if (selectedValue) {
                employeePositionInput.value = selectedValue;
                positionGroup.style.display = 'block';
                employeeIdGroup.style.display = 'block';
                positionModal.classList.remove('visible');
            } else {
                alert("Please select a position.");
            }
        });

        // Handle Cancel
        cancelPosBtn.addEventListener('click', function() {
            positionModal.classList.remove('visible');
            isEmployeeCheckbox.checked = false; // Uncheck since they cancelled
        });
    }
});