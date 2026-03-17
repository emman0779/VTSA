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

// --- Update Data ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['item_id'];
    $name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'];
    
    // Determine status based on quantity
    $status = ($quantity > 0) ? 'In Stock' : 'Out of Stock';

    $stmt = $conn->prepare("UPDATE inventory SET name = ?, category = ?, quantity = ?, unit = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssissi", $name, $category, $quantity, $unit, $status, $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: admin_dashboard.php#inventory");
exit();
?>