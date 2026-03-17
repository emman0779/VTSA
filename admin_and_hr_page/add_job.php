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
    $job_title = isset($_POST['job_title']) ? trim($_POST['job_title']) : '';
    $job_description = isset($_POST['job_description']) ? trim($_POST['job_description']) : '';

    // Validate inputs
    if (!empty($job_title) && !empty($job_description)) {
        // Prepare and execute the SQL query to insert the new job listing
        $stmt = $conn->prepare("INSERT INTO job_listing_database (job_title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $job_title, $job_description);

        if ($stmt->execute()) {
            // Redirect back to the HR dashboard with a success message
            header("Location: hr_dashboard.php?add_job_success=1#jobs");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>