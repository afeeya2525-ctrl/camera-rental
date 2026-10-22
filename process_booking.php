<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id        = $_SESSION['user_id'];
    $camera_id      = $_POST['camera_id'] ?? 1;
    $start_date     = $_POST['start_date'] ?? '';
    $end_date       = $_POST['end_date'] ?? '';
    $add_tripod     = isset($_POST['add_tripod']) ? 1 : 0;
    $add_battery    = isset($_POST['add_battery']) ? 1 : 0;
    $refund_account = $_POST['refund_account'] ?? '';
    $note           = $_POST['note'] ?? '';

    // 1. คำนวณจำนวนวันและราคาทั้งหมด
    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);
    $days  = $start->diff($end)->days + 1;

    if ($days <= 0) {
        echo "<script>alert('วันที่เลือกไม่ถูกต้อง'); window.history.back();</script>";
        exit();
    }

    // ดึงราคาเช่าต่อวันจากตาราง cameras
    $stmt_cam = $conn->prepare("SELECT daily_rate FROM cameras WHERE id = ?");
    $stmt_cam->bind_param("i", $camera_id);
    $stmt_cam->execute();
    $res_cam = $stmt_cam->get_result()->fetch_assoc();
    $daily_rate = $res_cam ? $res_cam['daily_rate'] : 300;
    $stmt_cam->close();

    $rental_fee  = $days * $daily_rate;
    $addon_fee   = $days * (($add_tripod ? 30 : 0) + ($add_battery ? 30 : 0));
    $deposit_fee = 200; // ค่าประกัน
    $total_price = $rental_fee + $addon_fee + $deposit_fee;

    // 2. จัดการอัปโหลดไฟล์รูปภาพ
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $slip_name = '';
    if (!empty($_FILES['slip_image']['name'])) {
        $slip_name = time() . 'slip' . basename($_FILES['slip_image']['name']);
        move_uploaded_file($_FILES['slip_image']['tmp_name'], $upload_dir . $slip_name);
    }

    $id_card_name = '';
    if (!empty($_FILES['id_card_image']['name'])) {
        $id_card_name = time() . 'idcard' . basename($_FILES['id_card_image']['name']);
        move_uploaded_file($_FILES['id_card_image']['tmp_name'], $upload_dir . $id_card_name);
    }

    // 3. บันทึกข้อมูลการจองลงตาราง bookings
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, camera_id, start_date, end_date, add_tripod, add_battery, total_price, refund_account, note, slip_image, id_card_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissiidssss", $user_id, $camera_id, $start_date, $end_date, $add_tripod, $add_battery, $total_price, $refund_account, $note, $slip_name, $id_card_name);

    if ($stmt->execute()) {
        echo "<script>
                alert('บันทึกคำขอจองเรียบร้อยแล้วค่ะ! ราคารวมทั้งหมด: " . number_format($total_price, 2) . " บาท');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                window.history.back();
              </script>";
    }
    $stmt->close();
}
?>