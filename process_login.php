<?php
session_start();
require_once 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // เพิ่มการดึงคอลัมน์ role ออกมาจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, password, fullname, phone, line_id, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // บันทึกข้อมูลลง Session
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['phone']    = $row['phone'];
            $_SESSION['line_id']  = $row['line_id'];
            $_SESSION['role']     = $row['role']; // บันทึกสิทธิ์ผู้ใช้งาน (user หรือ admin)

            // แยกการนำทางตามประเภทผู้ใช้
            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php"); // ถ้าเป็น Admin ให้ไปหน้าหลังบ้าน
            } else {
                header("Location: index.php"); // ถ้าเป็น User ทั่วไป ให้ไปหน้าแรก
            }
            exit();
        }
    }

    echo "<script>alert('Username หรือ Password ไม่ถูกต้อง'); window.history.back();</script>";
    $stmt->close();
}
?>