<?php
session_start();

// Ensure the user is logged in as an applicant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'applicant') {
    // Redirect to home/login if not logged in
    header("Location: ../index.php");
    exit();
}

// Check for notification
$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch open job listings
$open_jobs = [];
$sql_jobs = "SELECT job_title FROM job_listing_database WHERE status = 'Open' ORDER BY job_title ASC";
$result_jobs = $conn->query($sql_jobs);
while($row = $result_jobs->fetch_assoc()) {
    $open_jobs[] = $row['job_title'];
}

$applicant_id = $_SESSION['user_id'];

// 1. Fetch Applicant Details
$sql = "SELECT * FROM applicants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$applicant_result = $stmt->get_result();
$applicant = $applicant_result->fetch_assoc();
$stmt->close();

if (!$applicant) {
    echo "Applicant data not found.";
    exit();
}

// 2. Fetch Skills
$skills = [];
$sql_skills = "SELECT * FROM applicant_skills WHERE applicant_id = ?";
$stmt = $conn->prepare($sql_skills);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result_skills = $stmt->get_result();
while ($row = $result_skills->fetch_assoc()) {
    $skills[] = $row;
}
$stmt->close();

// 3. Fetch Education
$education = [];
$sql_edu = "SELECT * FROM applicant_education WHERE applicant_id = ?";
$stmt = $conn->prepare($sql_edu);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result_edu = $stmt->get_result();
while ($row = $result_edu->fetch_assoc()) {
    $education[] = $row;
}
$stmt->close();

// 4. Fetch Work Experience
$work_exp = [];
$sql_work = "SELECT * FROM applicant_work_exp WHERE applicant_id = ?";
$stmt = $conn->prepare($sql_work);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result_work = $stmt->get_result();
while ($row = $result_work->fetch_assoc()) {
    $work_exp[] = $row;
}
$stmt->close();

$conn->close();

// Prepare variables for display
$profile_name = htmlspecialchars($applicant['name']);
$profile_role = !empty($applicant['desired_position']) ? htmlspecialchars($applicant['desired_position']) : "Applicant";
$profile_email = htmlspecialchars($applicant['email']);
$profile_phone = !empty($applicant['phone']) ? htmlspecialchars($applicant['phone']) : "Not provided";
$profile_address = !empty($applicant['address']) ? htmlspecialchars($applicant['address']) : "Not provided";

// Profile Picture Logic
$profile_pic = !empty($applicant['profile_pic']) ? htmlspecialchars($applicant['profile_pic']) : "https://ui-avatars.com/api/?name=" . urlencode($profile_name) . "&background=203864&color=fff&size=128";

