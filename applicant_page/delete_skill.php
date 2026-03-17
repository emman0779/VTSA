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

if (isset($_GET['id'])) {
    $applicant_id = $_SESSION['user_id'];
    $skill_id = $_GET['id'];

    // Delete only if the skill belongs to the logged-in applicant
    $stmt = $conn->prepare("DELETE FROM applicant_skills WHERE id = ? AND applicant_id = ?");
    $stmt->bind_param("ii", $skill_id, $applicant_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: applicant_dashboard.php#profile");
exit();
?>