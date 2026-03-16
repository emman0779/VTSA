<?php
session_start();

// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'] ?? '';
$password_input = $_POST['password'] ?? '';

if (empty($email) || empty($password_input)) {
    echo "<script>alert('Please fill in both email and password.'); window.location.href='index.html';</script>";
    exit();
}

// 1. Check Employees Table
// Note: Employees table uses 'personal_email'
$stmt = $conn->prepare("SELECT id, name, password FROM employees WHERE personal_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password_input, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_type'] = 'employee';
        // Redirect to Employee Dashboard
        header("Location: employee_page/employee_dashboard.php");
        exit();
    }
}
$stmt->close();

// 2. Check HR Table
$stmt = $conn->prepare("SELECT id, name, password FROM hr_database WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // IMPORTANT: This assumes HR user passwords are created using password_hash().
    if (password_verify($password_input, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_type'] = 'hr';
        // Redirect to HR Dashboard
        header("Location: admin_and_hr_page/hr_dashboard.php");
        exit();
    }
}
$stmt->close();

// 3. Check Admin Table
$stmt = $conn->prepare("SELECT id, name, password FROM admin_database WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password_input, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_type'] = 'admin';
        // Redirect to Admin Dashboard
        header("Location: admin_and_hr_page/admin_dashboard.php");
        exit();
    }
}
$stmt->close();

// 4. Check Applicants Table
// Note: Applicants table uses 'email'
$stmt = $conn->prepare("SELECT id, name, password FROM applicants WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password_input, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_type'] = 'applicant';
        // Redirect to Applicant Dashboard
        header("Location: applicant_page/applicant_dashboard.html");
        exit();
    }
}
$stmt->close();
$conn->close();

// 5. If no match found or password incorrect
echo "<script>alert('Invalid email or password.'); window.location.href='index.html';</script>";
?>