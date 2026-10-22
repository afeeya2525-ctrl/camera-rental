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
</html>