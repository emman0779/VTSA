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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['applicant_id'])) {
    $applicant_id = intval($_POST['applicant_id']);

    if ($applicant_id > 0) {
        // Start a transaction to ensure all related data is deleted or none at all
        $conn->begin_transaction();

        try {
            // 1. Delete associated Skills
            $conn->query("DELETE FROM applicant_skills WHERE applicant_id = $applicant_id");

            // 2. Delete associated Education
            $conn->query("DELETE FROM applicant_education WHERE applicant_id = $applicant_id");

            // 3. Delete associated Work Experience
            $conn->query("DELETE FROM applicant_work_exp WHERE applicant_id = $applicant_id");

            // 4. Delete Status History (if table exists based on your update logic)
            $conn->query("DELETE FROM applicant_status WHERE applicant_id = $applicant_id");

            // 5. Delete the Applicant Record
            $stmt = $conn->prepare("DELETE FROM applicants WHERE id = ?");
            $stmt->bind_param("i", $applicant_id);
            $stmt->execute();
            $stmt->close();

            // Commit the transaction
            $conn->commit();
        } catch (mysqli_sql_exception $exception) {
            // If error, rollback changes
            $conn->rollback();
        }
    }
}

$conn->close();
header("Location: hr_dashboard.php#applicants");
exit();
?>