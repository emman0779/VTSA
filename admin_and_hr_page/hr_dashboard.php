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

// Fetch all applicants
$result_applicants = $conn->query("SELECT * FROM applicants ORDER BY date_time_applied DESC");
$applicants_list = $result_applicants->fetch_all(MYSQLI_ASSOC);
$applicantsById = [];

if (!empty($applicants_list)) {
    $appIds = [];
    foreach ($applicants_list as $app) {
        $app['skills'] = [];
        $app['education'] = [];
        $app['work_exp'] = [];
        $applicantsById[$app['id']] = $app;
        $appIds[] = $app['id'];
    }

    if (!empty($appIds)) {
        $idsStr = implode(',', $appIds);

        // Fetch Skills
        $res = $conn->query("SELECT * FROM applicant_skills WHERE applicant_id IN ($idsStr)");
        while ($row = $res->fetch_assoc()) {
            if (isset($applicantsById[$row['applicant_id']])) {
                $applicantsById[$row['applicant_id']]['skills'][] = $row;
            }
        }

        // Fetch Education
        $res = $conn->query("SELECT * FROM applicant_education WHERE applicant_id IN ($idsStr)");
        while ($row = $res->fetch_assoc()) {
            if (isset($applicantsById[$row['applicant_id']])) {
                $applicantsById[$row['applicant_id']]['education'][] = $row;
            }
        }

        // Fetch Work Experience
        $res = $conn->query("SELECT * FROM applicant_work_exp WHERE applicant_id IN ($idsStr)");
        while ($row = $res->fetch_assoc()) {
            if (isset($applicantsById[$row['applicant_id']])) {
                $applicantsById[$row['applicant_id']]['work_exp'][] = $row;
            }
        }
    }
}

// Fetch all job listings
$sql_jobs = "
    SELECT 
        jld.id, 
        jld.job_title, 
        jld.description, 
        jld.status, 
        COUNT(a.id) AS applicants_count
    FROM 
        job_listing_database jld
    LEFT JOIN 
        applicants a ON jld.job_title = a.desired_position
    GROUP BY 
        jld.id
    ORDER BY 
        jld.job_title ASC
";
$result_jobs = $conn->query($sql_jobs);
$job_listings = $result_jobs->fetch_all(MYSQLI_ASSOC);

// Calculate Recruitment Trends (Last 6 Months)
$trend_labels = [];
$trend_applicants = [];
$trend_hired = [];

for ($i = 5; $i >= 0; $i--) {
    // Use first day of month to avoid issues on 31st (e.g. Mar 31 - 1 month != Feb)
    $timestamp = strtotime("-$i months", strtotime(date('Y-m-01')));
    $date_key = date('Y-m', $timestamp);
    $label = date('M', $timestamp);
    $trend_labels[] = $label;
    $trend_applicants[$date_key] = 0;
    $trend_hired[$date_key] = 0;
}

foreach ($applicants_list as $app) {
    $month = date('Y-m', strtotime($app['date_time_applied']));
    if (isset($trend_applicants[$month])) {
        $trend_applicants[$month]++;
        if ($app['status'] === 'Hired') {
            $trend_hired[$month]++;
        }
    }
}

// Prepare Data for "Applicants per Open Position" Chart
$open_job_labels = [];
$open_job_applicants = [];
foreach ($job_listings as $job) {
    if (strcasecmp($job['status'], 'open') === 0) {
        $open_job_labels[] = $job['job_title'];
        $open_job_applicants[] = (int)$job['applicants_count'];
    }
}

