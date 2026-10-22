<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, c.camera_name 
        FROM bookings b 
        JOIN cameras c ON b.camera_id = c.id 
        WHERE b.user_id = '$user_id' 
        ORDER BY b.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการจองของฉัน - Chubby Lens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

    <nav class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <span class="text-dark fw-medium">ยินดีต้อนรับคุณ, <strong class="text-primary"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong></span>
        <div>
            <a href="index.php" class="btn btn-outline-primary btn-sm me-2">หน้าจองกล้อง</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
        </div>
    </nav>