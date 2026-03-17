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

// Count pending conference bookings
$pendingConferenceCount = 0;
foreach ($conferenceBookings as $booking) {
    if (strtolower($booking['status']) === 'pending') {
        $pendingConferenceCount++;
    }
}

// --- Fetch Supply Requests ---
$allRequests = [];
$pendingRequestCount = 0;

// Track totals for charts
$totalBondPaperApproved = 0;
$totalOtherSuppliesApproved = 0;

// 1. Bond Paper Requests
$sql_paper = "SELECT r.id, r.date_time_requested, 'Bond Paper' as request_type, r.paper_size as item_name, r.quantity, e.name as requestor_name, r.department, r.status, e.e_signature
              FROM request_bpaper r 
              JOIN employees e ON r.employee_id = e.id";
$res_paper = $conn->query($sql_paper);
if ($res_paper) {
    while ($row = $res_paper->fetch_assoc()) {
        $allRequests[] = $row;
        if (strtolower($row['status']) === 'approved') {
            $totalBondPaperApproved += (int)$row['quantity'];
        }
        if (strtolower($row['status']) === 'pending') $pendingRequestCount++;
    }
}

// 2. Other Supplies Requests
$sql_supplies = "SELECT r.id, r.date_time_requested, 'Other Supplies' as request_type, r.item_name, r.quantity, e.name as requestor_name, r.department, r.status, e.e_signature
                 FROM request_supplies r 
                 JOIN employees e ON r.employee_id = e.id";
$res_supplies = $conn->query($sql_supplies);
if ($res_supplies) {
    while ($row = $res_supplies->fetch_assoc()) {
        $allRequests[] = $row;
        if (strtolower($row['status']) === 'approved') {
            $totalOtherSuppliesApproved += (int)$row['quantity'];
        }
        if (strtolower($row['status']) === 'pending') $pendingRequestCount++;
    }
}

// Sort requests by date (newest first)
usort($allRequests, function($a, $b) {
    return strtotime($b['date_time_requested']) <=> strtotime($a['date_time_requested']);
});