// Resume Logic
$resume_filename = !empty($applicant['resume']) ? basename($applicant['resume']) : "No resume uploaded";
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="applicant_dashboard.css?v=<?php echo time(); ?>" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <title>Applicant Dashboard</title>
  </head>
  <body>
    <aside class="sidebar">
      <div class="sidebar-header">
        <a href="index.php" class="logo">
          <img src="../applicant_page/images/vtsa_white.png" alt="VTSA Logo" />
        </a>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <a href="index.php"
              ><i class="fas fa-home"></i> <span>Home</span></a
            >
          </li>
          <li>
            <a href="#applied" class="nav-link"
              ><i class="fas fa-check-square"></i> <span>Applied</span></a
            >
          </li>
          <li>
            <a href="#status" class="nav-link"
              ><i class="fas fa-tasks"></i> <span>Status</span></a
            >
          </li>
          <li>
            <a href="#profile" class="nav-link"
              ><i class="fas fa-user"></i> <span>Profile</span></a
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

    <!-- Main Content Area -->
    <main class="main-content">
      <?php if ($notification): ?>
        <div class="notification success">
          <?php echo htmlspecialchars($notification); ?>
        </div>
      <?php endif; ?>
      <!-- Applied Section (Static for now) -->
      <section id="applied" class="dashboard-section">
        <header class="page-header">
          <h1>My Applications</h1>
          <p>Track the status of your job applications</p>
        </header>
        <div class="applications-grid">
            <?php if (!empty($applicant['desired_position'])): ?>
                <div class="job-card">
                    <div class="card-header">
                        <div>
                            <h3 class="role-title"><?php echo htmlspecialchars($applicant['desired_position']); ?></h3>
                            <p class="company-name">VTSA International Inc.</p>
                        </div>
                        <?php
                            $status = $applicant['status'] ?? 'Pending';
                            $status_class = 'status-pending'; // default
                            if ($status === 'Hired') {
                                $status_class = 'status-hired';
                            } elseif ($status === 'Invited for Interview') {
                                $status_class = 'status-interview';
                            } elseif ($status === 'Reapply in 3 months' || $status === 'Rejected') {
                                $status_class = 'status-rejected';
                            } elseif ($status === 'Pooling') {
                                $status_class = 'status-interview'; // Use interview color for pooling
                            }
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                    </div>
                    <div class="card-details">
                        <div class="detail-row">
                            <i class="far fa-calendar-alt"></i>
                            <span>Applied: <?php echo date('F j, Y', strtotime($applicant['date_time_applied'])); ?></span>
                        </div>
                    </div>
                    <a href="#status" class="view-btn nav-link">View Details</a>
                </div>
            <?php else: ?>
                <p>You have not applied yet.</p>
            <?php endif; ?>
        </div>
      </section>

      <!-- Status Section (Static for now) -->
      <section id="status" class="dashboard-section">
        <header class="page-header">
          <h1>Application Progress</h1>
          <p>Detailed tracking of your ongoing applications</p>
        </header>
        <div class="status-container">
             <?php if (!empty($applicant['desired_position'])):
                $status = $applicant['status'] ?? 'Pending';
                
                // Define step completion
                $applied_class = 'completed';
                $review_class = ($status !== 'Pending') ? 'completed' : '';
                $interview_class = ($status === 'Invited for Interview' || $status === 'Hired') ? 'completed' : '';
                $offer_class = ($status === 'Hired') ? 'completed' : '';

                if ($status === 'Pooling') $review_class = 'active';
                if ($status === 'Invited for Interview') $interview_class = 'active';
                if ($status === 'Hired') $offer_class = 'active';
                
                // Define detailed status messages based on HR update
                $status_message = "Your application is currently under review by our recruitment team.";
                if ($status === 'Pooling') {
                    $status_message = "Your profile matches our requirements and has been placed in our talent pool. We will contact you when a slot opens.";
                } elseif ($status === 'Invited for Interview') {
                    $status_message = "Congratulations! You have been shortlisted for an interview. Please check your email for the schedule and further instructions.";
                } elseif ($status === 'Hired') {
                    $status_message = "Congratulations! You have been selected for the position. Welcome to VTSA International Inc.";
                }
                
                // Handle rejection status
                if ($status === 'Reapply in 3 months' || $status === 'Rejected') {
            ?>
                <div class="status-card">
                    <div class="status-header">
                      <div>
                        <h3 class="role-title"><?php echo htmlspecialchars($applicant['desired_position']); ?></h3>
                        <p class="company-name">VTSA International Inc.</p>
                      </div>
                    </div>
                    <div class="activity-log" style="border-left-color: var(--rejected-red);">
                      <h4>Application Update</h4>
                      <p>Thank you for your interest. After careful consideration, we have decided to move forward with other candidates at this time. We encourage you to check for future openings.</p>
                    </div>
                </div>
            <?php } else { ?>
                <div class="status-card">
                    <div class="status-header">
                      <div>
                        <h3 class="role-title"><?php echo htmlspecialchars($applicant['desired_position']); ?></h3>
                        <p class="company-name">VTSA International Inc.</p>
                      </div>
                    </div>

                    <!-- Progress Stepper -->
                    <div class="progress-track">
                      <div class="step <?php echo $applied_class; ?>">
                        <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                        <p>Applied</p>
                        <span class="date"><?php echo date('M d', strtotime($applicant['date_time_applied'])); ?></span>
                      </div>
                      <div class="step <?php echo $review_class; ?>">
                        <div class="step-icon"><i class="fas fa-search"></i></div>
                        <p>Review</p>
                      </div>
                      <div class="step <?php echo $interview_class; ?>">
                        <div class="step-icon"><i class="fas fa-comments"></i></div>
                        <p>Interview</p>
                      </div>
                      <div class="step <?php echo $offer_class; ?>">
                        <div class="step-icon"><i class="fas fa-briefcase"></i></div>
                        <p>Offer</p>
                      </div>
                    </div>
                    
                    <!-- Activity Log -->
                    <div class="activity-log">
                        <h4>Latest Activity</h4>
                        <div class="log-item">
                            <div class="log-dot"></div>
                            <p>
                                <strong>Status: <?php echo htmlspecialchars($status); ?></strong> - <?php echo htmlspecialchars($status_message); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php else: ?>
                <p>No active application to track.</p>
            <?php endif; ?>
        </div>
      </section>

      <!-- Profile Section (Connected to Database) -->
      <section id="profile" class="dashboard-section">
        <header class="page-header">
          <h1>My Profile</h1>
          <p>Manage your personal information and resume details</p>
        </header>

        <div class="profile-container">
          <!-- Header Card with Picture and Basic Info -->
          <div class="profile-header-card">
            <div class="profile-identity">
              <form id="pfp-upload-form" action="upload_profile_pic.php" method="POST" enctype="multipart/form-data">
                <div class="profile-img-container">
                  <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" />
                  <label for="profile-pic-upload" class="edit-pic-icon">
                    <i class="fas fa-camera"></i>
                  </label>
                  <input type="file" id="profile-pic-upload" name="profile_pic_file" accept="image/*" style="display: none" />
                </div>
              </form>
              <div class="profile-info">
                <h2><?php echo $profile_name; ?></h2>
                <p class="role-tag"><?php echo $profile_role; ?></p>
                <div class="contact-row">
                  <span><i class="fas fa-envelope"></i> <?php echo $profile_email; ?></span>
                  <span><i class="fas fa-phone"></i> <?php echo $profile_phone; ?></span>
                  <span><i class="fas fa-map-marker-alt"></i> <?php echo $profile_address; ?></span>
                </div>
              </div>
            </div>
            <button type="button" class="update-btn" id="open-update-modal">
              <i class="fas fa-pen"></i> Update Info
            </button>
          </div>

          <!-- Skills Section -->
          <section class="profile-section">
            <div class="section-header">
              <h3><i class="fas fa-cogs"></i> Skills</h3>
              <button class="icon-btn" id="open-skill-modal" type="button" title="Add Skill"><i class="fas fa-plus"></i></button>
            </div>
            <div class="section-content">
              <div class="skills-list">
                <?php if (count($skills) > 0): ?>
                    <?php foreach ($skills as $skill): ?>
                        <span class="skill-badge">
                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                            <a href="delete_skill.php?id=<?php echo $skill['id']; ?>" class="skill-delete" title="Remove Skill">&times;</a>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No skills listed yet.</p>
                <?php endif; ?>
              </div>
            </div>
          </section>

          <!-- Education Section -->
          <section class="profile-section">
            <div class="section-header">
              <h3><i class="fas fa-graduation-cap"></i> Education</h3>
              <button class="icon-btn" id="open-education-modal" type="button" title="Add Education"><i class="fas fa-plus"></i></button>
            </div>
            <div class="section-content">
                <?php if (count($education) > 0): ?>
                    <?php foreach ($education as $edu): ?>
                      <div class="experience-item">
                        <a href="delete_education.php?id=<?php echo $edu['id']; ?>" class="item-delete-btn" title="Remove Entry">&times;</a>
                        <h4><?php echo htmlspecialchars($edu['degree']); ?></h4>
                        <p class="item-meta">
                          <?php echo htmlspecialchars($edu['institution']); ?> | <?php echo htmlspecialchars($edu['years']); ?>
                        </p>
                        <?php if (!empty($edu['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No education details added.</p>
                <?php endif; ?>
            </div>
          </section>

          <!-- Work Experience Section -->
          <section class="profile-section">
            <div class="section-header">
              <h3><i class="fas fa-briefcase"></i> Work Experience</h3>
              <button class="icon-btn" id="open-work-modal" type="button" title="Add Work Experience"><i class="fas fa-plus"></i></button>
            </div>
            <div class="section-content">
                <?php if (count($work_exp) > 0): ?>
                    <?php foreach ($work_exp as $exp): ?>
                      <div class="experience-item">
                        <a href="delete_work_exp.php?id=<?php echo $exp['id']; ?>" class="item-delete-btn" title="Remove Entry">&times;</a>
                        <h4><?php echo htmlspecialchars($exp['job_title']); ?></h4>
                        <p class="item-meta">
                          <?php echo htmlspecialchars($exp['company']); ?> | <?php echo htmlspecialchars($exp['years']); ?>
                        </p>
                        <?php if (!empty($exp['description'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No work experience added.</p>
                <?php endif; ?>
            </div>
          </section>

          <!-- Resume Section -->
          <section class="profile-section">
            <div class="section-header">
              <h3><i class="fas fa-file-pdf"></i> My Resume</h3>
            </div>
            <div class="section-content">
              <form id="resume-upload-form" action="upload_resume.php" method="POST" enctype="multipart/form-data">
                <div class="resume-upload-area">
                  <div class="current-resume">
                    <i class="fas fa-file-alt file-icon"></i>
                    <span class="file-name"><?php echo htmlspecialchars($resume_filename); ?></span>
                  </div>
                  <div class="resume-actions">
                    <label for="resume-upload" class="upload-btn">
                      <i class="fas fa-upload"></i> Upload New Resume
                    </label>
                    <input
                      type="file"
                      id="resume-upload"
                      name="resume_file"
                      accept=".pdf,.doc,.docx"
                      style="display: none"
                    />
                    <p class="upload-note">
                      Max file size: 5 MB. Supported formats: PDF, DOC, DOCX.
                    </p>
                  </div>
                </div>
              </form>
            </div>
          </section>
        </div>
      </section>
    </main>

    <!-- Update Info Modal -->
    <div id="update-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Update Personal Info</h3>
          <button class="modal-close-btn" id="close-update-modal">&times;</button>
        </div>
        <form action="update_applicant_profile.php" method="POST">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($applicant['name']); ?>" required />
          </div>
          <div class="form-group">
            <label for="desired_position">Position Applied</label>
            <select id="desired_position" name="desired_position">
                <option value="">-- Select a Position --</option>
                <?php
                $current_position = $applicant['desired_position'] ?? '';
                // Add all open jobs to the dropdown
                foreach ($open_jobs as $job_title) {
                    $selected = ($current_position == $job_title) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($job_title) . '" ' . $selected . '>' . htmlspecialchars($job_title) . '</option>';
                }
                // If the applicant's current position is not in the open list (i.e., it's closed), add it to the list so it remains selected.
                if (!empty($current_position) && !in_array($current_position, $open_jobs)) {
                    echo '<option value="' . htmlspecialchars($current_position) . '" selected>' . htmlspecialchars($current_position) . ' (Position no longer open)</option>';
                }
                ?>
            </select>          </div>
          <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">-- Select --</option>
              <option value="Male" <?php if (($applicant['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
              <option value="Female" <?php if (($applicant['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
            </select>
          </div>
          <div class="form-group">
            <label for="source_of_hiring">Where did you hear about the hiring?</label>
            <select id="source_of_hiring" name="source_of_hiring">
                <option value="">-- Select an option --</option>
                <option value="Jobstreet" <?php if (($applicant['source_of_hiring'] ?? '') == 'Jobstreet') echo 'selected'; ?>>Jobstreet</option>
                <option value="LinkedIn" <?php if (($applicant['source_of_hiring'] ?? '') == 'LinkedIn') echo 'selected'; ?>>LinkedIn</option>
                <option value="Facebook" <?php if (($applicant['source_of_hiring'] ?? '') == 'Facebook') echo 'selected'; ?>>Facebook</option>
                <option value="Indeed" <?php if (($applicant['source_of_hiring'] ?? '') == 'Indeed') echo 'selected'; ?>>Indeed</option>
                <option value="Company Website" <?php if (($applicant['source_of_hiring'] ?? '') == 'Company Website') echo 'selected'; ?>>Company Website</option>
                <option value="Referral" <?php if (($applicant['source_of_hiring'] ?? '') == 'Referral') echo 'selected'; ?>>Referral</option>
                <option value="Other" <?php if (($applicant['source_of_hiring'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($applicant['phone'] ?? ''); ?>" placeholder="+63 912 345 6789" />
          </div>
          <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" placeholder="Current Address"><?php echo htmlspecialchars($applicant['address'] ?? ''); ?></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" id="cancel-update">Cancel</button>
            <button type="submit" class="update-btn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Skill Modal -->
    <div id="skill-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Add New Skill</h3>
          <button class="modal-close-btn" id="close-skill-modal" type="button">&times;</button>
        </div>
        <form action="add_skill.php" method="POST">
          <div class="form-group">
            <label for="skill_name">Skill Name</label>
            <input type="text" id="skill_name" name="skill_name" placeholder="e.g. Java, Project Management, Communication" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="update-btn">Add Skill</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Education Modal -->
    <div id="education-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Add Education</h3>
          <button class="modal-close-btn" id="close-education-modal" type="button">&times;</button>
        </div>
        <form action="add_education.php" method="POST">
          <div class="form-group">
            <label for="degree">Degree / Course</label>
            <input type="text" id="degree" name="degree" placeholder="e.g. BS Information Technology" required />
          </div>
          <div class="form-group">
            <label for="institution">School / Institution</label>
            <input type="text" id="institution" name="institution" placeholder="e.g. Polytechnic University" required />
          </div>
          <div class="form-group">
            <label for="years">Years Attended</label>
            <input type="text" id="years" name="years" placeholder="e.g. 2018 - 2022" />
          </div>
          <div class="form-group">
            <label for="edu_desc">Description (Optional)</label>
            <textarea id="edu_desc" name="description" rows="3" placeholder="Awards, Thesis, or Major achievements..."></textarea>
          </div>
          <div class="modal-actions">
            <button type="submit" class="update-btn">Add Education</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Work Experience Modal -->
    <div id="work-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Add Work Experience</h3>
          <button class="modal-close-btn" id="close-work-modal" type="button">&times;</button>
        </div>
        <form action="add_work_exp.php" method="POST">
          <div class="form-group">
            <label for="job_title">Job Title</label>
            <input type="text" id="job_title" name="job_title" placeholder="e.g. Web Developer" required />
          </div>
          <div class="form-group">
            <label for="company">Company Name</label>
            <input type="text" id="company" name="company" placeholder="e.g. Tech Corp" required />
          </div>
          <div class="form-group">
            <label for="work_years">Years Active</label>
            <input type="text" id="work_years" name="years" placeholder="e.g. 2020 - Present" />
          </div>
          <div class="form-group">
            <label for="work_desc">Description (Optional)</label>
            <textarea id="work_desc" name="description" rows="3" placeholder="Key responsibilities and achievements..."></textarea>
          </div>
          <div class="modal-actions">
            <button type="submit" class="update-btn">Add Experience</button>
          </div>
        </form>
      </div>
    </div>
    <script src="applicant_dashboard.js?v=<?php echo time(); ?>"></script>
  </body>
</html>