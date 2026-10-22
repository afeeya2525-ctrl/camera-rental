<?php
session_start();
require_once 'db.php';

// เช็กสิทธิ์ว่าต้องเป็นแอดมินเท่านั้น
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    $tracking_number = isset($_POST['tracking_number']) ? trim($_POST['tracking_number']) : '';

    if ($action === 'approve') {
        $status = 'approved';
        $sql = "UPDATE bookings SET status = '$status', tracking_number = '$tracking_number' WHERE id = '$booking_id'";
    } elseif ($action === 'reject') {
        $status = 'rejected';
        $sql = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php?msg=success");
    } else {
        header("Location: admin_dashboard.php?msg=error");
    }
}
?>