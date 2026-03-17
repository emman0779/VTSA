<?php
session_start();

// Ensure the user is logged in as HR or Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'hr' && $_SESSION['user_type'] !== 'admin')) {
    header("Location: ../index.html");
    exit();
}

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Fetch All Applicants ---
$result_applicants = $conn->query("SELECT * FROM applicants ORDER BY date_time_applied DESC");
$applicants_list = $result_applicants->fetch_all(MYSQLI_ASSOC);
$conn->close();

// --- Generate CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=applicants_records_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, ['Date Applied', 'Name', 'Email', 'Phone', 'Address', 'Gender', 'Position Applied', 'Source of Hiring', 'Status']);

// Add data rows
foreach ($applicants_list as $app) {
    $row = [
        date('Y-m-d H:i:s', strtotime($app['date_time_applied'])),
        $app['name'],
        $app['email'],
        $app['phone'] ?? 'N/A',
        $app['address'] ?? 'N/A',
        $app['gender'] ?? 'N/A',
        $app['desired_position'] ?? 'N/A',
        $app['source_of_hiring'] ?? 'N/A',
        $app['status'] ?? 'Pending'
    ];
    fputcsv($output, $row);
}

fclose($output);
exit();
?>