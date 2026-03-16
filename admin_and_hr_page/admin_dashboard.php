<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.html");
    exit();
}
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
                <p>12</p>
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
                <tr>
                  <td>Mar 12, 2026</td>
                  <td>Bond Paper</td>
                  <td>A4</td>
                  <td>5 Reams</td>
                  <td>HR Department</td>
                  <td>
                    <span class="status-pill status-pending">Pending</span>
                  </td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>Mar 11, 2026</td>
                  <td>Other Supplies</td>
                  <td>Whiteboard Markers</td>
                  <td>1 Box</td>
                  <td>John Doe</td>
                  <td>
                    <span class="status-pill status-hired">Approved</span>
                  </td>
                  <td>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
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
                  <th>Activity</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Mar 15, 2026</td>
                  <td>09:00 AM - 11:00 AM</td>
                  <td>Marketing</td>
                  <td>Quarterly Planning</td>
                  <td>
                    <span class="status-pill status-hired">Confirmed</span>
                  </td>
                  <td>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>Mar 16, 2026</td>
                  <td>01:00 PM - 03:00 PM</td>
                  <td>Engineering</td>
                  <td>Safety Training</td>
                  <td>
                    <span class="status-pill status-pending">Pending</span>
                  </td>
                  <td>
                    <button class="action-btn view-btn">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn delete-btn">
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
    <script src="admin_dashboard.js"></script>
  </body>
</html>
