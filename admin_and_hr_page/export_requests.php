<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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

// --- Fetch All Supply Requests ---
$allRequests = [];

// 1. Bond Paper Requests
$sql_paper = "SELECT r.id, r.date_time_requested, 'Bond Paper' as request_type, r.paper_size as item_name, r.quantity, e.name as requestor_name, r.department, r.status, e.employee_id_number
              FROM request_bpaper r 
              JOIN employees e ON r.employee_id = e.id";
$res_paper = $conn->query($sql_paper);
if ($res_paper) {
    while ($row = $res_paper->fetch_assoc()) {
        $allRequests[] = $row;
    }
}

// 2. Other Supplies Requests
$sql_supplies = "SELECT r.id, r.date_time_requested, 'Other Supplies' as request_type, r.item_name, r.quantity, e.name as requestor_name, r.department, r.status, e.employee_id_number
                 FROM request_supplies r 
                 JOIN employees e ON r.employee_id = e.id";
$res_supplies = $conn->query($sql_supplies);
if ($res_supplies) {
    while ($row = $res_supplies->fetch_assoc()) {
        $allRequests[] = $row;
    }
}

// Sort requests by date (newest first)
usort($allRequests, function($a, $b) {
    return strtotime($b['date_time_requested']) <=> strtotime($a['date_time_requested']);
});

$conn->close();

// --- Generate CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=supply_requests_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, ['Date', 'Request Type', 'Item/Size', 'Quantity', 'Requestor/Dept', 'Employee ID Number']);

// Add data rows
foreach ($allRequests as $req) {
    $row = [
        date('Y-m-d H:i:s', strtotime($req['date_time_requested'])),
        $req['request_type'],
        $req['item_name'],
        $req['quantity'],
        $req['requestor_name'] . ' / ' . $req['department'],
        $req['employee_id_number'] ?? 'N/A'
    ];
    fputcsv($output, $row);
}

fclose($output);
exit();
?>