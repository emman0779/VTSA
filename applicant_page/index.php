<?php
// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Could not connect to the job service. Please
try again later."); } $open_jobs = []; $sql = "SELECT
job_title, description FROM job_listing_database WHERE status = 'Open' ORDER BY
job_title ASC"; $result = $conn->query($sql); if ($result) { while($row =
$result->fetch_assoc()) { $open_jobs[] = $row; } } $conn->close(); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css?v=1" />
    <title>Document</title>
  </head>
  <body>
    <header class="header">
      <nav class="navbar">
        <a href="#" class="logo"
          ><img src="images/vtsa_logo.png" alt="VTSA company logo"
        /></a>
        <ul class="nav-links">
          <li><a href="index.html" class="active">Home</a></li>
          <li><a href="applicant_dashboard.php#applied">Applied</a></li>
          <li><a href="applicant_dashboard.php#status">Status</a></li>
          <li><a href="applicant_dashboard.php#profile">Profile</a></li>
        </ul>
        <a href="#" class="logo-right"
          ><img src="images/logo2.png" alt="Secondary company logo"
        /></a>
      </nav>
    </header>

    <nav class="scroll" id="navbar">
      <a href="#" class="logo"
        ><img src="images/vtsa_white.png" alt="VTSA company logo"
      /></a>
      <ul class="nav-links">
        <li><a href="index.html" class="active">Home</a></li>
        <li><a href="applicant_dashboard.php#applied">Applied</a></li>
        <li><a href="applicant_dashboard.php#status">Status</a></li>
        <li><a href="applicant_dashboard.php#profile">Profile</a></li>
      </ul>
      <a href="#" class="logo-right"
        ><img src="images/schneider_white.png" alt="Secondary company logo"
      /></a>
    </nav>

    <div class="hero">
      <aside>
        <h3>VTSA CAREERS</h3>
        <h1>JOB OPENINGS & OPPORTUNITIES</h1>
        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum dolores
          impedit architecto omnis, voluptatibus cumque officia numquam, dolore
          quibusdam blanditiis nihil ad iure molestias! Ad ipsum dolores
          praesentium rem fuga?
        </p>
        <a href="#jobs-section" class="hero-apply-button" role="button"
          >APPLY NOW!</a
        >
      </aside>
      <div class="heroImage">
        <img
          src="images/hero.png"
          alt="Illustration of a modern office environment"
        />
      </div>
    </div>
    <div class="intro">
      <div>
        <h1>BE PART OF OUR TEAM</h1>
        <p>
          Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nesciunt
          aperiam neque, veniam ipsam totam, pariatur aliquid minus
          necessitatibus repudiandae et, expedita eius nam repellendus
          voluptates beatae maiores molestias laborum laboriosam! Lorem ipsum
          dolor sit amet consectetur adipisicing elit. Ab dignissimos et
          exercitationem commodi! Dolores, quod magnam. Eaque sunt excepturi
          quis blanditiis porro, nemo nostrum sapiente animi quae, debitis nisi
          odio.
        </p>
      </div>
    </div>

    <main id="jobs-section">
      <?php if (!empty($open_jobs)): ?> <?php foreach ($open_jobs as $job): ?>
      <div class="jobCards">
        <h4><?php echo htmlspecialchars($job['job_title']); ?></h4>
        <p>
          <?php $description = htmlspecialchars($job['description']); if
          (strlen($description) > 120) { echo substr($description, 0, 120) .
          '...'; } else { echo $description; } ?>
        </p>
        <div>
          <a href="#">View Details</a>
          <a
            href="applicant_dashboard.php#applied"
            class="job-apply-button"
            role="button"
            >APPLY</a
          >
        </div>
      </div>
      <?php endforeach; ?> <?php else: ?>
      <div
        style="
          grid-column: 1 / -1;
          text-align: center;
          padding: 2rem;
          background: #fff;
          border-radius: 12px;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        "
      >
        <h3>No Open Positions At The Moment</h3>
        <p style="color: #666; margin-top: 0.5rem">
          Please check back later for new opportunities.
        </p>
      </div>
      <?php endif; ?>
    </main>

    <footer class="footer-section">
      <div class="footer-container">
        <div class="footer-about">
          <div class="footer-logos">
            <a href="#"
              ><img
                src="images/combined logo.png"
                alt="VTSA company logo"
                class="footer-logo-vtsa"
            /></a>
          </div>
          <p>
            Your partner in vertical transportation solutions, providing
            innovative and reliable elevators and components.
          </p>
          <div class="footer-contact-info">
            <h4>Contacts</h4>
            <p>+63 919 088 4006 / (02) 700 11805</p>
            <p>
              <a href="mailto:ph.schneiderlifts@vtsagroup.com"
                >ph.schneiderlifts@vtsagroup.com</a
              >
            </p>
            <p>
              <a
                href="http://www.vtsalifts.com"
                target="_blank"
                rel="noopener noreferrer"
                >www.vtsalifts.com</a
              >
            </p>
          </div>
        </div>
        <div class="footer-links-column">
          <h4>Our Offices</h4>
          <div class="footer-address">
            <p>
              <strong>Main Office</strong><br />
              25 M Glaston Tower, Ortigas East, Pasig City Pasig, Philippines
            </p>
            <p>
              <strong>North Luzon</strong><br />
              LG-08, Goshen Land Towers Upper General Luna Road, Baguio City,
              Philippines
            </p>
            <p>
              <strong>Central Luzon</strong><br />
              Marigold St. Block 104 Lot 73 Deca Clark Resort & Residences Brgy.
              Magot, Pampanga City, Philippines
            </p>
          </div>
        </div>
        <div class="footer-links-column">
          <h4 class="invisible-heading">Offices Continued</h4>
          <div class="footer-address">
            <p>
              <strong>Cebu</strong><br />
              Unit 1516, 15th Floor, BPI Cebu Corporate Center, Archbishop Reyes
              Avenue, Cebu City City, Philippines
            </p>
            <p>
              <strong>Davao</strong><br />
              Plaza De Tavera J. Camus Extension, Brgy., 9-A, Davao City,
              Philippines
            </p>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 VTSA International Inc. All Rights Reserved.</p>
      </div>
    </footer>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal-overlay">
      <div class="modal-content">
        <h3
          id="modal-title"
          style="margin-top: 0; margin-bottom: 15px; color: #203864"
        >
          Application Notice
        </h3>
        <p id="modal-message">
          You cannot apply at this time due to an existing application or
          cooldown period.
        </p>
        <div class="modal-actions">
          <button
            id="confirm-yes"
            type="button"
            style="
              padding: 8px 20px;
              cursor: pointer;
              background-color: #203864;
              color: white;
              border: none;
              border-radius: 5px;
            "
          >
            Yes
          </button>
          <button
            id="confirm-no"
            type="button"
            onclick="
              document
                .getElementById('confirmation-modal')
                .classList.remove('visible')
            "
            style="padding: 8px 20px; cursor: pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </body>
  <script src="script.js?v=2"></script>
</html>
