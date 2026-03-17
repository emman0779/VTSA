<?php
session_start();

// Ensure the user is logged in as an applicant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'applicant') {
    header("Location: ../index.html");
    exit();
}

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
    $job_title = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
    $company = isset($_POST['company']) ? trim($_POST['company']) : '';
    $years = isset($_POST['years']) ? trim($_POST['years']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (!empty($job_title) && !empty($company)) {
        $stmt = $conn->prepare("INSERT INTO applicant_work_exp (applicant_id, job_title, company, years, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $applicant_id, $job_title, $company, $years, $description);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: applicant_dashboard.php#profile");
exit();
?>