<?php
session_start();

// Ensure the user is logged in as HR or Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'hr' && $_SESSION['user_type'] !== 'admin')) {
    header("Location: ../index.html");
    exit();
}

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($job_id > 0 && ($new_status === 'Open' || $new_status === 'Closed')) {
        $stmt = $conn->prepare("UPDATE job_listing_database SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $job_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: hr_dashboard.php#jobs");
exit();
?>