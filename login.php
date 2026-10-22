<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - ระบบเช่ากล้อง</title>
    <!-- เรียกใช้ไฟล์ตกแต่ง CSS ภายนอก -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="card">
        <h2>เข้าสู่ระบบ</h2>
        <form action="process_login.php" method="POST">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
        <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a></p>
    </div>
</body>
</html>