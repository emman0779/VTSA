<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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

// Fetch conference bookings
$conferenceBookings = [];
$sql_conf = "SELECT cb.*, e.name AS employee_name FROM conference_bookings cb JOIN employees e ON cb.employee_id = e.id ORDER BY cb.booking_date ASC, cb.start_time ASC";
$result_conf = $conn->query($sql_conf);
if ($result_conf) {
    while ($row = $result_conf->fetch_assoc()) {
        $conferenceBookings[] = $row;
    }
}

// --- Fetch Supply Requests ---
$allRequests = [];
$pendingRequestCount = 0;

// 1. Bond Paper Requests
$sql_paper = "SELECT r.id, r.date_time_requested, 'Bond Paper' as request_type, r.paper_size as item_name, r.quantity, e.name as requestor_name, r.department, r.status 
              FROM request_bpaper r 
              JOIN employees e ON r.employee_id = e.id";
$res_paper = $conn->query($sql_paper);
if ($res_paper) {
    while ($row = $res_paper->fetch_assoc()) {
        $allRequests[] = $row;
        if (strtolower($row['status']) === 'pending') $pendingRequestCount++;
    }
}

// 2. Other Supplies Requests
$sql_supplies = "SELECT r.id, r.date_time_requested, 'Other Supplies' as request_type, r.item_name, r.quantity, e.name as requestor_name, r.department, r.status 
                 FROM request_supplies r 
                 JOIN employees e ON r.employee_id = e.id";
$res_supplies = $conn->query($sql_supplies);
if ($res_supplies) {
    while ($row = $res_supplies->fetch_assoc()) {
        $allRequests[] = $row;
        if (strtolower($row['status']) === 'pending') $pendingRequestCount++;
    }
}

// Sort requests by date (newest first)
usort($allRequests, function($a, $b) {
    return strtotime($b['date_time_requested']) <=> strtotime($a['date_time_requested']);
});
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - VTSA System</title>
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
        <div class="sidebar-title">Admin Panel</div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <a href="#dashboard" class="nav-link active"
              ><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a
            >
          </li>
          <li>
            <a href="#requests" class="nav-link"
              ><i class="fas fa-box-open"></i> <span>Requests</span></a
            >
          </li>
          <li>
            <a href="#conference" class="nav-link"
              ><i class="fas fa-calendar-alt"></i>
              <span>Conference Hall</span></a
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
        <div class="top-bar-title">VTSA Management System</div>
      </header>
      <main class="main-content">
        <!-- Dashboard Overview Section -->
        <section id="dashboard" class="dashboard-section active-section">
          <h1>Dashboard Overview</h1>
          <div class="stats-cards">
            <div class="card">
              <div class="card-icon"><i class="fas fa-box-open"></i></div>
              <div class="card-info">
                <h4>Pending Requests</h4>
                <p><?php echo $pendingRequestCount; ?></p>
              </div>
            </div>
            <div class="card">
              <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
              <div class="card-info">
                <h4>Hall Bookings</h4>
                <p>3</p>
              </div>
            </div>
          </div>
          <div class="charts-grid">
            <div class="chart-container">
              <canvas id="bondPaperChart"></canvas>
            </div>
            <div class="chart-container">
              <canvas id="otherSuppliesChart"></canvas>
            </div>
          </div>
        </section>

        <!-- Requests Section -->
        <section id="requests" class="dashboard-section">
          <div class="section-header">
            <h1>Manage Supply Requests</h1>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Request Type</th>
                  <th>Item / Size</th>
                  <th>Quantity</th>
                  <th>Requestor / Dept</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($allRequests)): ?>
                  <?php foreach ($allRequests as $req): ?>
                    <tr>
                      <td><?php echo date('M j, Y', strtotime($req['date_time_requested'])); ?></td>
                      <td><?php echo htmlspecialchars($req['request_type']); ?></td>
                      <td><?php echo htmlspecialchars($req['item_name']); ?></td>
                      <td>
                        <?php 
                          echo htmlspecialchars($req['quantity']); 
                          if ($req['request_type'] === 'Bond Paper') echo ' Reams';
                        ?>
                      </td>
                      <td><?php echo htmlspecialchars($req['requestor_name']) . ' / ' . htmlspecialchars($req['department']); ?></td>
                      <td>
                        <?php 
                          $statusClass = 'status-pending'; // Default
                          $s = strtolower($req['status']);
                          if ($s === 'approved') $statusClass = 'status-hired';
                          elseif ($s === 'rejected' || $s === 'cancelled') $statusClass = 'status-closed';
                        ?>
                        <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($req['status']); ?></span>
                      </td>
                      <td>
                        <!-- Approve Form -->
                        <form action="../update_request_status.php" method="POST" style="display:inline;">
                            <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="req_type" value="<?php echo htmlspecialchars($req['request_type']); ?>">
                            <input type="hidden" name="status" value="Approved">
                            <button type="submit" class="action-btn view-btn" title="Approve"><i class="fas fa-check"></i></button>
                        </form>
                        <!-- Reject Form -->
                        <form action="../update_request_status.php" method="POST" style="display:inline;">
                            <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="req_type" value="<?php echo htmlspecialchars($req['request_type']); ?>">
                            <input type="hidden" name="status" value="Rejected">
                            <button type="submit" class="action-btn delete-btn" title="Reject"><i class="fas fa-times"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" style="text-align: center;">No requests found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Conference Hall Section -->
        <section id="conference" class="dashboard-section">
          <div class="section-header">
            <h1>Conference Hall Schedule</h1>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Department</th>
                  <th>Booked By</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($conferenceBookings)): ?>
                  <?php foreach ($conferenceBookings as $booking): ?>
                    <tr>
                      <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                      <td><?php echo date('h:i A', strtotime($booking['start_time'])); ?> - <?php echo date('h:i A', strtotime($booking['end_time'])); ?></td>
                      <td><?php echo htmlspecialchars($booking['department']); ?></td>
                      <td><?php echo htmlspecialchars($booking['employee_name']); ?></td>
                      <td>
                        <span class="status-pill <?php echo strtolower($booking['status']) === 'pending' ? 'status-pending' : 'status-hired'; ?>">
                          <?php echo htmlspecialchars($booking['status']); ?>
                        </span>
                      </td>
                      <td>
                        <button class="action-btn delete-btn">
                          <i class="fas fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" style="text-align: center;">No conference bookings found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
    <script src="admin_dashboard.js"></script>
  </body>
</html>
