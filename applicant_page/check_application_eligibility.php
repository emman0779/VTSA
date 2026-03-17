<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'applicant') {
    // Let the frontend handle the redirect/login flow
    echo json_encode(['status' => 'login_required']);
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

$applicant_id = $_SESSION['user_id'];
$sql = "SELECT status, date_time_applied, desired_position FROM applicants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // If they haven't applied for any position yet, they are allowed
    if (empty($row['desired_position'])) {
        echo json_encode(['status' => 'allowed']);
        exit();
    }

    $status = $row['status'] ?? 'Pending';
    $date_applied = $row['date_time_applied'];
    
    // Statuses that block new applications immediately
    $active_statuses = ['Pending', 'Pooling', 'Invited for Interview', 'Hired'];
    
    if (in_array($status, $active_statuses)) {
        echo json_encode([
            'status' => 'blocked', 
            'message' => "You currently have an active application status: '$status'. You cannot submit a new application at this time."
        ]);
    } else {
        // Enforce 3-month cooldown for all inactive statuses (Rejected, Reapply in 3 months, etc.)
        $applied_time = strtotime($date_applied);
        $reapply_time = strtotime('+3 months', $applied_time);
        
        if (time() < $reapply_time) {
            $eligible_date = date('F j, Y', $reapply_time);
            echo json_encode([
                'status' => 'blocked', 
                'message' => "You can only reapply 3 months after your last application. You will be eligible on $eligible_date."
            ]);
        } else {
            echo json_encode(['status' => 'allowed']);
        }
    }
} else {
    // Record not found
    echo json_encode(['status' => 'allowed']);
}

$stmt->close();
$conn->close();
?>