const isEmployeeCheckbox = document.getElementById('is-employee');
if (isEmployeeCheckbox) {
    isEmployeeCheckbox.addEventListener('change', function() {
        const employeeIdGroup = document.getElementById('employee-id-group');
        const employeeIdInput = document.getElementById('employee-id');
        employeeIdGroup.style.display = this.checked ? 'block' : 'none';
        employeeIdInput.required = this.checked;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // --- Sidebar Toggle Logic ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    // --- Logic for Profile Picture Preview ---
    const profilePictureInput = document.getElementById('profile_picture');
    const profileImgPreview = document.getElementById('profile_img_preview');

    if (profilePictureInput && profileImgPreview) {
        profilePictureInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImgPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                profileImgPreview.src = "https://via.placeholder.com/150/cccccc/ffffff?text=Upload+Image"; // Default placeholder
            }
        });
    }

    // --- Logic for conditional current address field ---
    const addressRadios = document.querySelectorAll('input[name="is_address_permanent"]');
    const currentAddressGroup = document.getElementById('current_address_group');

    if (addressRadios.length > 0 && currentAddressGroup) {
        addressRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (document.getElementById('is_permanent_no').checked) {
                    currentAddressGroup.style.display = 'block';
                } else {
                    currentAddressGroup.style.display = 'none';
                }
            });
        });
    }
});
