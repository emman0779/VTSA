<?php
session_start();

// Ensure the user is logged in as an applicant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'applicant') {
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
    $applicant_id = $_SESSION['user_id'];
    $position = isset($_POST['position']) ? trim($_POST['position']) : '';

    if (!empty($position)) {
        // Update the applicant's desired_position, date_time_applied, and status
        $sql = "UPDATE applicants SET desired_position = ?, date_time_applied = NOW(), status = 'Pending' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $position, $applicant_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
// Redirect to the applied section
header("Location: applicant_dashboard.php#applied");
exit();
?>