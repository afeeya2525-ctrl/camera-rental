<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ - Chubby Lens</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Kanit -->
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background: linear-gradient(135deg, #ffe6eb 0%, #fff0f3 50%, #f8f9fa 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
    }

    .login-card {
      background: #ffffff;
      border-radius: 28px;
      box-shadow: 0 12px 35px rgba(255, 107, 139, 0.12);
      border: 1.5px solid #ffe0e6;
      width: 100%;
      max-width: 420px;
      padding: 40px 30px;
      transition: all 0.3s ease;
    }

    .brand-badge {
      background-color: #ff6b8b;
      color: white;
      font-size: 0.85rem;
      font-weight: 500;
      padding: 6px 16px;
      border-radius: 50px;
      display: inline-block;
      margin-bottom: 12px;
      box-shadow: 0 4px 12px rgba(255, 107, 139, 0.3);
    }

    .form-control {
      border-radius: 14px;
      border: 1.5px solid #ffe0e6;
      padding: 12px 16px;
      background-color: #fcfcfc;
      font-size: 0.95rem;
      transition: all 0.25s ease;
    }

    .form-control:focus {
      border-color: #ff6b8b;
      background-color: #ffffff;
      box-shadow: 0 0 0 0.25rem rgba(255, 107, 139, 0.2);
    }

    .btn-login {
      background: linear-gradient(135deg, #ff6b8b 0%, #ff8fa3 100%);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 14px;
      font-size: 1rem;
      font-weight: 600;
      width: 100%;
      box-shadow: 0 6px 18px rgba(255, 107, 139, 0.35);
      transition: all 0.3s ease;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #e05270 0%, #ff6b8b 100%);
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(255, 107, 139, 0.45);
      color: white;
    }

    .register-link {
      color: #ff6b8b;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .register-link:hover {
      color: #e05270;
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-card text-center">
    <!-- โลโก้แบรนด์ -->
    <span class="brand-badge">📷 Chubby Lens Y2K</span>
    <h3 class="fw-bold text-dark mb-1">เข้าสู่ระบบ</h3>
    <p class="text-muted small mb-4">ยินดีต้อนรับเข้าสู่ระบบเช่ากล้องดิจิทัล</p>

    <form action="process_login.php" method="POST">
      <div class="mb-3 text-start">
        <label class="form-label fw-medium text-dark small">Username / ชื่อผู้ใช้</label>
        <input type="text" name="username" class="form-control" placeholder="กรอก Username ของคุณ" required autofocus>
      </div>

      <div class="mb-4 text-start">
        <label class="form-label fw-medium text-dark small">Password / รหัสผ่าน</label>
        <input type="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required>
      </div>

      <button type="submit" class="btn btn-login mb-4">
        เข้าสู่ระบบ
      </button>

      <div class="small text-secondary">
        ยังไม่มีบัญชีสมาชิก? <a href="register.php" class="register-link">สมัครสมาชิกที่นี่</a>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>