<?php
// --- Database Connection ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Update HR Password ---
$new_password = '@VTSA_HR2026';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$email = 'hr@vtsa.com';

$stmt = $conn->prepare("UPDATE hr_database SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    echo "Success! Password for HR user ($email) has been updated to: $new_password";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>