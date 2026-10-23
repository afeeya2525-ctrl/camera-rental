<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];

// ดึงรายการจองของลูกค้ารายนี้ พร้อมชื่อกล้องจากตาราง cameras
$sql = "SELECT b.*, c.camera_name 
        FROM bookings b 
        LEFT JOIN cameras c ON b.camera_id = c.id 
        WHERE b.user_id = ? 
        ORDER BY b.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ติดตามสถานะการจอง - Chubby Lens</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background-color: #f8f9fa;
    }
    .badge-pending {
      background-color: #ffeba8;
      color: #856404;
    }
    .badge-approved {
      background-color: #d4edda;
      color: #155724;
    }
    .badge-rejected {
      background-color: #f8d7da;
      color: #721c24;
    }
  </style>
</head>
<body>

  <!-- แถบผู้ใช้งานด้านบน -->
  <nav class="bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
      <span class="text-dark fw-medium">ยินดีต้อนรับคุณ, <strong><?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?></strong></span>
      <div>
        <a href="index.php" class="btn btn-primary btn-sm me-2">หน้าจองกล้อง</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
      </div>
  </nav>

  <div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
      <h3 class="fw-bold text-dark mb-1">📋 รายการการจองกล้องของฉัน</h3>
      <p class="text-muted small mb-4">ตรวจสอบสถานะการอนุมัติคิวและรายละเอียดรายการจอง</p>

      <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>รุ่นกล้อง</th>
                <th>วันที่เริ่มเช่า</th>
                <th>วันที่ส่งคืน</th>
                <th>ยอดรวม</th>
                <th>สถานะการจอง</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $i++; ?></td>
                  <td class="fw-bold"><?php echo htmlspecialchars($row['camera_name'] ?? 'กล้อง'); ?></td>
                  <td><?php echo date('d/m/Y', strtotime($row['start_date'])); ?></td>
                  <td><?php echo date('d/m/Y', strtotime($row['end_date'])); ?></td>
                  <td class="fw-bold text-danger"><?php echo number_format($row['total_price']); ?> บาท</td>
                  <td>
                    <?php 
                      $status = $row['status'] ?? 'pending';
                      if ($status == 'approved') {
                          echo '<span class="badge badge-approved px-3 py-2 rounded-pill">🟢 อนุมัติแล้ว</span>';
                      } elseif ($status == 'rejected') {
                          echo '<span class="badge badge-rejected px-3 py-2 rounded-pill">🔴 ไม่อนุมัติ / ยกเลิก</span>';
                      } else {
                          echo '<span class="badge badge-pending px-3 py-2 rounded-pill">🟡 รอแอดมินตรวจสอบ</span>';
                      }
                    ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-5 text-muted">
          <h5>ยังไม่มีประวัติการจองกล้อง</h5>
          <a href="index.php" class="btn btn-danger rounded-pill mt-3 px-4" style="background-color: #ff6b8b; border: none;">คลิกเพื่อไปจองกล้อง</a>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======
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
>>>>>>> 962e6859cedf71e7faa250a3eeef3121e52b6e61
