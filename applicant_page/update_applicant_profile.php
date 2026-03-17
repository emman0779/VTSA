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
    
    // Fetch current desired_position
    $sql_fetch = "SELECT desired_position FROM applicants WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $applicant_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $current = $result->fetch_assoc();
    $stmt_fetch->close();
    
    $was_empty = empty($current['desired_position']);
    
    // Sanitize and retrieve inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $desired_position = isset($_POST['desired_position']) ? trim($_POST['desired_position']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $source_of_hiring = isset($_POST['source_of_hiring']) ? trim($_POST['source_of_hiring']) : '';

    // Update Query
    $sql = "UPDATE applicants SET name = ?, desired_position = ?, gender = ?, source_of_hiring = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $name, $desired_position, $gender, $source_of_hiring, $phone, $address, $applicant_id);
    
    $stmt->execute();
    $stmt->close();
    
    // Set session message if applied
    if ($was_empty && !empty($desired_position)) {
        $_SESSION['notification'] = 'Applied successfully!';
    }
}

$conn->close();
// Redirect back to the profile section
header("Location: applicant_dashboard.php#profile");
exit();
?>