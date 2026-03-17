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

// --- Create Table if it doesn't exist ---
$sql_create = "CREATE TABLE IF NOT EXISTS inventory (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 0,
    unit VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'In Stock',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($sql_create)) {
    die("Error creating table: " . $conn->error);
}

// --- Insert Data ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'];
    $status = ($quantity > 0) ? 'In Stock' : 'Out of Stock';

    $stmt = $conn->prepare("INSERT INTO inventory (name, category, quantity, unit, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $name, $category, $quantity, $unit, $status);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: admin_dashboard.php#inventory");
exit();
?>