<?php
session_start();

// Check if user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.html");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['req_id'];
    $type = $_POST['req_type'];
    $status = $_POST['status'];

    $anchor = "requests";
    // Determine which table to update based on the request type
    $table = "";
    if ($type === 'Bond Paper') {
        $table = "request_bpaper";
    } elseif ($type === 'Other Supplies') {
        $table = "request_supplies";
    } elseif ($type === 'Conference Booking') {
        $table = "conference_bookings";
        $anchor = "conference";
    }

    if ($table && $id && $status) {
        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
// Redirect back to the admin dashboard
header("Location: admin_and_hr_page/admin_dashboard.php#" . $anchor);
exit();
?>