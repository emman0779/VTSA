<?php
session_start();

// Ensure the user is logged in as an applicant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'applicant') {
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

$applicant_id = $_SESSION['user_id'];
$upload_dir = '../uploads/resumes/';
$redirect_url = 'applicant_dashboard.php#profile';

// Check if a file was uploaded
if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['resume_file'];

    // --- File Validation ---
    $max_size = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $max_size) {
        // In a real app, you would set a session flash message to show an error
        header("Location: " . $redirect_url);
        exit();
    }

    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        // Handle error: invalid file type
        header("Location: " . $redirect_url);
        exit();
    }

    // --- Process Upload ---
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Get old resume path to delete it later
    $stmt_old = $conn->prepare("SELECT resume FROM applicants WHERE id = ?");
    $stmt_old->bind_param("i", $applicant_id);
    $stmt_old->execute();
    $old_resume_path = $stmt_old->get_result()->fetch_assoc()['resume'] ?? null;
    $stmt_old->close();

    $new_filename = 'resume_' . $applicant_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $stmt_update = $conn->prepare("UPDATE applicants SET resume = ? WHERE id = ?");
        $stmt_update->bind_param("si", $target_path, $applicant_id);
        $stmt_update->execute();
        $stmt_update->close();

        if ($old_resume_path && file_exists($old_resume_path)) {
            unlink($old_resume_path);
        }
    }
}

$conn->close();
header("Location: " . $redirect_url);
exit();
?>