<<<<<<< HEAD
<?php
session_start();
require_once 'db.php';

// เช็กสิทธิ์ว่าเป็น Admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ดึงรายการจองทั้งหมด พร้อมข้อมูลกล้อง และข้อมูลผู้ใช้
$sql = "SELECT b.*, c.camera_name, u.fullname, u.phone 
        FROM bookings b 
        LEFT JOIN cameras c ON b.camera_id = c.id 
        LEFT JOIN users u ON b.user_id = u.id 
        ORDER BY b.id DESC";

$result = $conn->query($sql);

// นับจำนวนรายการแยกตามสถานะ
$total_count = $result ? $result->num_rows : 0;
$pending_count = 0;
$approved_count = 0;

if ($result) {
    while ($row_check = $result->fetch_assoc()) {
        $st = $row_check['status'] ?? 'pending';
        if ($st == 'pending') $pending_count++;
        if ($st == 'approved') $approved_count++;
    }
    // รีเซ็ตตัวชี้ข้อมูลกลับไปจุดเริ่มต้น
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจัดการหลังบ้าน (Admin) - Chubby Lens</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Kanit -->
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background: linear-gradient(135deg, #fff5f7 0%, #f8f9fa 100%);
      color: #4a4a4a;
      min-height: 100vh;
    }

    /* แถบเมนูด้านบน */
    .admin-navbar {
      background-color: #ffffff;
      border-bottom: 2px solid #ffe0e6;
      box-shadow: 0 4px 15px rgba(255, 107, 139, 0.05);
    }

    /* กล่อง Dashboard Card */
    .dashboard-card {
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 10px 30px rgba(255, 107, 139, 0.08);
      border: 1px solid rgba(255, 211, 226, 0.6);
      padding: 30px;
    }

    /* การ์ดสรุปสถิติ */
    .stat-box {
      border-radius: 18px;
      padding: 20px;
      border: 1px solid #ffe0e6;
      background-color: #fff8f9;
      transition: all 0.25s ease;
    }
    .stat-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 18px rgba(255, 107, 139, 0.12);
    }

    /* จัดระเบียบตาราง */
    .table-custom {
      border-collapse: separate;
      border-spacing: 0 8px;
    }
    .table-custom thead th {
      border: none;
      color: #ff6b8b;
      font-weight: 600;
      background-color: #fff0f3;
      padding: 14px 16px;
    }
    .table-custom tbody tr {
      background-color: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
      transition: all 0.2s ease;
    }
    .table-custom tbody tr:hover {
      background-color: #fff9fa;
      transform: scale(1.002);
    }
    .table-custom tbody td {
      border-top: 1px solid #ffe0e6;
      border-bottom: 1px solid #ffe0e6;
      padding: 16px;
      vertical-align: middle;
    }
    .table-custom tbody td:first-child {
      border-left: 1px solid #ffe0e6;
      border-top-left-radius: 12px;
      border-bottom-left-radius: 12px;
    }
    .table-custom tbody td:last-child {
      border-right: 1px solid #ffe0e6;
      border-top-right-radius: 12px;
      border-bottom-right-radius: 12px;
    }

    /* ป้ายสถานะ */
    .badge-approved {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .badge-rejected {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .badge-pending {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
    }

    /* ปุ่มการทำงาน */
    .btn-approve {
      background-color: #28a745;
      color: white;
      border-radius: 50px;
      font-size: 0.85rem;
      padding: 6px 14px;
      border: none;
    }
    .btn-approve:hover { background-color: #218838; color: white; }
    .btn-reject {
      background-color: #dc3545;
      color: white;
      border-radius: 50px;
      font-size: 0.85rem;
      padding: 6px 14px;
      border: none;
    }
    .btn-reject:hover { background-color: #c82333; color: white; }
  </style>
</head>
<body>

  <!-- แถบเมนูด้านบน -->
  <nav class="admin-navbar py-3 px-4 d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center gap-2">
        <span class="fs-4">📷</span>
        <h5 class="fw-bold text-dark mb-0">ระบบจัดการหลังบ้าน (Admin)</h5>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span class="text-secondary small">แอดมิน: <strong class="text-dark"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">ออกจากระบบ</a>
      </div>
  </nav>

  <div class="container-fluid px-4 pb-5">
    
    <!-- สรุปตัวเลขสถิติภาพรวม -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-box d-flex justify-content-between align-items-center">
          <div>
            <span class="text-muted small d-block mb-1">คำขอจองทั้งหมด</span>
            <h3 class="fw-bold text-dark mb-0"><?php echo $total_count; ?> รายการ</h3>
          </div>
          <span class="fs-1 text-pink">📋</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-box d-flex justify-content-between align-items-center" style="background-color: #fffdf0; border-color: #ffeeba;">
          <div>
            <span class="text-warning small d-block mb-1 fw-medium">รอการตรวจสอบ</span>
            <h3 class="fw-bold text-warning mb-0"><?php echo $pending_count; ?> รายการ</h3>
          </div>
          <span class="fs-1">⏳</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-box d-flex justify-content-between align-items-center" style="background-color: #f4fbf7; border-color: #c3e6cb;">
          <div>
            <span class="text-success small d-block mb-1 fw-medium">อนุมัติแล้ว</span>
            <h3 class="fw-bold text-success mb-0"><?php echo $approved_count; ?> รายการ</h3>
          </div>
          <span class="fs-1">✅</span>
        </div>
      </div>
    </div>

    <!-- กล่องตารางรายการจอง -->
    <div class="dashboard-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold text-dark mb-1">รายการคำขอจองเช่ากล้องทั้งหมด</h4>
          <p class="text-muted small mb-0">ตรวจสอบหลักฐานการโอนเงิน บัตรประชาชน และอนุมัติคิวจอง</p>
        </div>
      </div>

      <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th class="text-center">รหัสจอง</th>
                <th>ชื่อลูกค้า (เบอร์โทร)</th>
                <th>รุ่นกล้อง</th>
                <th>วันที่เช่า – คืน</th>
                <th>ยอดโอนสุทธิ</th>
                <th class="text-center">หลักฐาน</th>
                <th class="text-center">สถานะ</th>
                <th class="text-center">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td class="text-center fw-bold text-secondary">#<?php echo $row['id']; ?></td>
                  <td>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['fullname'] ?? 'ลูกค้า'); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></small>
                  </td>
                  <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['camera_name'] ?? 'กล้อง'); ?></td>
                  <td>
                    <small class="d-block text-dark fw-medium">
                      <?php echo date('d/m/Y', strtotime($row['start_date'])); ?> ถึง <?php echo date('d/m/Y', strtotime($row['end_date'])); ?>
                    </small>
                  </td>
                  <td class="fw-bold text-danger fs-6"><?php echo number_format($row['total_price']); ?> ฿</td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <?php if (!empty($row['slip_image'])): ?>
                        <a href="uploads/<?php echo htmlspecialchars($row['slip_image']); ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill" style="font-size: 0.75rem;">ดูสลิป</a>
                      <?php endif; ?>
                      <?php if (!empty($row['id_card_image'])): ?>
                        <a href="uploads/<?php echo htmlspecialchars($row['id_card_image']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill" style="font-size: 0.75rem;">ดูบัตร</a>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="text-center">
                    <?php 
                      $status = $row['status'] ?? 'pending';
                      if ($status == 'approved') {
                          echo '<span class="badge badge-approved px-3 py-2 rounded-pill">🟢 อนุมัติแล้ว</span>';
                      } elseif ($status == 'rejected') {
                          echo '<span class="badge badge-rejected px-3 py-2 rounded-pill">🔴 ปฏิเสธ</span>';
                      } else {
                          echo '<span class="badge badge-pending px-3 py-2 rounded-pill">🟡 รอตรวจสอบ</span>';
                      }
                    ?>
                  </td>
                  <td class="text-center">
                    <form action="process_admin_action.php" method="POST" class="d-inline-flex gap-1">
                      <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                      <button type="submit" name="action" value="approve" class="btn btn-approve shadow-sm" onclick="return confirm('ยืนยันอนุมัติคำขอจองนี้?');">อนุมัติ</button>
                      <button type="submit" name="action" value="reject" class="btn btn-reject shadow-sm" onclick="return confirm('ยืนยันปฏิเสธคำขอจองนี้?');">ปฏิเสธ</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5 text-muted">
          <span class="fs-1 d-block mb-2">📥</span>
          <h5>ยังไม่มีรายการจองเข้ามาในระบบ</h5>
          <p class="small">รายการจองจากลูกค้าจะมาปรากฏที่หน้านี้ทันทีเมื่อมีการกดสั่งจอง</p>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
=======
<?php
session_start();
require_once 'db.php';

// ตรวจสอบสิทธิ์ (ถ้าไม่ได้ล็อกอิน หรือ ไม่ใช่แอดมิน ให้เด้งกลับไปหน้าแรก)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะผู้ดูแลระบบเท่านั้น'); window.location.href='index.php';</script>";
    exit();
}

// ดึงข้อมูลการจองทั้งหมดจากฐานข้อมูล
$sql = "SELECT b.*, u.fullname, u.phone, c.camera_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN cameras c ON b.camera_id = c.id 
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบหลังบ้าน - Chubby Lens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    <nav class="bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center mb-4 shadow-sm">
        <h5 class="mb-0 fw-bold">📸 ระบบจัดการหลังบ้าน (Admin)</h5>
        <div>
            <span class="me-3">แอดมิน: <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">ออกจากระบบ</a>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h4 class="fw-bold mb-4">รายการคำขอจองเช่ากล้องทั้งหมด</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>รหัสจอง</th>
                            <th>ชื่อลูกค้า (เบอร์โทร)</th>
                            <th>รุ่นกล้อง</th>
                            <th>วันที่เช่า - คืน</th>
                            <th>ยอดโอนสุทธิ</th>
                            <th>หลักฐาน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['fullname']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['camera_name']); ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($row['start_date'])); ?> ถึง 
                                        <?php echo date('d/m/Y', strtotime($row['end_date'])); ?>
                                    </td>
                                    <td class="text-danger fw-bold"><?php echo number_format($row['total_price']); ?> ฿</td>
                                    <td>
                                        <a href="uploads/<?php echo htmlspecialchars($row['slip_image']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-1">ดูสลิป</a>
                                        <a href="uploads/<?php echo htmlspecialchars($row['id_card_image']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">ดูบัตร</a>
                                    </td>
                                    <td>
                                        <?php 
                                            if($row['status'] == 'pending') echo '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
                                            elseif($row['status'] == 'approved') echo '<span class="badge bg-success">อนุมัติแล้ว</span>';
                                            elseif($row['status'] == 'rejected') echo '<span class="badge bg-danger">ไม่อนุมัติ</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if($row['status'] == 'pending'): ?>
                                            <form action="process_admin_action.php" method="POST" class="d-flex gap-2 align-items-center">
                                            > <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="ใส่เลขพัสดุ" value="<?php echo htmlspecialchars($row['tracking_number'] ?? ''); ?>" style="width: 140px;">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm text-nowrap">อนุมัติ / บันทึกเลขพัสดุ</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm text-nowrap" onclick="return confirm('ยืนยันปฏิเสธการจองนี้?');">ปฏิเสธ</button>
                                            </form
                                        <?php else: ?>
                                            <span class="text-muted small">จัดการแล้ว</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีรายการจองในระบบ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
>>>>>>> 962e6859cedf71e7faa250a3eeef3121e52b6e61
</html>