// --- Fetch Inventory ---
$inventoryItems = [];
$totalBondPaperInStock = 0;
$totalOtherSuppliesInStock = 0;
$sql_inv = "SELECT * FROM inventory ORDER BY id DESC";
// Suppress error if table doesn't exist yet (will be created by add_inventory.php)
if ($result_inv = $conn->query($sql_inv)) {
    while ($row = $result_inv->fetch_assoc()) {
        $inventoryItems[] = $row;
        if ($row['category'] === 'Bond Paper') {
            $totalBondPaperInStock += (int)$row['quantity'];
        } else {
            $totalOtherSuppliesInStock += (int)$row['quantity'];
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - VTSA System</title>
    <link rel="stylesheet" href="hr_dashboard.css?v=<?php echo time(); ?>" />
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
          <li>
            <a href="#inventory" class="nav-link"
              ><i class="fas fa-warehouse"></i> <span>Inventory</span></a
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
                <h4>Pending Bookings</h4>
                <p><?php echo $pendingConferenceCount; ?></p>
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
            <button id="export-requests-btn" class="btn btn-primary">
              <i class="fas fa-file-export"></i> Export to Excel
            </button>
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
            <button id="export-conference-btn" class="btn btn-primary">
              <i class="fas fa-file-export"></i> Export to Excel
            </button>
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
                        <?php 
                          $statusClass = 'status-pending';
                          $s = strtolower($booking['status']);
                          if ($s === 'approved') $statusClass = 'status-hired';
                          elseif ($s === 'rejected' || $s === 'cancelled') $statusClass = 'status-closed';
                        ?>
                        <span class="status-pill <?php echo $statusClass; ?>"><?php echo htmlspecialchars($booking['status']); ?></span>
                      </td>
                      <td>
                        <form action="../update_request_status.php" method="POST" style="display:inline;">
                            <input type="hidden" name="req_id" value="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="req_type" value="Conference Booking">
                            <input type="hidden" name="status" value="Approved">
                            <button type="submit" class="action-btn view-btn" title="Approve"><i class="fas fa-check"></i></button>
                        </form>
                        <form action="../update_request_status.php" method="POST" style="display:inline;">
                            <input type="hidden" name="req_id" value="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="req_type" value="Conference Booking">
                            <input type="hidden" name="status" value="Rejected">
                            <button type="submit" class="action-btn delete-btn" title="Reject"><i class="fas fa-times"></i></button>
                        </form>
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

        <!-- Inventory Section -->
        <section id="inventory" class="dashboard-section">
          <div class="section-header">
            <h1>Inventory Management</h1>
            <button id="add-inventory-btn" class="btn btn-primary">
              <i class="fas fa-plus"></i> Add New Item
            </button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Category</th>
                  <th>Stock Level</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Last Updated</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($inventoryItems)): 
                    foreach ($inventoryItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td>
                          <?php 
                            $statusClass = ($item['quantity'] > 0) ? 'status-open' : 'status-closed';
                            $statusText = ($item['quantity'] > 0) ? 'In Stock' : 'Out of Stock';
                          ?>
                          <span class="status-pill <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($item['last_updated'])); ?></td>
                        <td>
                            <button class="action-btn edit-btn edit-inventory-btn" 
                                    data-id="<?php echo $item['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                    data-category="<?php echo htmlspecialchars($item['category']); ?>"
                                    data-quantity="<?php echo htmlspecialchars($item['quantity']); ?>"
                                    data-unit="<?php echo htmlspecialchars($item['unit']); ?>"
                                    title="Edit Item">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="delete_inventory.php" method="POST" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="action-btn delete-btn" title="Delete Item" onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.');">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; 
                else: ?>
                    <tr><td colspan="7" style="text-align: center;">No inventory items found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
    <!-- Export Requests Confirmation Modal -->
    <div id="export-requests-modal" class="modal-overlay">
      <div class="modal-content">
        <h3>Export Supply Requests</h3>
        <p>Do you want to continue and export all supply requests to an Excel file?</p>
        <div class="modal-actions">
          <a href="export_requests.php" id="confirm-export-requests" class="btn btn-primary" style="text-decoration: none;">Continue</a>
          <button type="button" id="cancel-export-requests" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Export Conference Confirmation Modal -->
    <div id="export-conference-modal" class="modal-overlay">
      <div class="modal-content">
        <h3>Export Conference Bookings</h3>
        <p>Do you want to continue and export all conference bookings to an Excel file?</p>
        <div class="modal-actions">
          <a href="export_conference.php" id="confirm-export-conference" class="btn btn-primary" style="text-decoration: none;">Continue</a>
          <button type="button" id="cancel-export-conference" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Add Inventory Modal -->
    <div id="add-inventory-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="section-header" style="margin-bottom: 20px;">
            <h3>Add Inventory Item</h3>
            <button type="button" id="close-inventory-modal" class="modal-close-btn" style="float: right;">&times;</button>
        </div>
        <form action="add_inventory.php" method="POST">
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" id="item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Bond Paper">Bond Paper</option>
                    <option value="Office Supplies">Office Supplies</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Quantity</label>
                <input type="number" id="stock_quantity" name="quantity" min="0" required>
            </div>
            <div class="form-group">
                <label for="unit">Unit</label>
                <input type="text" id="unit" name="unit" placeholder="e.g. pcs, boxes, reams" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Add Item</button>
                <button type="button" id="cancel-inventory" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
      </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div id="edit-inventory-modal" class="modal-overlay">
      <div class="modal-content">
        <div class="section-header" style="margin-bottom: 20px;">
            <h3>Edit Inventory Item</h3>
            <button type="button" id="close-edit-inventory-modal" class="modal-close-btn" style="float: right;">&times;</button>
        </div>
        <form action="update_inventory.php" method="POST">
            <input type="hidden" id="edit_item_id" name="item_id">
            <div class="form-group">
                <label for="edit_item_name">Item Name</label>
                <input type="text" id="edit_item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="edit_category">Category</label>
                <select id="edit_category" name="category" required>
                    <option value="Bond Paper">Bond Paper</option>
                    <option value="Office Supplies">Office Supplies</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_stock_quantity">Quantity</label>
                <input type="number" id="edit_stock_quantity" name="quantity" min="0" required>
            </div>
            <div class="form-group">
                <label for="edit_unit">Unit</label>
                <input type="text" id="edit_unit" name="unit" placeholder="e.g. pcs, boxes, reams" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" id="cancel-edit-inventory" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
      </div>
    </div>

    <script>
      // Pass dynamic data from PHP to JavaScript
      window.supplyChartData = {
        bondPaper: {
          requested: <?php echo $totalBondPaperApproved; ?>,
          remaining: <?php echo $totalBondPaperInStock; ?>
        },
        otherSupplies: {
          requested: <?php echo $totalOtherSuppliesApproved; ?>,
          remaining: <?php echo $totalOtherSuppliesInStock; ?>
        }
      };
    </script>
    <script src="admin_dashboard.js?v=<?php echo time(); ?>"></script>
  </body>
</html>
