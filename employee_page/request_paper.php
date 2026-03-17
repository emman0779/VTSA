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

// --- Get Form Data ---
$employee_id = $_SESSION['user_id'];
$paper_size = $_POST['paper_size'] ?? '';
$quantity = $_POST['paper_quantity'] ?? 0;
$department = $_POST['paper_department'] ?? '';
$status = 'Pending'; // Default status

if (empty($paper_size) || empty($quantity) || empty($department)) {
    echo "<script>alert('All fields are required.'); window.location.href='employee_dashboard.php#request';</script>";
    exit();
}

// Prevent new requests when there is already a pending request
$pendingCount = 0;

$pendingCheckStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM request_bpaper WHERE employee_id = ? AND status = 'Pending'");
$pendingCheckStmt->bind_param("i", $employee_id);
$pendingCheckStmt->execute();
$pendingCheckResult = $pendingCheckStmt->get_result();
$pendingCount += $pendingCheckResult->fetch_assoc()['cnt'] ?? 0;
$pendingCheckStmt->close();

$pendingCheckStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM request_supplies WHERE employee_id = ? AND status = 'Pending'");
$pendingCheckStmt->bind_param("i", $employee_id);
$pendingCheckStmt->execute();
$pendingCheckResult = $pendingCheckStmt->get_result();
$pendingCount += $pendingCheckResult->fetch_assoc()['cnt'] ?? 0;
$pendingCheckStmt->close();

if ($pendingCount > 0) {
    echo "<script>alert('You already have a pending request. Please wait for it to be processed before submitting another.'); window.location.href='employee_dashboard.php#request';</script>";
    exit();
}

// --- Check Inventory Stock Level (Bond Paper) ---
// We search for an inventory item that starts with the paper size (e.g., "A4" matches "A4 Bond Paper")
$search_term = $paper_size . "%";
$stmt_check = $conn->prepare("SELECT id, name, quantity FROM inventory WHERE category = 'Bond Paper' AND name LIKE ? LIMIT 1");
$stmt_check->bind_param("s", $search_term);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
$stock_item = $res_check->fetch_assoc();
$stmt_check->close();

if (!$stock_item || $stock_item['quantity'] < $quantity) {
    echo "<script>alert('Insufficient stock for " . htmlspecialchars($paper_size) . " Bond Paper.'); window.location.href='employee_dashboard.php#request';</script>";
    exit();
}

$stmt = $conn->prepare("INSERT INTO request_bpaper (employee_id, paper_size, quantity, department, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isiss", $employee_id, $paper_size, $quantity, $department, $status);

if ($stmt->execute()) {
    echo "<script>alert('Bond paper request submitted successfully!'); window.location.href='employee_dashboard.php#request';</script>";
} else {
    echo "<script>alert('Error submitting request: " . $stmt->error . "'); window.location.href='employee_dashboard.php#request';</script>";
}

$stmt->close();
$conn->close();
?>