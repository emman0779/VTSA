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

// --- Update Admin Password ---
$new_password = '@ADMINvtsa_2026';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$email = 'admin@vtsa.com';

$stmt = $conn->prepare("UPDATE admin_database SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()) {
    echo "Success! Password for Admin user ($email) has been updated to: $new_password";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>