$conn->close();
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HR Dashboard - VTSA System</title>
    <link rel="stylesheet" href="hr_dashboard.css?v=<?php echo time(); ?>" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Additional styles for applicant modal */
        .skill-badge {
            background-color: #eef2f7;
            color: #203864;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
            margin: 0 5px 5px 0;
        }
        .experience-item { margin-bottom: 15px; }
        .item-meta { font-size: 0.85rem; color: #666; margin-bottom: 5px; }
    </style>
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
                <p><?php echo count($applicants_list); ?></p>
              </div>
            </div>
            <div class="card">
              <div class="card-icon"><i class="fas fa-user-tie"></i></div>
              <div class="card-info">
                <h4>Total Employees</h4>
                <p><?php echo count($employees_list); ?></p>
              </div>
            </div>
            <div class="card">
              <div class="card-icon"><i class="fas fa-briefcase"></i></div>
              <div class="card-info">
                <h4>Total Job Listings</h4>
                <p><?php echo count($job_listings); ?></p>
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
          <div class="section-header">
            <h1>Manage Applicants</h1>
            <button id="export-applicants-btn" class="btn btn-primary"><i class="fas fa-file-export"></i> Export to Excel</button>
          </div>
          <div class="table-wrapper" style="margin-top: 1.5rem;">
            <table>
              <thead>
                <tr>
                  <th>Date Applied</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Gender</th>
                  <th>Position Applied</th>
                  <th>Source</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($applicants_list)): ?>
                  <?php foreach ($applicants_list as $applicant): ?>
                    <tr>
                      <td><?php echo date('M j, Y', strtotime($applicant['date_time_applied'])); ?></td>
                      <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['gender'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($applicant['desired_position'] ?? 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($applicant['source_of_hiring'] ?? 'N/A'); ?></td>
                      <td>
                        <?php 
                          $statusClass = 'status-pending';
                          $status = $applicant['status'] ?? 'Pending';
                          
                          if ($status === 'Hired') {
                              $statusClass = 'status-hired';
                          } elseif ($status === 'Invited for Interview') {
                              $statusClass = 'status-open';
                          } elseif ($status === 'Reapply in 3 months' || $status === 'Rejected') {
                              $statusClass = 'status-closed';
                          }
                        ?>
                        <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                      </td>
                      <td>
                        <button type="button" class="action-btn view-btn view-applicant-btn" data-id="<?php echo $applicant['id']; ?>" title="View Details"><i class="fas fa-eye"></i></button>
                        <button type="button" class="action-btn edit-btn edit-status-btn" data-id="<?php echo $applicant['id']; ?>" data-status="<?php echo htmlspecialchars($status); ?>" title="Edit Status"><i class="fas fa-edit"></i></button>
                         <form action="delete_applicant.php" method="POST" style="display:inline;">
                            <input type="hidden" name="applicant_id" value="<?php echo $applicant['id']; ?>">
                            <button type="submit" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this applicant? This action cannot be undone.')">
                              <i class="fas fa-trash"></i>
                            </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="8" style="text-align: center;">No applicants found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Employees Management Section -->
        <section id="employees" class="dashboard-section">
          <div class="section-header">
            <h1>Manage Employees</h1>
            <button id="export-employees-btn" class="btn btn-primary add-new-btn">
              <i class="fas fa-file-export"></i> Export to Excel
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
                  <th>Gender</th>
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
                      <td><?php echo htmlspecialchars($employee['gender'] ?? 'N/A'); ?></td>
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
                    <td colspan="7" style="text-align: center;">No employee records found.</td>
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
            <button type="button" class="btn btn-primary add-new-btn" id="open-add-job-modal">


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
                <?php if (!empty($job_listings)): ?>
                  <?php foreach ($job_listings as $job): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($job['job_title']); ?></td>
                      <td>
                        <?php
                            $statusClass = strtolower($job['status']) === 'open' ? 'status-open' : 'status-closed';
                        ?>
                        <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($job['status']); ?></span>
                      </td>
                      <td><?php echo htmlspecialchars($job['applicants_count']); ?></td>
                      <td>
                        <button type="button" class="action-btn view-btn view-job-applicants-btn" data-job-title="<?php echo htmlspecialchars($job['job_title']); ?>" title="View Applicants for this Job">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="action-btn edit-btn edit-job-btn" data-id="<?php echo $job['id']; ?>" data-status="<?php echo htmlspecialchars($job['status']); ?>" title="Edit Job Status">
                          <i class="fas fa-edit"></i>
                        </button>
                        <form action="delete_job.php" method="POST" style="display:inline;">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" class="action-btn delete-btn" title="Delete Job" onclick="return confirm('Are you sure you want to delete this job listing? This action cannot be undone.');">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="4" style="text-align: center;">No job listings found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
       <!-- Add Job Modal -->
       <div id="add-job-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="section-header" style="margin-bottom: 20px;">
                <h3>Add New Job Listing</h3>
                <button type="button" id="close-add-job-modal" class="modal-close-btn" style="float: right;">&times;</button>
            </div>
            <form action="add_job.php" method="POST">
                <div class="form-group">
                    <label for="job_title">Job Title</label>
                    <input type="text" id="job_title" name="job_title" placeholder="Enter job title" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                </div>
                <div class="form-group">
                    <label for="job_description">Job Description</label>
                    <textarea id="job_description" name="job_description" placeholder="Enter job description" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 20px">Add Job</button>
                    <button type="button" id="cancel-add-job" class="btn btn-secondary" style="padding: 8px 20px">Cancel</button>
                </div>
            </form>
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

    <!-- Job Applicants List Modal -->
    <div id="job-applicants-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 800px; text-align: left; max-height: 90vh; overflow-y: auto;">
        <div class="section-header" style="margin-bottom: 20px;">
            <h3 id="modal-job-title">Applicants for Position</h3>
            <button type="button" id="close-job-applicants-modal" class="modal-close-btn" style="float: right;">&times;</button>
        </div>
        <div class="table-wrapper" style="margin-top: 0; box-shadow: none;">
            <table id="job-applicants-table">
                <thead>
                    <tr><th>Name</th><th>Date Applied</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody><!-- Populated by JS --></tbody>
            </table>
        </div>
      </div>
    </div>

    <!-- Applicant Details Modal -->
    <div id="applicant-details-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 800px; text-align: left; max-height: 90vh; overflow-y: auto;">
        <div class="section-header" style="margin-bottom: 20px;">
            <h2 id="modal-applicant-name">Applicant Details</h2>
            <button type="button" id="close-applicant-modal" class="modal-close-btn" style="float: right; background: transparent; border: none; font-size: 1.5rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; border-radius: 50%; transition: background-color 0.2s ease, color 0.2s ease; margin-left: auto;">&times;</button>
        </div>
        <div id="applicant-details-content">
            <!-- Content injected by JS -->
        </div>
      </div>
    </div>

    <!-- Edit Status Modal -->
    <div id="edit-status-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 400px; text-align: left;">
        <div class="section-header" style="margin-bottom: 20px;">
            <h3>Edit Application Status</h3>
            <button type="button" id="close-status-modal" class="modal-close-btn" style="float: right; background: transparent; border: none; font-size: 1.5rem; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; border-radius: 50%; transition: background-color 0.2s ease, color 0.2s ease; margin-left: auto;">&times;</button>
        </div>
        <form action="update_applicant_status.php" method="POST">
            <input type="hidden" id="edit-applicant-id" name="applicant_id" value="">
            <div class="form-group">
                <label for="status-select">Select Status</label>
                <select id="status-select" name="status" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="Pending">Pending</option>
                    <option value="Invited for Interview">Invited for Interview</option>
                    <option value="Pooling">Pooling</option>
                    <option value="Hired">Hired</option>
                    <option value="Reapply in 3 months">Reapply in 3 months</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Update Status</button>
                <button type="button" id="cancel-status-edit" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Edit Job Status Modal -->
    <div id="edit-job-status-modal" class="modal-overlay">
      <div class="modal-content" style="max-width: 400px; text-align: left;">
        <div class="section-header" style="margin-bottom: 20px;">
            <h3>Edit Job Status</h3>
            <button type="button" id="close-job-status-modal" class="modal-close-btn" style="float: right;">&times;</button>
        </div>
        <form action="update_job_status.php" method="POST">
            <input type="hidden" id="edit-job-id" name="job_id" value="">
            <div class="form-group">
                <label for="job-status-select">Select Status</label>
                <select id="job-status-select" name="status" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Update Status</button>
                <button type="button" id="cancel-job-status-edit" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Export Applicants Confirmation Modal -->
    <div id="export-applicants-modal" class="modal-overlay">
      <div class="modal-content">
        <h3>Export Applicant Records</h3>
        <p>Do you want to continue and export all applicant records to an Excel file?</p>
        <div class="modal-actions">
          <a href="export_applicants.php" id="confirm-export-applicants" class="btn btn-primary" style="text-decoration: none;">Continue</a>
          <button type="button" id="cancel-export-applicants" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Export Employees Confirmation Modal -->
    <div id="export-employees-modal" class="modal-overlay">
      <div class="modal-content">
        <h3>Export Employee Records</h3>
        <p>Do you want to continue and export all employee records to an Excel file?</p>
        <div class="modal-actions">
          <a href="export_employees.php" id="confirm-export-employees" class="btn btn-primary" style="text-decoration: none;">Continue</a>
          <button type="button" id="cancel-export-employees" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <script>
      window.employeesData = <?php echo json_encode($employeesById); ?>;
      window.applicantsData = <?php echo json_encode($applicantsById); ?>;
      window.recruitmentTrendsData = {
        labels: <?php echo json_encode($trend_labels); ?>,
        applicants: <?php echo json_encode(array_values($trend_applicants)); ?>,
        hired: <?php echo json_encode(array_values($trend_hired)); ?>
      };
      window.openJobsData = {
        labels: <?php echo json_encode($open_job_labels); ?>,
        counts: <?php echo json_encode($open_job_applicants); ?>
      };

      document.addEventListener('DOMContentLoaded', function() {
        const applicantModal = document.getElementById('applicant-details-modal');
        const closeApplicantModalBtn = document.getElementById('close-applicant-modal');
        const applicantContent = document.getElementById('applicant-details-content');
        const modalApplicantName = document.getElementById('modal-applicant-name');

        const statusModal = document.getElementById('edit-status-modal');
        const closeStatusModalBtn = document.getElementById('close-status-modal');
        const cancelStatusBtn = document.getElementById('cancel-status-edit');
        const editApplicantIdInput = document.getElementById('edit-applicant-id');
        const statusSelect = document.getElementById('status-select');

        const jobApplicantsModal = document.getElementById('job-applicants-modal');
        const closeJobApplicantsModalBtn = document.getElementById('close-job-applicants-modal');
        const jobApplicantsTableBody = document.querySelector('#job-applicants-table tbody');

        const editJobStatusModal = document.getElementById('edit-job-status-modal');
        const closeJobStatusModalBtn = document.getElementById('close-job-status-modal');
        const cancelJobStatusBtn = document.getElementById('cancel-job-status-edit');
        const editJobIdInput = document.getElementById('edit-job-id');
        const jobStatusSelect = document.getElementById('job-status-select');

        // Handle "View Details" click for applicants
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.view-applicant-btn')) {
                const btn = e.target.closest('.view-applicant-btn');
                const id = btn.getAttribute('data-id');
                const applicant = window.applicantsData[id];

                if (applicant) {
                    modalApplicantName.textContent = applicant.name;

                    // Profile Picture
                    const pfp = applicant.profile_pic ? applicant.profile_pic : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(applicant.name) + '&background=203864&color=fff&size=128';

                    // Resume
                    let resumeHtml = '<p style="color: #666;">No resume uploaded.</p>';
                    if (applicant.resume) {
                        const resumeName = applicant.resume.split('/').pop();
                        // Adjust path if needed, assuming relative to this file's location or root
                        resumeHtml = `<a href="${applicant.resume}" target="_blank" class="btn btn-primary" style="text-decoration: none; display: inline-block; margin-top: 10px;"><i class="fas fa-download"></i> Download Resume</a>`;
                    }

                    // Skills
                    let skillsHtml = '<p style="color: #666;">No skills listed.</p>';
                    if (applicant.skills && applicant.skills.length > 0) {
                        skillsHtml = ''; // Clear default message
                        applicant.skills.forEach(skill => {
                            skillsHtml += `<span class="skill-badge">${skill.skill_name}</span>`;
                        });
                    }

                    // Education
                    let eduHtml = '<p style="color: #666;">No education listed.</p>';
                    if (applicant.education && applicant.education.length > 0) {
                        eduHtml = ''; // Clear default message
                        applicant.education.forEach(edu => {
                             eduHtml += `<div class="experience-item">
                                <h4 style="margin: 0;">${edu.degree}</h4>
                                <p class="item-meta">${edu.institution} | ${edu.years}</p>
                                ${edu.description ? `<p style="margin: 5px 0;">${edu.description}</p>` : ''}
                             </div>`;
                        });
                    }

                    // Work Experience
                    let workHtml = '<p style="color: #666;">No work experience listed.</p>';
                    if (applicant.work_exp && applicant.work_exp.length > 0) {
                        workHtml = ''; // Clear default message
                        applicant.work_exp.forEach(exp => {
                             workHtml += `<div class="experience-item">
                                <h4 style="margin: 0;">${exp.job_title}</h4>
                                <p class="item-meta">${exp.company} | ${exp.years}</p>
                                ${exp.description ? `<p style="margin: 5px 0;">${exp.description}</p>` : ''}
                             </div>`;
                        });
                    }

                    applicantContent.innerHTML = `
                        <div style="display:flex; gap: 20px; margin-bottom: 25px; align-items:center;">
                            <img src="${pfp}" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border: 3px solid #203864;">
                            <div>
                                <h3 style="margin: 0 0 5px 0; color: #203864;">${applicant.name}</h3>
                                <p style="margin: 2px 0;"><strong>Email:</strong> ${applicant.email}</p>
                                <p style="margin: 2px 0;"><strong>Phone:</strong> ${applicant.phone || 'N/A'}</p>
                                <p style="margin: 2px 0;"><strong>Address:</strong> ${applicant.address || 'N/A'}</p>
                                <p style="margin: 2px 0;"><strong>Gender:</strong> ${applicant.gender || 'N/A'}</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h4 style="color: #203864; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px;">Education</h4>
                                ${eduHtml}
                            </div>
                            <div>
                                <h4 style="color: #203864; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px;">Work Experience</h4>
                                ${workHtml}
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                             <h4 style="color: #203864; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px;">Skills</h4>
                             ${skillsHtml}
                        </div>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;">
                            <h4 style="margin-top: 0; color: #203864; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Resume</h4>
                            ${resumeHtml}
                        </div>
                    `;

                    applicantModal.classList.add('visible');
                }
            }
            
            // Handle "Edit Status" click
            if (e.target.closest('.edit-status-btn')) {
                const btn = e.target.closest('.edit-status-btn');
                const id = btn.getAttribute('data-id');
                const currentStatus = btn.getAttribute('data-status');

                editApplicantIdInput.value = id;
                statusSelect.value = currentStatus;
                
                statusModal.classList.add('visible');
            }

             // Handle "View Job Applicants" (Eye Icon on Jobs Table)
             if (e.target.closest('.view-job-applicants-btn')) {
                const btn = e.target.closest('.view-job-applicants-btn');
                const jobTitle = btn.getAttribute('data-job-title');
                const modalTitle = document.getElementById('modal-job-title');
                
                modalTitle.textContent = `Applicants for ${jobTitle}`;
                jobApplicantsTableBody.innerHTML = '';

                const applicants = Object.values(window.applicantsData);
                // Filter applicants by matching job title
                const filtered = applicants.filter(app => 
                    (app.desired_position || '').trim().toLowerCase() === jobTitle.trim().toLowerCase()
                );

                if (filtered.length > 0) {
                    filtered.forEach(app => {
                        // Determine status class
                        let statusClass = 'status-pending';
                        if (app.status === 'Hired') statusClass = 'status-hired';
                        else if (app.status === 'Invited for Interview') statusClass = 'status-open';
                        else if (app.status === 'Reapply in 3 months' || app.status === 'Rejected') statusClass = 'status-closed';

                        const dateApplied = new Date(app.date_time_applied).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${app.name}</td>
                            <td>${dateApplied}</td>
                            <td><span class="status-pill ${statusClass}">${app.status}</span></td>
                            <td>
                                <button type="button" class="action-btn view-btn view-applicant-btn" data-id="${app.id}" title="View Details"><i class="fas fa-eye"></i></button>
                            </td>
                        `;
                        jobApplicantsTableBody.appendChild(row);
                    });
                } else {
                    jobApplicantsTableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px;">No applicants found for this position.</td></tr>';
                }
                jobApplicantsModal.classList.add('visible');
             }

             // Handle "Edit Job Status" click
            if (e.target.closest('.edit-job-btn')) {
                const btn = e.target.closest('.edit-job-btn');
                const id = btn.getAttribute('data-id');
                const currentStatus = btn.getAttribute('data-status');

                editJobIdInput.value = id;
                jobStatusSelect.value = currentStatus;
                
                editJobStatusModal.classList.add('visible');
            }
        });

        if(closeApplicantModalBtn) {
            closeApplicantModalBtn.addEventListener('click', () => {
                applicantModal.classList.remove('visible');
            });
        }
        if(applicantModal) {
            applicantModal.addEventListener('click', (e) => {
                if (e.target === applicantModal) applicantModal.classList.remove('visible');
            });
        }

        // Status Modal Close Logic
        const closeStatusModal = () => {
            statusModal.classList.remove('visible');
        };

        if(closeStatusModalBtn) {
            closeStatusModalBtn.addEventListener('click', closeStatusModal);
        }
        if(cancelStatusBtn) {
            cancelStatusBtn.addEventListener('click', closeStatusModal);
        }
        if(statusModal) {
            statusModal.addEventListener('click', (e) => {
                if (e.target === statusModal) closeStatusModal();
            });
        }

        // Job Applicants Modal Close Logic
        if(closeJobApplicantsModalBtn) {
            closeJobApplicantsModalBtn.addEventListener('click', () => { jobApplicantsModal.classList.remove('visible'); });
        }
        if(jobApplicantsModal) {
            jobApplicantsModal.addEventListener('click', (e) => {
                if (e.target === jobApplicantsModal) jobApplicantsModal.classList.remove('visible');
            });
        }

        // Edit Job Status Modal Close Logic
        if(closeJobStatusModalBtn) {
            closeJobStatusModalBtn.addEventListener('click', () => editJobStatusModal.classList.remove('visible'));
        }
        if(cancelJobStatusBtn) {
            cancelJobStatusBtn.addEventListener('click', () => editJobStatusModal.classList.remove('visible'));
        }
        if(editJobStatusModal) {
            editJobStatusModal.addEventListener('click', (e) => {
                if (e.target === editJobStatusModal) editJobStatusModal.classList.remove('visible');
            });
        }
      });
    </script>
    <script src="hr_dashboard.js?v=<?php echo time(); ?>"></script>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addJobModal = document.getElementById('add-job-modal');
            const openAddJobModalBtn = document.getElementById('open-add-job-modal');
            const closeAddJobModalBtn = document.getElementById('close-add-job-modal');
            const cancelAddJobBtn = document.getElementById('cancel-add-job');

            // Function to open the add job modal
            function openAddJobModal() {
                addJobModal.classList.add('visible');
            }

            // Function to close the add job modal
            function closeAddJobModal() {
                addJobModal.classList.remove('visible');
            }

            // Add event listener to the "Add New Job" button
            openAddJobModalBtn.addEventListener('click', openAddJobModal);

            // Add event listener to the close button in the modal
            closeAddJobModalBtn.addEventListener('click', closeAddJobModal);

            // Add event listener to the cancel button in the modal
            cancelAddJobBtn.addEventListener('click', closeAddJobModal);

            // Optional: Close the modal if the user clicks outside the content
            addJobModal.addEventListener('click', function(event) {
                if (event.target === addJobModal) {
                    closeAddJobModal();
                }
            });
        });
    </script>
  </body>
</html>
