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

// --- Fetch All Employees ---
$result_employees = $conn->query("SELECT * FROM employees ORDER BY name ASC");
$employees_list = $result_employees->fetch_all(MYSQLI_ASSOC);
$conn->close();

// --- Generate CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=employees_records_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, [
    'Employee ID', 
    'Name', 
    'Position', 
    'Civil Status', 
    'Gender', 
    'Date of Birth', 
    'Personal Contact No.', 
    'Work Email', 
    'Personal Email', 
    'Permanent Address', 
    'Current Address', 
    'Emergency Contact Person', 
    'Relationship', 
    'Emergency Contact No.',
    'E-Signature Path'
]);

// Add data rows
foreach ($employees_list as $emp) {
    $row = [
        $emp['employee_id_number'] ?? 'N/A',
        $emp['name'],
        $emp['position'] ?? 'N/A',
        $emp['civil_status'] ?? 'N/A',
        $emp['gender'] ?? 'N/A',
        $emp['date_of_birth'] ?? 'N/A',
        $emp['personal_no'] ?? 'N/A',
        $emp['work_email'] ?? 'N/A',
        $emp['personal_email'] ?? 'N/A',
        $emp['permanent_address'] ?? 'N/A',
        $emp['current_address'] ?? 'N/A',
        $emp['contact_person'] ?? 'N/A',
        $emp['relationship'] ?? 'N/A',
        $emp['contact_number'] ?? 'N/A',
        $emp['e_signature'] ?? 'N/A'
    ];
    fputcsv($output, $row);
}

fclose($output);
exit();
?>