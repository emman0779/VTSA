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
    $skill_name = isset($_POST['skill_name']) ? trim($_POST['skill_name']) : '';

    if (!empty($skill_name)) {
        $stmt = $conn->prepare("INSERT INTO applicant_skills (applicant_id, skill_name) VALUES (?, ?)");
        $stmt->bind_param("is", $applicant_id, $skill_name);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: applicant_dashboard.php#profile");
exit();
?>