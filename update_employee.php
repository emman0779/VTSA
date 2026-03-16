<?php
session_start();

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

// Check if user is logged in as an employee
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    echo "<script>alert('Unauthorized access.'); window.location.href='index.html';</script>";
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Get Form Data ---
$id = $_SESSION['user_id'];
$fullname = $_POST['fullname'] ?? '';
$civil_status = $_POST['civil_status'] ?? '';
$gender = $_POST['gender'] ?? '';
$birth_date = $_POST['date_of_birth'] ?? '';
$permanent_address = $_POST['permanent_address'] ?? '';
$current_address = $_POST['current_address'] ?? '';
$is_permanent_yes = isset($_POST['is_address_permanent']) && $_POST['is_address_permanent'] === 'yes';

// If current address is same as permanent, copy it
if ($is_permanent_yes) {
    $current_address = $permanent_address;
}

$personal_no = $_POST['personal_no'] ?? '';
$personal_email = $_POST['personal_email'] ?? '';
$work_email = $_POST['work_email'] ?? '';

$contact_person = $_POST['contact_person'] ?? '';
$relationship = $_POST['relationship'] ?? '';
$contact_number = $_POST['contact_number'] ?? ''; // Emergency contact number

// --- Update Database ---
$sql = "UPDATE employees SET 
        name = ?, 
        civil_status = ?, 
        gender = ?, 
        date_of_birth = ?, 
        personal_no = ?, 
        work_email = ?, 
        personal_email = ?, 
        permanent_address = ?, 
        current_address = ?, 
        contact_person = ?, 
        relationship = ?, 
        contact_number = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssi", 
    $fullname, 
    $civil_status, 
    $gender, 
    $birth_date, 
    $personal_no, 
    $work_email, 
    $personal_email, 
    $permanent_address, 
    $current_address, 
    $contact_person, 
    $relationship, 
    $contact_number, 
    $id
);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $fullname;
    echo "<script>alert('Information updated successfully!'); window.location.href='employee_page/employee_dashboard.php';</script>";
} else {
    echo "<script>alert('Error updating record: " . $stmt->error . "'); window.location.href='employee_page/employee_dashboard.php';</script>";
}

$stmt->close();
$conn->close();
?>