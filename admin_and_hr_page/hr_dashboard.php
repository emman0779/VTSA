<?php
session_start();

// Check if user is logged in and is an HR user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
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

// Fetch all employees
$result = $conn->query("SELECT * FROM employees");
$employees_list = $result->fetch_all(MYSQLI_ASSOC);
$employeesById = [];
foreach ($employees_list as $employee) {
    $employeesById[$employee['id']] = $employee;
}

$conn->close();
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HR Dashboard - VTSA System</title>
    <link rel="stylesheet" href="hr_dashboard.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body class="dashboard-page">
    <aside class="sidebar">
      <div class="sidebar-header">
        <a href="#" class="logo">
          <img src="../applicant_page/images/vtsa_white.png" alt="VTSA Logo" />
        </a>
        <div class="sidebar-title">HR Panel</div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <a href="#dashboard" class="nav-link active"
              ><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a
            >
          </li>
          <li>
            <a href="#applicants" class="nav-link"
              ><i class="fas fa-users"></i> <span>Applicants</span></a
            >
          </li>
          <li>
            <a href="#employees" class="nav-link"
              ><i class="fas fa-user-tie"></i>
              <span>Updated Employees Data</span></a
            >
          </li>
          <li>
            <a href="#jobs" class="nav-link"
              ><i class="fas fa-briefcase"></i> <span>Job Listings</span></a
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
        <div class="top-bar-title">VTSA International Inc.</div>
        <img
          src="../applicant_page/images/logo2.png"
          alt="Logo"
          class="header-logo"
        />
      </header>
      <main class="main-content">
        <!-- Dashboard Overview Section -->
        <section id="dashboard" class="dashboard-section active-section">
          <h1>Dashboard Overview</h1>
          <div class="stats-cards">
            <div class="card">
              <div class="card-icon"><i class="fas fa-users"></i></div>
              <div class="card-info">
                <h4>Total Applicants</h4>
                <p>125</p>
              </div>
            </div>
            <div class="card">
              <div class="card-icon"><i class="fas fa-briefcase"></i></div>
              <div class="card-info">
                <h4>Open Positions</h4>
                <p>6</p>
              </div>
            </div>
            <div class="card">
              <div class="card-icon"><i class="fas fa-user-plus"></i></div>
              <div class="card-info">
                <h4>New Hires (Month)</h4>
                <p>4</p>
              </div>
            </div>
          </div>

          <div class="charts-grid">
            <div class="chart-container">
              <canvas id="applicantChart"></canvas>
            </div>
            <div class="chart-container">
              <canvas id="jobChart"></canvas>
            </div>
          </div>
        </section>

        <!-- Applicants Management Section -->
        <section id="applicants" class="dashboard-section">
          <h1>Manage Applicants</h1>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Applied For</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>John Doe</td>
                  <td>john.doe@example.com</td>
                  <td>Test and Commissioning Technician</td>
                  <td>
                    <span class="status-pill status-pending">Pending</span>
                  </td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>Jane Smith</td>
                  <td>jane.smith@example.com</td>
                  <td>Admin Staff</td>
                  <td><span class="status-pill status-hired">Hired</span></td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Employees Management Section -->
        <section id="employees" class="dashboard-section">
          <div class="section-header">
            <h1>Manage Employees</h1>
            <button id="export-btn" class="btn btn-primary add-new-btn">
              <i class="fas fa-file-export"></i> Export
            </button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Employee Name</th>
                  <th>Work Email</th>
                  <th>Contact Number</th>
                  <th>Civil Status</th>
                  <th>Date of Birth</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($employees_list)): ?>
                  <?php foreach ($employees_list as $employee): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($employee['name']); ?></td>
                      <td><?php echo htmlspecialchars($employee['work_email'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($employee['personal_no'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($employee['civil_status'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($employee['date_of_birth'] ?? 'N/A'); ?></td>
                      <td>
                        <button
                          class="action-btn view-btn"
                          data-id="<?php echo $employee['id']; ?>"
                          title="View Full Record"
                        >
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn delete-btn" title="Delete Record">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" style="text-align: center;">No employee records found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Job Listings Management Section -->
        <section id="jobs" class="dashboard-section">
          <div class="section-header">
            <h1>Manage Job Listings</h1>
            <button class="btn btn-primary add-new-btn">
              <i class="fas fa-plus"></i> Add New Job
            </button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Job Title</th>
                  <th>Status</th>
                  <th>Applicants</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Test and Commissioning Technician</td>
                  <td><span class="status-pill status-open">Open</span></td>
                  <td>25</td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>PMS Technician</td>
                  <td><span class="status-pill status-open">Open</span></td>
                  <td>18</td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>Sales & Marketing Officer</td>
                  <td><span class="status-pill status-closed">Closed</span></td>
                  <td>32</td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-btn">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

    <!-- Export Selection Modal -->
    <div id="export-modal" class="modal-overlay">
      <div class="modal-content">
        <h3>Select Export Format</h3>
        <div class="form-group">
          <select
            id="export-format"
            style="
              width: 100%;
              padding: 10px;
              border-radius: 5px;
              border: 1px solid #ccc;
            "
          >
            <option value="">-- Select Format --</option>
            <option value="Excel">Excel</option>
            <option value="Word">Word</option>
            <option value="PDF">PDF</option>
          </select>
        </div>
        <div class="modal-actions">
          <button
            type="button"
            id="confirm-export"
            class="btn btn-primary"
            style="padding: 8px 20px"
          >
            Export
          </button>
          <button
            type="button"
            id="cancel-export"
            class="btn btn-secondary"
            style="padding: 8px 20px"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Employee Details Modal -->
    <div id="employee-details-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 600px; max-height: 95vh; overflow: auto; text-align: left">
        <div class="section-header" style="margin-bottom: 20px">
          <h3 id="modal-employee-name">Employee Details</h3>
          <button type="button" id="close-employee-modal" class="modal-close-btn" style="float: right; background: transparent;
  border: none;
  font-size: 1.5rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #666;
  border-radius: 50%;
  transition:
    background-color 0.2s ease,
    color 0.2s ease;
  margin-left: auto;">&times;</button>
        </div>
        <div id="employee-details-content" class="table-wrapper" style="margin-top: 0">
          <!-- Employee details table will be injected here by JS -->
        </div>
      </div>
    </div>
    <script>
      window.employeesData = <?php echo json_encode($employeesById); ?>;
    </script>
    <script src="hr_dashboard.js"></script>
  </body>
</html>
