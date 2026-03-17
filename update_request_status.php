<?php
session_start();

// Check if user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.html");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['req_id'];
    $type = $_POST['req_type'];
    $status = $_POST['status'];

    $anchor = "requests";
    // Determine which table to update based on the request type
    $table = "";
    if ($type === 'Bond Paper') {
        $table = "request_bpaper";
    } elseif ($type === 'Other Supplies') {
        $table = "request_supplies";
    } elseif ($type === 'Conference Booking') {
        $table = "conference_bookings";
        $anchor = "conference";
    }

    if ($table && $id && $status) {
        // --- Check Current Status First ---
        // Prevents double-restoring inventory if the item is already rejected
        $stmt_check = $conn->prepare("SELECT status FROM $table WHERE id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        $current_row = $res_check->fetch_assoc();
        $current_status = $current_row['status'] ?? '';
        $stmt_check->close();

        // Only proceed with updates if the status is actually changing
        if ($current_status !== $status) {

        // --- Subtract from Inventory on Approval ---
        // If transitioning TO 'Approved' FROM a non-approved state
        if ($status === 'Approved' && $current_status !== 'Approved') {
            $deduct_qty = 0;
            $deduct_name = '';

            if ($type === 'Other Supplies') {
                $stmt_get = $conn->prepare("SELECT item_name, quantity FROM request_supplies WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $res_get = $stmt_get->get_result();
                if ($r = $res_get->fetch_assoc()) {
                    $deduct_qty = $r['quantity'];
                    $deduct_name = $r['item_name'];
                }
                $stmt_get->close();
            } elseif ($type === 'Bond Paper') {
                $stmt_get = $conn->prepare("SELECT paper_size, quantity FROM request_bpaper WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $res_get = $stmt_get->get_result();
                if ($r = $res_get->fetch_assoc()) {
                    $deduct_qty = $r['quantity'];
                    $deduct_name = $r['paper_size'] . '%'; 
                }
                $stmt_get->close();
            }

            if ($deduct_qty > 0 && !empty($deduct_name)) {
                $operator = ($type === 'Bond Paper') ? 'LIKE' : '=';
                $stmt_deduct = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE name $operator ?");
                $stmt_deduct->bind_param("is", $deduct_qty, $deduct_name);
                $stmt_deduct->execute();
                $stmt_deduct->close();
            }
        }

        // --- Restore Inventory on Rejection ---
        // If transitioning TO 'Rejected' FROM a non-rejected state (like Pending or Approved)
        if ($status === 'Rejected' && $current_status !== 'Rejected') {
            $restore_qty = 0;
            $restore_name = '';

            if ($type === 'Other Supplies') {
                $stmt_get = $conn->prepare("SELECT item_name, quantity FROM request_supplies WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $res_get = $stmt_get->get_result();
                if ($r = $res_get->fetch_assoc()) {
                    $restore_qty = $r['quantity'];
                    $restore_name = $r['item_name'];
                }
                $stmt_get->close();
            } elseif ($type === 'Bond Paper') {
                $stmt_get = $conn->prepare("SELECT paper_size, quantity FROM request_bpaper WHERE id = ?");
                $stmt_get->bind_param("i", $id);
                $stmt_get->execute();
                $res_get = $stmt_get->get_result();
                if ($r = $res_get->fetch_assoc()) {
                    $restore_qty = $r['quantity'];
                    // Try to match partial name for Bond Paper (e.g. "A4" -> "A4 Bond Paper")
                    $restore_name = $r['paper_size'] . '%'; 
                }
                $stmt_get->close();
            }

            if ($restore_qty > 0 && !empty($restore_name)) {
                // Use LIKE for Bond Paper matching, or exact match for supplies
                $operator = ($type === 'Bond Paper') ? 'LIKE' : '=';
                $stmt_restore = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE name $operator ?");
                $stmt_restore->bind_param("is", $restore_qty, $restore_name);
                $stmt_restore->execute();
                $stmt_restore->close();
            }
        }

        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
        }
    }
}

$conn->close();
// Redirect back to the admin dashboard
header("Location: admin_and_hr_page/admin_dashboard.php#" . $anchor);
exit();
?>