<?php
// --- Database Connection ---
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Form Data & Validation ---
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
$confirm_pass = $_POST['confirm-password'] ?? '';
$is_employee = isset($_POST['is-employee']);

if (empty($fullname) || empty($email) || empty($pass)) {
    die("Error: Please fill all required fields. <a href='register.html'>Go back</a>.");
}
if ($pass !== $confirm_pass) {
    die("Error: Passwords do not match. <a href='register.html'>Go back</a>.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format. <a href='register.html'>Go back</a>.");
}

$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// --- Determine Target Table and Check for Existing Email ---
$target_table = $is_employee ? 'employees' : 'applicants';
$email_column = $is_employee ? 'personal_email' : 'email';

$stmt = $conn->prepare("SELECT id FROM $target_table WHERE $email_column = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    die("Error: An account with this email already exists. <a href='index.html'>Please log in</a>.");
}
$stmt->close();


// --- Process Registration ---
if ($is_employee) {
    // --- EMPLOYEE REGISTRATION ---
    $position = trim($_POST['employee-position'] ?? '');
    $employee_id = trim($_POST['employee-id'] ?? '');
    $e_signature_path = null;

    // Handle e-signature upload
    if (isset($_FILES['e-signature']) && $_FILES['e-signature']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/signatures/';
        // Ensure the directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        // Sanitize filename and create a unique name to prevent overwrites
        $file_info = pathinfo($_FILES["e-signature"]["name"]);
        $file_ext = strtolower($file_info['extension']);
        $safe_filename = uniqid('sig_', true) . '.' . $file_ext;
        $target_path = $upload_dir . $safe_filename;

        // Validate file type
        $allowed_types = ['png', 'jpg', 'jpeg'];
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($_FILES["e-signature"]["tmp_name"], $target_path)) {
                $e_signature_path = $target_path;
            } else {
                error_log("Failed to move uploaded file to " . $target_path);
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO employees (name, personal_email, password, position, employee_id_number, e_signature) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullname, $email, $hashed_password, $position, $employee_id, $e_signature_path);

} else {
    // --- APPLICANT REGISTRATION ---
    $stmt = $conn->prepare("INSERT INTO applicants (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);
}

// --- Execute and Close ---
if ($stmt->execute()) {
    $user_type = $is_employee ? "Employee" : "Applicant";
    // Display a success message using the site's styling
    echo "<!DOCTYPE html><html><head><title>Registration Success</title><link rel='stylesheet' href='style.css'></head><body class='login-page'><div class='login-card'><h2>Registration Successful!</h2><p>{$user_type} account created for {$email}.</p><br><a href='index.html' class='login-btn' style='text-decoration:none;text-align:center;display:block;margin:0 auto;width:fit-content;'>Proceed to Login</a></div></body></html>";
} else {
    // Provide a generic error for the user, but log the detailed SQL error for the developer
    error_log("SQL Error: " . $stmt->error);
    die("An error occurred during registration. Please try again later. <a href='register.html'>Go back</a>.");
}

$stmt->close();
$conn->close();
?>