<?php
session_start();

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    header("Location: ../index.html");
    exit();
}

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee data
$employee_id = $_SESSION['user_id'];
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

// --- Fetch Request History ---
$requests = [];
// Get bond paper requests
$sql_bpaper = "SELECT CONCAT(paper_size, ' Bond Paper') as item, quantity, status, date_time_requested FROM request_bpaper WHERE employee_id = ?";
$stmt_bpaper = $conn->prepare($sql_bpaper);
$stmt_bpaper->bind_param("i", $employee_id);
$stmt_bpaper->execute();
$result_bpaper = $stmt_bpaper->get_result();
while ($row = $result_bpaper->fetch_assoc()) {
    $requests[] = $row;
}
$stmt_bpaper->close();

// Get other supply requests
$sql_supplies = "SELECT item_name as item, quantity, status, date_time_requested FROM request_supplies WHERE employee_id = ?";
$stmt_supplies = $conn->prepare($sql_supplies);
$stmt_supplies->bind_param("i", $employee_id);
$stmt_supplies->execute();
$result_supplies = $stmt_supplies->get_result();
while ($row = $result_supplies->fetch_assoc()) {
    $requests[] = $row;
}
$stmt_supplies->close();

// Sort requests by date, descending
usort($requests, fn($a, $b) => strtotime($b['date_time_requested']) <=> strtotime($a['date_time_requested']));

// Determine if there is an active pending request (supplies)
$hasPendingSupplies = false;
foreach ($requests as $req) {
    if (isset($req['status']) && strtolower($req['status']) === 'pending') {
        $hasPendingSupplies = true;
        break;
    }
}

// Determine if the current employee already has a pending conference booking
$pendingConferenceStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM conference_bookings WHERE employee_id = ? AND status = 'Pending'");
$pendingConferenceStmt->bind_param("i", $employee_id);
$pendingConferenceStmt->execute();
$pendingConferenceResult = $pendingConferenceStmt->get_result();
$pendingConferenceCount = $pendingConferenceResult->fetch_assoc()['cnt'] ?? 0;
$pendingConferenceStmt->close();

$hasPendingConference = ($pendingConferenceCount > 0);

// Fetch Conference Schedule for the modal
$conferenceBookings = [];
$sql_conf = "SELECT cb.*, e.name AS employee_name FROM conference_bookings cb JOIN employees e ON cb.employee_id = e.id ORDER BY cb.booking_date ASC, cb.start_time ASC";
$result_conf = $conn->query($sql_conf);
if ($result_conf) {
    while ($row = $result_conf->fetch_assoc()) {
        $conferenceBookings[] = $row;
    }
}

