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
    $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    // Basic validation
    if ($applicant_id > 0 && !empty($new_status)) {
        // Start a transaction to ensure both updates succeed or fail together
        $conn->begin_transaction();

        try {
            // 1. Update the current status in the main applicants table
            $stmt_update = $conn->prepare("UPDATE applicants SET status = ? WHERE id = ?");
            $stmt_update->bind_param("si", $new_status, $applicant_id);
            $stmt_update->execute();
            $stmt_update->close();

            // 2. Log the status change in the history table (`applicant_status`)
            $stmt_log = $conn->prepare("INSERT INTO applicant_status (applicant_id, status) VALUES (?, ?)");
            $stmt_log->bind_param("is", $applicant_id, $new_status);
            $stmt_log->execute();
            $stmt_log->close();

            // If both queries were successful, commit the transaction
            $conn->commit();
        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
        }
    }
}

$conn->close();
// Redirect back to the HR dashboard's applicants section
header("Location: hr_dashboard.php#applicants");
exit();
?>