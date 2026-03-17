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
    $degree = isset($_POST['degree']) ? trim($_POST['degree']) : '';
    $institution = isset($_POST['institution']) ? trim($_POST['institution']) : '';
    $years = isset($_POST['years']) ? trim($_POST['years']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (!empty($degree) && !empty($institution)) {
        $stmt = $conn->prepare("INSERT INTO applicant_education (applicant_id, degree, institution, years, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $applicant_id, $degree, $institution, $years, $description);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: applicant_dashboard.php#profile");
exit();
?>