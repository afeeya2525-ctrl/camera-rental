<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก - ระบบเช่ากล้อง</title>
    <!-- เรียกใช้ไฟล์ตกแต่ง CSS ภายนอก -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="card">
        <h2>สมัครสมาชิก</h2>
        <form action="process_register.php" method="POST">
            <div class="form-group">
                <label>Username (ชื่อผู้ใช้):</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password (รหัสผ่าน):</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>ชื่อ-นามสกุล:</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="form-group">
                <label>เบอร์โทรศัพท์:</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Line ID:</label>
                <input type="text" name="line_id">
            </div>
            <!-- เพิ่มคลาส btn-success เพื่อให้ปุ่มเป็นสีเขียวตามที่ตั้งไว้ใน CSS -->
            <button type="submit" class="btn-success">สมัครสมาชิก</button>
        </form>
        <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
    </div>
</body>
</html>