$conn->close();
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
  </head>
  <body class="dashboard-page">
    <aside class="sidebar">
      <div class="sidebar-header">
        <a href="#home" class="logo">
          <img src="../applicant_page/images/vtsa_white.png" alt="VTSA Logo" />
        </a>
        <div class="sidebar-title">Employee Panel</div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <a href="#profile" class="nav-link active"
              ><i class="fas fa-user-edit"></i>
              <span>Employee Information</span></a
            >
          </li>
          <li>
            <a href="#request" class="nav-link"
              ><i class="fas fa-box-open"></i> <span>Request Supplies</span></a
            >
          </li>
          <li>
            <a href="#conference" class="nav-link"
              ><i class="fas fa-calendar-alt"></i>
              <span>Conference Schedule</span></a
            >
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <ul>
          <li>
            <a href="logout.php"
              ><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a
            >
          </li>
        </ul>
      </div>
    </aside>
    <div class="main-container">
      <header class="top-bar">
        <button id="sidebar-toggle" class="sidebar-toggle-btn">
          <i class="fas fa-bars"></i>
        </button>
        <div class="top-bar-title">Employee Dashboard</div>
        <img
          src="../applicant_page/images/logo2.png"
          alt="Logo"
          class="header-logo"
        />
      </header>
      <main class="main-content">
        <!-- Profile/Information Update Section -->
        <section id="profile" class="dashboard-section">
          <div class="section-header">
            <h1>Employee Personnel Record</h1>
            <button id="open-update-modal" class="btn btn-primary">
              <i class="fas fa-edit"></i> Update Information
            </button>
          </div>

          <div class="profile-info-wrapper" style="margin-top: 20px">
            <div
              class="row-flex"
              style="align-items: flex-start; margin-bottom: 20px"
            >
              <div style="flex: 1; padding-right: 20px">
                <h3
                  style="
                    color: var(--primary-color);
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                  "
                >
                  Personal Information
                </h3>
                <p style="margin-bottom: 8px">
                  <strong>Full Name:</strong> <?php echo htmlspecialchars($employee['name'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Civil Status:</strong> <?php echo htmlspecialchars($employee['civil_status'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px"><strong>Gender:</strong> <?php echo htmlspecialchars($employee['gender'] ?? 'N/A'); ?></p>
                <p style="margin-bottom: 8px">
                  <strong>Date of Birth:</strong> <?php echo htmlspecialchars($employee['date_of_birth'] ? date('F j, Y', strtotime($employee['date_of_birth'])) : 'N/A'); ?>
                </p>
              </div>
              <div style="flex: 1">
                <h3
                  style="
                    color: var(--primary-color);
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                  "
                >
                  Contact Information
                </h3>
                <p style="margin-bottom: 8px">
                  <strong>Personal No.:</strong> <?php echo htmlspecialchars($employee['personal_no'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Work Email:</strong> <?php echo htmlspecialchars($employee['work_email'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Personal Email:</strong> <?php echo htmlspecialchars($employee['personal_email'] ?? 'N/A'); ?>
                </p>
              </div>
            </div>

            <div
              class="row-flex"
              style="align-items: flex-start; margin-bottom: 20px"
            >
              <div style="flex: 1; padding-right: 20px">
                <h3
                  style="
                    color: var(--primary-color);
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                  "
                >
                  Address
                </h3>
                <p style="margin-bottom: 8px">
                  <strong>Permanent Address:</strong> <?php echo htmlspecialchars($employee['permanent_address'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Current Address:</strong> <?php echo htmlspecialchars($employee['current_address'] ?? 'N/A'); ?>
                </p>
              </div>
              <div style="flex: 1">
                <h3
                  style="
                    color: var(--primary-color);
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                  "
                >
                  Emergency Contact
                </h3>
                <p style="margin-bottom: 8px">
                  <strong>Contact Person:</strong> <?php echo htmlspecialchars($employee['contact_person'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Relationship:</strong> <?php echo htmlspecialchars($employee['relationship'] ?? 'N/A'); ?>
                </p>
                <p style="margin-bottom: 8px">
                  <strong>Contact No.:</strong> <?php echo htmlspecialchars($employee['contact_number'] ?? 'N/A'); ?>
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- Request Supplies Section -->
        <section id="request" class="dashboard-section">
          <div class="profile-info-wrapper">
            <div class="section-header" style="padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h2 style="font-size: 1.5rem; margin: 0;">Request Supplies</h2>
                <button type="button" id="view-requests-btn" class="btn btn-secondary" style="font-size: 0.8rem; padding: 8px 12px;">
                    <i class="fas fa-history"></i> View History
                </button>
            </div>

            <?php if ($hasPendingSupplies): ?>
              <div class="alert" style="background: #fff3cd; color: #856404; padding: 12px 16px; border-radius: 8px; border: 1px solid #ffeeba; margin-bottom: 20px;">
                <strong>Notice:</strong> You currently have a pending request. You must wait for it to be processed before submitting another.
              </div>
            <?php endif; ?>

            <!-- Dropdown to select form -->
            <div class="form-group">
              <label for="request_type_select">Request Type</label>
              <select id="request_type_select" name="request_type_select">
                <option value="bond_paper">Request Bond Paper</option>
                <option value="other_supplies">
                  Request Other Office Supplies
                </option>
              </select>
            </div>

            <!-- Bond Paper Form Container -->
            <div id="bond_paper_form_container">
              <h2
                class="centered-header"
                style="margin-bottom: 1.5rem; font-size: 1.5rem"
              >
                Request Bond Paper
              </h2>
              <form action="request_paper.php" method="POST">
                <div class="form-group">
                  <label for="paper_size">Paper Size</label>
                  <select id="paper_size" name="paper_size" required>
                    <option value="">-- Select Size --</option>
                    <option value="A4">A4</option>
                    <option value="Long">Long (8.5" x 13")</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="paper_quantity">Quantity (in reams)</label>
                  <input
                    type="number"
                    id="paper_quantity"
                    name="paper_quantity"
                    min="1"
                    value="1"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="paper_department">Department</label>
                  <input
                    type="text"
                    id="paper_department"
                    name="paper_department"
                    placeholder="e.g. HR, Engineering, Sales"
                    required
                  />
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-primary" <?php echo $hasPendingSupplies ? 'disabled' : ''; ?> >
                    Submit Paper Request
                  </button>
                </div>
              </form>
            </div>

            <!-- Other Supplies Form Container (hidden by default) -->
            <div id="other_supplies_form_container" style="display: none">
              <h2
                class="centered-header"
                style="margin-bottom: 1.5rem; font-size: 1.5rem"
              >
                Request Other Office Supplies
              </h2>
              <form action="request_other_supply.php" method="POST">
                <div class="form-group">
                  <label for="item_name">Item Name</label>
                  <input
                    type="text"
                    id="item_name"
                    name="item_name"
                    placeholder="e.g. Stapler, Ballpen, Ink Cartridge"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="1"
                    value="1"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="other_department">Department</label>
                  <input
                    type="text"
                    id="other_department"
                    name="department"
                    placeholder="e.g. HR, Engineering, Sales"
                    required
                  />
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-primary" <?php echo $hasPendingSupplies ? 'disabled' : ''; ?> >
                    Submit Request
                  </button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <!-- Conference Schedule Section -->
        <section id="conference" class="dashboard-section">
          <div class="profile-info-wrapper">
            <div class="section-header">
              <h1 class="centered-header">Book Conference Hall</h1>
              <button type="button" id="view-schedule-btn" class="btn btn-secondary" onclick="openScheduleModal()">
                <i class="fas fa-calendar-alt"></i> View Schedule
              </button>
            </div>
            <p class="centered-header-description">
              Check availability and schedule your meeting.
            </p>

            <?php if ($hasPendingConference): ?>
              <div class="alert" style="background: #fff3cd; color: #856404; padding: 12px 16px; border-radius: 8px; border: 1px solid #ffeeba; margin-bottom: 20px;">
                <strong>Notice:</strong> You currently have a pending conference booking. Please wait for it to be processed before submitting another.
              </div>
            <?php endif; ?>

            <form action="request_conference.php" method="POST">
              <div class="form-group">
                <label for="department_name">Department Name</label>
                <input
                  type="text"
                  id="department_name"
                  name="department_name"
                  placeholder="e.g. Marketing"
                  required
                />
              </div>
              <div class="form-group">
                <label for="purpose">Purpose / Meeting Title</label>
                <input
                  type="text"
                  id="purpose"
                  name="purpose"
                  placeholder="e.g. Monthly Check-In"
                  required
                />
              </div>
              <div class="form-group">
                <label for="participants">No. of Participants</label>
                <input
                  type="number"
                  id="participants"
                  name="participants"
                  min="1"
                  required
                />
              </div>
              <div class="form-group">
                <label for="booking_date">Date</label>
                <input
                  type="date"
                  id="booking_date"
                  name="booking_date"
                  required
                />
              </div>
              <div style="display: flex; gap: 15px">
                <div class="form-group" style="flex: 1">
                  <label for="start_time">Start Time</label>
                  <input
                    type="time"
                    id="start_time"
                    name="start_time"
                    required
                  />
                </div>
                <div class="form-group" style="flex: 1">
                  <label for="end_time">End Time</label>
                  <input type="time" id="end_time" name="end_time" required />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-primary" <?php echo $hasPendingConference ? 'disabled' : ''; ?> >
                  Submit Booking
                </button>
              </div>
            </form>
          </div>
        </section>
      </main>
    </div>

    <!-- Update Information Modal -->
    <div id="update-modal" class="modal-overlay">
      <div
        class="modal-content"
        style="
          max-width: 800px;
          text-align: left;
          max-height: 90vh;
          overflow-y: auto;
        "
      >
        <div class="section-header" style="margin-bottom: 20px">
          <h2>Update Information</h2>
          <button
            type="button"
            id="close-update-modal"
            style="
              background: none;
              border: none;
              font-size: 1.5rem;
              cursor: pointer;
            "
          >
            &times;
          </button>
        </div>
        <form id="employee-form" action="../update_employee.php" method="POST">
          <fieldset>
            <legend>Personal Information</legend>
            <div class="form-group">
              <label for="fullname">Full Name</label
              ><input
                type="text"
                id="fullname"
                name="fullname"
                value="<?php echo htmlspecialchars($employee['name'] ?? ''); ?>"
                required
              />
            </div>
            <div class="form-group">
              <label for="civil_status">Civil Status</label>
              <select id="civil_status" name="civil_status" required>
                <option value="">-- Select --</option>
                <option value="Single" <?php if (($employee['civil_status'] ?? '') == 'Single') echo 'selected'; ?>>Single</option>
                <option value="Married" <?php if (($employee['civil_status'] ?? '') == 'Married') echo 'selected'; ?>>Married</option>
                <option value="Widowed" <?php if (($employee['civil_status'] ?? '') == 'Widowed') echo 'selected'; ?>>Widowed</option>
                <option value="Separated" <?php if (($employee['civil_status'] ?? '') == 'Separated') echo 'selected'; ?>>Separated</option>
              </select>
            </div>
            <div class="form-group">
              <label for="gender">Gender</label>
              <select id="gender" name="gender" required>
                <option value="">-- Select --</option>
                <option value="Male" <?php if (($employee['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if (($employee['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
              </select>
            </div>
            <div class="form-group">
              <label for="birth_date">Date of Birth</label>
              <input
                type="date"
                id="birth_date"
                name="date_of_birth"
                value="<?php echo htmlspecialchars($employee['date_of_birth'] ?? ''); ?>"
                required
              />
            </div>
          </fieldset>

          <fieldset>
            <legend>Address Information</legend>
            <div class="form-group">
              <label for="permanent_address">Permanent Address</label
              ><textarea
                id="permanent_address"
                name="permanent_address"
                rows="3"
                required
              ><?php echo htmlspecialchars($employee['permanent_address'] ?? ''); ?></textarea>
            </div>
            <div class="form-group radio-group">
              <label>Is your current address your permanent address?</label>
              <div>
                <input
                  type="radio"
                  id="is_permanent_yes"
                  name="is_address_permanent"
                  value="yes"
                  checked
                /><label for="is_permanent_yes">Yes</label>
              </div>
              <div>
                <input
                  type="radio"
                  id="is_permanent_no"
                  name="is_address_permanent"
                  value="no"
                /><label for="is_permanent_no">No</label>
              </div>
            </div>
            <div
              class="form-group"
              id="current_address_group"
              style="display: none"
            >
              <label for="current_address">Current Address</label
              ><textarea
                id="current_address"
                name="current_address"
                rows="3"
              ><?php echo htmlspecialchars($employee['current_address'] ?? ''); ?></textarea>
            </div>
          </fieldset>

          <fieldset>
            <legend>Contact Information</legend>
            <div class="form-group">
              <label for="contact_number">Active Personal Contact Number</label
              ><input
                type="tel"
                id="contact_number"
                name="personal_no"
                required
                value="<?php echo htmlspecialchars($employee['personal_no'] ?? ''); ?>"
              />
            </div>
            <div class="form-group">
              <label for="other_contact_number"
                >Other Contact Number (Optional)</label
              ><input
                type="tel"
                id="other_contact_number"
                name="other_contact_number"
                placeholder="e.g., 09181234567"
              />
            </div>
            <div class="form-group">
              <label for="personal_email">Updated Personal Email Address</label
              ><input
                type="email"
                id="personal_email"
                name="personal_email"
                required
                value="<?php echo htmlspecialchars($employee['personal_email'] ?? ''); ?>"
              />
            </div>
            <div class="form-group">
              <label for="work_email">Work Email Address</label
              ><input
                type="email"
                id="work_email"
                name="work_email"
                value="<?php echo htmlspecialchars($employee['work_email'] ?? ''); ?>"
              />
            </div>
          </fieldset>

          <fieldset>
            <legend>Emergency Contact</legend>
            <div class="form-group">
              <label for="emergency_contact_person"
                >Emergency Contact Person</label
              ><input
                type="text"
                id="emergency_contact_person"
                name="contact_person"
                value="<?php echo htmlspecialchars($employee['contact_person'] ?? ''); ?>"
                required
              />
            </div>
            <div class="form-group">
              <label for="emergency_contact_relationship">Relationship</label
              ><input
                type="text"
                id="emergency_contact_relationship"
                name="relationship"
                required
                value="<?php echo htmlspecialchars($employee['relationship'] ?? ''); ?>"
              />
            </div>
            <div class="form-group">
              <label for="emergency_contact_number"
                >Emergency Contact Number</label
              ><input
                type="tel"
                id="emergency_contact_number"
                name="contact_number"
                value="<?php echo htmlspecialchars($employee['contact_number'] ?? ''); ?>"
                required
              />
            </div>
          </fieldset>

          <fieldset>
            <legend>Legal Agreements</legend>
            <div class="form-group checkbox-group">
              <input
                type="checkbox"
                id="data_privacy_consent"
                name="data_privacy_consent"
                required
              />
              <label for="data_privacy_consent"
                >I consent to the collection and processing of my personal data
                in accordance with the Data Privacy Act.</label
              >
            </div>
            <div class="form-group checkbox-group">
              <input
                type="checkbox"
                id="declaration"
                name="declaration"
                required
              />
              <label for="declaration"
                >I declare that the information I have provided is true,
                correct, and complete to the best of my knowledge.</label
              >
            </div>
          </fieldset>

          <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Submit Update</button>
            <button type="button" id="cancel-update" class="btn btn-secondary">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Request History Modal -->
    <div id="request-history-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 700px; text-align: left;">
        <div class="section-header" style="margin-bottom: 20px;">
          <h2>My Supply Requests</h2>
          <button type="button" id="close-history-modal" class="modal-close-btn">&times;</button>
        </div>
        <div id="request-history-content" class="table-wrapper" style="margin-top: 0; max-height: 60vh; overflow-y: auto;">
          <!-- History table will be injected here by JS -->
        </div>
      </div>
    </div>

    <!-- Conference Schedule Modal -->
    <div id="schedule-modal" class="modal-overlay" onclick="if (event.target === this) closeScheduleModal()">
      <div class="modal-content" style="max-width: 800px; text-align: left;">
        <div class="section-header" style="margin-bottom: 20px;">
          <h2>Conference Hall Schedule</h2>
          <button type="button" id="close-schedule-modal" class="modal-close-btn" onclick="closeScheduleModal()">&times;</button>
        </div>
        <div id="schedule-history-content" class="table-wrapper" style="margin-top: 0; max-height: 60vh; overflow-y: auto;">
          <!-- Schedule table will be injected here by JS -->
        </div>
      </div>
    </div>

    <script>
      // Pass PHP data to JavaScript
      window.requestHistory = <?php echo json_encode($requests); ?>;
      window.conferenceSchedule = <?php echo json_encode($conferenceBookings); ?>;

      // Fallback modal helpers (ensure the schedule modal works even if the external script fails)
      function openScheduleModal() {
        if (typeof window.showScheduleModal === "function") {
          window.showScheduleModal();
          return;
        }

        const modal = document.getElementById("schedule-modal");
        const contentDiv = document.getElementById("schedule-history-content");
        if (!modal || !contentDiv) {
          console.warn("Schedule modal elements not found.");
          return;
        }

        // Fallback: Populate table manually if script.js didn't
        const schedule = window.conferenceSchedule || [];
        let html = `<table><thead><tr><th>Date</th><th>Time</th><th>Department</th><th>Participants</th><th>Booked By</th><th>Status</th></tr></thead><tbody>`;
        
        if (schedule.length === 0) {
            html += `<tr><td colspan="6" style="text-align: center;">No schedule entries found.</td></tr>`;
        } else {
            schedule.forEach(booking => {
                const date = booking.booking_date ? new Date(booking.booking_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
                const start = booking.start_time ? booking.start_time.substring(0, 5) : '';
                const end = booking.end_time ? booking.end_time.substring(0, 5) : '';
                const statusClass = (booking.status || 'Pending').toLowerCase() === 'pending' ? 'status-pending' : 'status-hired';
                
                html += `<tr><td>${date}</td><td>${start} - ${end}</td><td>${booking.department || ''}</td><td>${booking.participants || '-'}</td><td>${booking.employee_name || ''}</td><td><span class="status-pill ${statusClass}">${booking.status || 'Pending'}</span></td></tr>`;
            });
        }
        html += `</tbody></table>`;
        contentDiv.innerHTML = html;

        // Ensure the modal is visible even if CSS rules are not applied.
        modal.style.display = "flex";
        modal.style.opacity = "1";
        modal.style.visibility = "visible";
        modal.classList.add("visible");
      }

      function closeScheduleModal() {
        if (typeof window.closeScheduleModal === "function" && window.closeScheduleModal !== closeScheduleModal) {
          // Use script-defined close handler if available
          window.closeScheduleModal();
          return;
        }

        const modal = document.getElementById("schedule-modal");
        if (!modal) return;

        modal.style.opacity = "";
        modal.style.visibility = "";
        modal.classList.remove("visible");
      }
    </script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
  </body>
</html>
