<?php
session_start();

// Check if user is logged in as an employee
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    echo "<script>alert('Unauthorized access.'); window.location.href='../index.html';</script>";
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

// Ensure the conference_bookings table exists
$conn->query(
    "CREATE TABLE IF NOT EXISTS conference_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        department VARCHAR(100) NOT NULL,
        booking_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        purpose VARCHAR(255) DEFAULT NULL,
        participants INT DEFAULT 0,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
    )"
);

// Check if 'participants' column exists (migration for existing table)
$colCheck = $conn->query("SHOW COLUMNS FROM conference_bookings LIKE 'participants'");
if ($colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE conference_bookings ADD COLUMN participants INT DEFAULT 0 AFTER purpose");
}

// --- Get Form Data ---
$employee_id = $_SESSION['user_id'];
$department = trim($_POST['department_name'] ?? '');
$booking_date = $_POST['booking_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$purpose = trim($_POST['purpose'] ?? '');
$participants = (int)($_POST['participants'] ?? 0);
$status = 'Pending';

if (empty($department) || empty($booking_date) || empty($start_time) || empty($end_time) || $participants <= 0) {
    echo "<script>alert('All fields are required.'); window.location.href='employee_dashboard.php#conference';</script>";
    exit();
}

if (strtotime($start_time) >= strtotime($end_time)) {
    echo "<script>alert('End time must be after the start time.'); window.location.href='employee_dashboard.php#conference';</script>";
    exit();
}

// Prevent new requests when there is already a pending request (supplies or conference)
$pendingCount = 0;

$pendingCheckStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM conference_bookings WHERE employee_id = ? AND status = 'Pending'");
$pendingCheckStmt->bind_param("i", $employee_id);
$pendingCheckStmt->execute();
$pendingCheckResult = $pendingCheckStmt->get_result();
$pendingCount += $pendingCheckResult->fetch_assoc()['cnt'] ?? 0;
$pendingCheckStmt->close();

if ($pendingCount > 0) {
    echo "<script>alert('You already have a pending request. Please wait for it to be processed before submitting another.'); window.location.href='employee_dashboard.php#conference';</script>";
    exit();
}

$stmt = $conn->prepare("INSERT INTO conference_bookings (employee_id, department, booking_date, start_time, end_time, purpose, participants, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssiis", $employee_id, $department, $booking_date, $start_time, $end_time, $purpose, $participants, $status);

if ($stmt->execute()) {
    echo "<script>alert('Conference booking request submitted successfully!'); window.location.href='employee_dashboard.php#conference';</script>";
} else {
    echo "<script>alert('Error submitting booking: " . $stmt->error . "'); window.location.href='employee_dashboard.php#conference';</script>";
}

$stmt->close();
$conn->close();
?>
