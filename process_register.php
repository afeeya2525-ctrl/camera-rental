<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $line_id  = trim($_POST['line_id'] ?? '');

    if (empty($username) || empty($password) || empty($fullname) || empty($phone)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน'); window.history.back();</script>";
        exit();
    }

    // เข้ารหัสรหัสผ่านเพื่อความปลอดภัย
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, phone, line_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashed_password, $fullname, $phone, $line_id);

    if ($stmt->execute()) {
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Username นี้มีผู้ใช้งานแล้ว หรือเกิดข้อผิดพลาด'); window.history.back();</script>";
    }
    $stmt->close();
}
?>
