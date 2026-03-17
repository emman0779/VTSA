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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);

    if ($job_id > 0) {
        $stmt = $conn->prepare("DELETE FROM job_listing_database WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: hr_dashboard.php#jobs");
exit();
?>