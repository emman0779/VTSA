<?php
// fix_passwords.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vtsa_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Fix HR Password
$hr_email = 'hr@vtsa.com';
$hr_password = '@VTSA_HR2026';
$hr_hash = password_hash($hr_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE hr_database SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hr_hash, $hr_email);
if ($stmt->execute()) {
    echo "HR Password updated successfully for $hr_email<br>";
} else {
    echo "Error updating HR password: " . $conn->error . "<br>";
}
$stmt->close();

// 2. Fix Admin Password
$admin_email = 'admin@vtsa.com';
$admin_password = '@ADMINvtsa_2026';
$admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admin_database SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $admin_hash, $admin_email);
if ($stmt->execute()) {
    echo "Admin Password updated successfully for $admin_email<br>";
} else {
    echo "Error updating Admin password: " . $conn->error . "<br>";
}
$stmt->close();

$conn->close();
echo "<br>You can now log in.";
?>