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

// --- Fetch All Conference Bookings ---
$bookings = [];
$sql = "SELECT cb.*, e.name AS employee_name, e.employee_id_number 
        FROM conference_bookings cb 
        JOIN employees e ON cb.employee_id = e.id 
        ORDER BY cb.booking_date ASC, cb.start_time ASC";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}
$conn->close();

// --- Generate CSV ---
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=conference_bookings_'.date('Y-m-d').'.csv');

$output = fopen('php://output', 'w');

// Add header row
fputcsv($output, ['Booking Date', 'Start Time', 'End Time', 'Department', 'Purpose', 'Participants', 'Booked By', 'Employee ID Number']);

// Add data rows
foreach ($bookings as $b) {
    $row = [
        $b['booking_date'],
        date('h:i A', strtotime($b['start_time'])),
        date('h:i A', strtotime($b['end_time'])),
        $b['department'],
        $b['purpose'],
        $b['participants'],
        $b['employee_name'],
        $b['employee_id_number'] ?? 'N/A'
    ];
    fputcsv($output, $row);
}

fclose($output);
exit();
?>