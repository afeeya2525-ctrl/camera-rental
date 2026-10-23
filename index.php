<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// ดึงรายการกล้องจากฐานข้อมูล
$cameras = $conn->query("SELECT * FROM cameras");

// ดึงรายการการจองทั้งหมดที่ไม่ใช่การปฏิเสธ (เพื่อนำมาบล็อกวันที่ติดคิวบนปฏิทิน)
$booking_query = $conn->query("SELECT camera_id, start_date, end_date FROM bookings WHERE status != 'rejected'");
$booked_data = [];
if ($booking_query) {
    while ($b = $booking_query->fetch_assoc()) {
        $cam_id = $b['camera_id'];
        if (!isset($booked_data[$cam_id])) {
            $booked_data[$cam_id] = [];
        }
        $booked_data[$cam_id][] = [
            'from' => $b['start_date'],
            'to' => $b['end_date']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจองกล้อง - Chubby Lens</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Kanit -->
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- สไตล์ตกแต่งเพิ่มเติมสำหรับหน้า index.php -->
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background: linear-gradient(135deg, #fff5f7 0%, #f8f9fa 100%);
      color: #4a4a4a;
    }
    
    .card {
      border-radius: 24px !important;
      box-shadow: 0 10px 30px rgba(255, 107, 139, 0.08) !important;
      border: 1px solid rgba(255, 211, 226, 0.5) !important;
    }

    /* กล่องปฏิทินทรงมนสไตล์มินิมอล Y2K */
    .calendar-card {
      background: #ffffff;
      border: 1.5px solid #ffe0e6;
      border-radius: 24px;
      padding: 20px 15px;
      max-width: 100%;
      width: 100%;
      margin: 0 auto;
      box-shadow: 0 6px 20px rgba(255, 107, 139, 0.06);
      box-sizing: border-box;
    }
    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding: 0 5px;
    }
    .calendar-weekdays {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      font-weight: 600;
      color: #ff6b8b;
      font-size: 0.95rem;
      margin-bottom: 12px;
    }
    .calendar-days {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
      justify-items: center;
      align-items: center;
    }
    .cal-day {
      width: 100%;
      max-width: 38px;
      aspect-ratio: 1 / 1;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 0.9rem;
      font-weight: 600;
      user-select: none;
      transition: all 0.2s ease;
      margin: 0 auto;
    }
    .cal-day.empty {
      visibility: hidden;
    }
    /* วันในอดีต (สีเทาจาง) */
    .cal-day.past {
      color: #ccc;
      background-color: #f5f5f5;
      cursor: not-allowed;
    }
    /* วันติดคิวจองแล้ว (แดง) */
    .cal-day.booked {
      background-color: #fce4e4 !important;
      color: #d9534f !important;
      font-weight: bold;
      border: 1px solid #f7c8c8 !important;
      cursor: not-allowed;
    }
    /* วันว่างจองได้ (เขียว) */
    .cal-day.available {
      background-color: #e2f0d9 !important;
      color: #385723 !important;
      border: 1px solid #c5e0b4 !important;
      cursor: pointer;
    }
    .cal-day.available:hover {
      background-color: #c5e0b4 !important;
      transform: scale(1.12);
    }
    /* วันที่ถูกคลิกเลือก */
    .cal-day.selected-start, .cal-day.selected-end {
      background-color: #ff6b8b !important;
      color: #ffffff !important;
      font-weight: bold;
      border: 2px solid #e85575 !important;
      box-shadow: 0 4px 10px rgba(255, 107, 139, 0.4);
    }
    .cal-day.selected-range {
      background-color: #ffdeeb !important;
      color: #ff6b8b !important;
      border-radius: 50%;
    }

    /* ปุ่มสถานะด้านล่างปฏิทิน */
    .status-badge-green {
      background-color: #e2f0d9;
      color: #2e75b6;
      border: 1px solid #b4c6e7;
      border-radius: 50px;
      padding: 8px 18px;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .status-badge-red {
      background-color: #fce4e4;
      color: #c00000;
      border: 1px solid #f4b084;
      border-radius: 50px;
      padding: 8px 18px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    /* ตกแต่ง Input และ Select */
    .form-control, .form-select {
      border-radius: 12px;
      border: 1.5px solid #ffe0e6;
      padding: 10px 15px;
    }
    .form-control:focus, .form-select:focus {
      border-color: #ff6b8b;
      box-shadow: 0 0 0 0.25rem rgba(255, 107, 139, 0.2);
    }
  </style>
</head>
<body>

  <!-- แถบผู้ใช้งานด้านบน -->
  <nav class="bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center shadow-sm">
      <span class="text-dark fw-medium">ยินดีต้อนรับคุณ, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
      <div>
        <a href="my_bookings.php" class="btn btn-outline-primary btn-sm me-2 rounded-pill">ติดตามสถานะการจอง</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill">ออกจากระบบ</a>
      </div>
  </nav>

  <!-- HEADER / HERO SECTION -->
  <header class="hero-section py-5 mb-5 text-center" style="background: linear-gradient(135deg, #ffe6eb 0%, #fff0f3 100%); border-bottom: 2px solid #f8d7da;">
    <div class="container bg-transparent border-0 shadow-none">
      <span class="badge rounded-pill px-3 py-2 fs-6 mb-3 shadow-sm" style="background-color: #ff6b8b; color: white;">Chubby Lens Camera Rental</span>
      <h1 class="fw-bold text-dark display-5 mb-2">บริการเช่ากล้องดิจิทัล Y2K</h1>
      <p class="text-muted fs-5 mb-0">โทนผิวละมุน แฟลชฉ่ำ งานผิวสวยสไตล์ Y2K ถ่ายง่าย พกพาสะดวก</p>
    </div>
  </header>

  <div class="container pb-5">

    <!-- SECTION: ขั้นตอนการเช่าง่ายๆ 4 ขั้นตอน -->
    <section class="mb-5 text-center">
      <h3 class="fw-bold text-dark mb-4">ขั้นตอนการเช่ากล้องกับ Chubby Lens</h3>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold" style="color: #ff6b8b;">1. เช็ควันว่าง</h6>
            <p class="small text-muted mb-0">เลือกรุ่นที่ต้องการและระบุวันเช่า</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold" style="color: #ff6b8b;">2. กรอกฟอร์ม & โอนเงิน</h6>
            <p class="small text-muted mb-0">แนบสลิปโอนเงินและบัตรยืนยันตัวตน</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold" style="color: #ff6b8b;">3. ยืนยันการจอง</h6>
            <p class="small text-muted mb-0">แอดมินตรวจหลักฐานและยืนยันคิว</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold" style="color: #ff6b8b;">4. รับกล้อง & ส่งคืน</h6>
            <p class="small text-muted mb-0">รับกล้องตามนัด คืนปุ๊บรับมัดจำคืนปั๊บ</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: ฟอร์มส่งคำขอจอง & ชำระเงิน -->
    <section>
      <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4">
        <div class="border-bottom pb-3 mb-4">
          <h3 class="fw-bold text-dark mb-1">กรอกข้อมูลการจองเช่ากล้อง</h3>
          <p class="text-muted small mb-0">กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการยืนยันคิว</p>
        </div>

        <form action="process_booking.php" method="POST" enctype="multipart/form-data">

          <!-- 1. เลือกรุ่นกล้อง -->
          <h5 class="fw-bold text-dark mb-3">1. เลือกรุ่นกล้องที่ต้องการ</h5>
          <div class="mb-4">
            <select name="camera_id" id="cameraSelect" class="form-select form-select-lg" onchange="loadCalendarForCamera(); calculateTotal();" required>
                <option value="" data-price="0">-- กรุณาเลือกกล้อง --</option>
                <?php 
                if ($cameras) {
                    $cameras->data_seek(0);
                    while($row = $cameras->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['daily_rate']; ?>">
                            <?php echo htmlspecialchars($row['camera_name']); ?> (<?php echo number_format($row['daily_rate']); ?> บาท/วัน)
                        </option>
                    <?php 
                    endwhile; 
                }
                ?>
            </select>
          </div>

          <!-- ตารางแสดงปฏิทินคิววันว่างประจำเดือน -->
          <div class="p-4 rounded-4 mb-4 text-center" style="background-color: #fff8f9; border: 1px solid #ffe0e6;">
            <h6 class="fw-bold text-dark mb-2">📅 ตารางคิวเช่าประจำเดือน</h6>
            <p class="text-muted small mb-3">คลิกครั้งแรก = วันเริ่มเช่า / คลิกครั้งที่สอง = วันส่งคืนกล้อง</p>
            
            <div class="calendar-card">
              <!-- หัวปฏิทินแสดง วัน/เดือน/ปี และปุ่มเปลี่ยนเดือน -->
              <div class="calendar-header">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="prevMonth()">❮</button>
                <h6 class="fw-bold text-dark mb-0 fs-5" id="calendarMonthTitle">เดือน ปี</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="nextMonth()">❯</button>
              </div>

              <!-- แถบชื่อวันในสัปดาห์ -->
              <div class="calendar-weekdays">
                <span>อา</span><span>จ</span><span>อ</span><span>พ</span><span>พฤ</span><span>ศ</span><span>ส</span>
              </div>

              <!-- ตารางตัวเลขวันที่ -->
              <div class="calendar-days" id="calendarDays"></div>
            </div>

            <!-- สัญลักษณ์บอกสถานะ -->
            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
              <span class="status-badge-green">
                🟢 วันที่ว่าง (คลิกเลือกจองได้)
              </span>
              <span class="status-badge-red">
                🔴 วันติดคิวจองแล้ว (ห้ามเลือก)
              </span>
            </div>
          </div>

          <!-- 2. ข้อมูลผู้เช่า -->
          <h5 class="fw-bold text-dark mb-3">2. ข้อมูลติดต่อผู้เช่า</h5>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">ชื่อ-นามสกุล</label>
              <input type="text" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">เบอร์โทรศัพท์ติดต่อ</label>
              <input type="tel" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-medium text-dark">Line ID</label>
              <input type="text" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['line_id'] ?? ''); ?>" readonly>
            </div>
          </div>

          <!-- 3. กำหนดวันที่เช่า & สรุปราคา -->
          <h5 class="fw-bold text-dark mb-3">3. กำหนดวันที่เช่า</h5>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">วันที่เริ่มเช่า (รับกล้อง) *</label>
              <input type="date" name="start_date" id="startDate" class="form-control py-2" onchange="manualDateInput()" required readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">วันที่ส่งคืนกล้อง *</label>
              <input type="date" name="end_date" id="endDate" class="form-control py-2" onchange="manualDateInput()" required readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-medium text-dark">อุปกรณ์เสริมเพิ่มเติม (Add-ons)</label>
              <div class="d-flex flex-wrap gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="add_tripod" value="1" id="add1" onchange="calculateTotal()">
                  <label class="form-check-label text-secondary" for="add1">ขาตั้งกล้องพกพา (+30 บาท/วัน)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="add_battery" value="1" id="add2" onchange="calculateTotal()">
                  <label class="form-check-label text-secondary" for="add2">แบตสำรองก้อนที่ 2 (+30 บาท/วัน)</label>
                </div>
              </div>
            </div>

            <!-- สรุปราคาการคำนวณ -->
            <div class="col-12 mt-4">
              <div class="p-4 border rounded-4" style="background-color: #fff0f3; border: 1.5px dashed #ff6b8b !important;">
                <h6 class="fw-bold text-dark mb-3">สรุปรายการคำนวณค่าเช่าอัตโนมัติ</h6>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">จำนวนวันที่เช่า:</span>
                  <span class="fw-bold text-dark" id="rentalDays">0 วัน</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">ค่าเช่ากล้อง:</span>
                  <span class="fw-bold text-dark" id="rentalFee">0 บาท</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">ค่าอุปกรณ์เสริมเพิ่มเติม:</span>
                  <span class="fw-bold text-dark" id="addonFee">0 บาท</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">เงินประกันตัวเครื่อง (ได้คืนเต็มจำนวน):</span>
                  <span class="fw-bold text-dark">150 บาท</span>
                </div>
                <hr style="border-top: 1px dashed #ff6b8b;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="fw-bold text-dark fs-5">ยอดโอนเงินสุทธิ:</span>
                  <span class="fw-bold text-danger fs-3" id="totalAmount">150 บาท</span>
                </div>

                <div class="p-3 bg-white rounded-3 border small text-muted">
                  <strong class="text-dark d-block mb-1">เงื่อนไขการคืนเงินประกัน/มัดจำ (150 บาท):</strong>
                  - ทางร้านจะโอนเงินคืนทันที หลังจากได้รับกล้องคืนและตรวจเช็กสภาพอุปกรณ์เรียบร้อยแล้ว<br>
                  - กล้องต้องอยู่ในสภาพสมบูรณ์ ไม่ชำรุดเสียหาย หรือสูญหาย
                </div>
              </div>
            </div>

            <div class="col-12 mt-3">
              <label class="form-label fw-medium text-dark">หมายเหตุเพิ่มเติม</label>
              <textarea name="note" class="form-control" rows="2" placeholder="เช่น ขอรับกล้องช่วงเช้า 09.00 น."></textarea>
            </div>
          </div>

          <!-- 4. ส่วนชำระเงิน & แนบหลักฐาน -->
          <h5 class="fw-bold text-dark mb-3">4. ชำระเงิน & ยืนยันตัวตน</h5>
          <div class="p-4 rounded-4 bg-light border mb-4">
            <div class="card card-body border-0 shadow-sm mb-4">
              <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                  <small class="text-muted d-block">ธนาคารกสิกรไทย (K-Bank)</small>
                  <span class="fs-4 fw-bold text-dark">190-0-18743-2</span>
                  <small class="text-muted d-block">ชื่อบัญชี: Chubby Lens </small>
                </div>
                <div class="mt-3 mt-sm-0 text-center">
                  <img src="qr.png" alt="QR Code ชำระเงิน" class="img-fluid rounded shadow-sm" style="max-width: 150px;" onerror="this.src='my_qrqr.png.png'">
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-medium text-dark small">แนบสลิปการโอนเงิน *</label>
                <input type="file" name="slip_image" class="form-control" accept="image/*" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium text-dark small">แนบรูปถ่ายบัตรประชาชน *</label>
                <input type="file" name="id_card_image" class="form-control" accept="image/*" required>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-medium text-dark small">บัญชีสำหรับรับมัดจำคืน *</label>
                <input type="text" name="refund_account" class="form-control" placeholder="ระบุธนาคาร + เลขบัญชี และชื่อบัญชี" required>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-lg w-100 py-3 fw-bold shadow-sm text-white rounded-pill" style="background: linear-gradient(135deg, #ff6b8b 0%, #ff8fa3 100%); border: none;">
              ส่งคำขอจองเช่ากล้อง
          </button>

        </form>
      </div>
    </section>

  </div>

  <footer class="text-center py-4 text-muted border-top bg-white mt-5">
    <small>© 2026 Chubby Lens Camera Rental</small>
  </footer>

  <!-- JavaScript คำนวณราคา & จัดการปฏิทิน -->
  <script>
    const bookedData = <?php echo json_encode($booked_data); ?>;

    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();
    let selectedStart = null;
    let selectedEnd = null;

    const monthNamesTH = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    function isBooked(dateStr) {
      const cameraSelect = document.getElementById('cameraSelect');
      if (!cameraSelect) return false;
      const cameraId = cameraSelect.value;
      if (!cameraId) return false;

      const cameraBookings = bookedData[cameraId] || [];
      for (let b of cameraBookings) {
        if (dateStr >= b.from && dateStr <= b.to) {
          return true;
        }
      }
      return false;
    }

    function formatDateISO(year, month, day) {
      let m = (month + 1).toString().padStart(2, '0');
      let d = day.toString().padStart(2, '0');
      return `${year}-${m}-${d}`;
    }

    function renderCalendar() {
      const titleElem = document.getElementById('calendarMonthTitle');
      if (titleElem) {
        titleElem.innerText = `${monthNamesTH[currentMonth]} ${currentYear + 543}`;
      }
      const calendarDays = document.getElementById('calendarDays');
      if (!calendarDays) return;
      calendarDays.innerHTML = '';

      const firstDay = new Date(currentYear, currentMonth, 1).getDay();
      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

      const today = new Date();
      today.setHours(0,0,0,0);

      // วันว่างก่อนเริ่มวันที่ 1
      for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'cal-day empty';
        calendarDays.appendChild(emptyCell);
      }

      // สร้างช่องวันที่ 1 ถึง สิ้นเดือน
      for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        const dateObj = new Date(currentYear, currentMonth, day);
        const dateStr = formatDateISO(currentYear, currentMonth, day);

        dayCell.innerText = day;
        dayCell.className = 'cal-day';

        const isPast = dateObj < today;
        const booked = isBooked(dateStr);

        if (isPast) {
          dayCell.classList.add('past');
        } else if (booked) {
          dayCell.classList.add('booked');
        } else {
          dayCell.classList.add('available');

          if (selectedStart === dateStr) dayCell.classList.add('selected-start');
          if (selectedEnd === dateStr) dayCell.classList.add('selected-end');
          if (selectedStart && selectedEnd && dateStr > selectedStart && dateStr < selectedEnd) {
            dayCell.classList.add('selected-range');
          }

          dayCell.onclick = () => selectCalendarDate(dateStr);
        }

        calendarDays.appendChild(dayCell);
      }
    }

    function selectCalendarDate(dateStr) {
      if (!selectedStart || (selectedStart && selectedEnd)) {
        selectedStart = dateStr;
        selectedEnd = null;
      } else if (selectedStart && !selectedEnd) {
        if (dateStr < selectedStart) {
          selectedStart = dateStr;
        } else {
          selectedEnd = dateStr;
        }
      }

      document.getElementById('startDate').value = selectedStart || '';
      document.getElementById('endDate').value = selectedEnd || '';

      renderCalendar();
      calculateTotal();
    }

    function manualDateInput() {
      selectedStart = document.getElementById('startDate').value;
      selectedEnd = document.getElementById('endDate').value;
      renderCalendar();
      calculateTotal();
    }

    function loadCalendarForCamera() {
      renderCalendar();
    }

    function prevMonth() {
      currentMonth--;
      if (currentMonth < 0) { currentMonth = 11; currentYear--; }
      renderCalendar();
    }

    function nextMonth() {
      currentMonth++;
      if (currentMonth > 11) { currentMonth = 0; currentYear++; }
      renderCalendar();
    }

    function calculateTotal() {
      const cameraSelect = document.getElementById('cameraSelect');
      if (!cameraSelect) return;
      const selectedOption = cameraSelect.options[cameraSelect.selectedIndex];
      const cameraPrice = parseFloat(selectedOption ? selectedOption.getAttribute('data-price') : 0) || 0;

      const startDateVal = document.getElementById('startDate').value;
      const endDateVal = document.getElementById('endDate').value;
      
      const add1Elem = document.getElementById('add1');
      const add2Elem = document.getElementById('add2');
      const add1 = add1Elem ? add1Elem.checked : false;
      const add2 = add2Elem ? add2Elem.checked : false;

      let days = 0;
      let rentalFee = 0;
      let addonFee = 0;
      const depositFee = 150; 

      if (startDateVal && endDateVal && cameraPrice > 0) {
        const start = new Date(startDateVal);
        const end = new Date(endDateVal);

        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 0) {
          days = diffDays;
          rentalFee = days * cameraPrice;

          let addonRate = 0;
          if (add1) addonRate += 30; // ขาตั้งกล้อง (+30 บาท/วัน)
          if (add2) addonRate += 30; // แบตเตอรี่สำรอง (+30 บาท/วัน)
          addonFee = days * addonRate;
        }
      }

      const totalAmount = rentalFee + addonFee + (days > 0 ? depositFee : 0);

      document.getElementById('rentalDays').innerText = days + " วัน";
      document.getElementById('rentalFee').innerText = rentalFee.toLocaleString() + " บาท";
      document.getElementById('addonFee').innerText = addonFee.toLocaleString() + " บาท";
      document.getElementById('totalAmount').innerText = (days > 0 ? totalAmount : depositFee).toLocaleString() + " บาท";
    }

    document.addEventListener('DOMContentLoaded', function() {
      renderCalendar();
      calculateTotal();
    });
  </script>

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

// ดึงรายการกล้องจากฐานข้อมูล
$cameras =$conn->query("SELECT * FROM cameras");

// ดึงรายการการจองทั้งหมดที่ไม่ใช่การปฏิเสธ (เพื่อนำมาบล็อกวันที่ติดคิวบนปฏิทิน)
$booking_query =$conn->query("SELECT camera_id, start_date, end_date FROM bookings WHERE status != 'rejected'");
$booked_data = [];
if ($booking_query) {
    while ($b =$booking_query->fetch_assoc()) {
        $cam_id =$b['camera_id'];
        if (!isset($booked_data[$cam_id])) {
            $booked_data[$cam_id] = [];
        }
        $booked_data[$cam_id][] = [
            'from' => $b['start_date'],
            'to' => $b['end_date']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจองกล้อง - Chubby Lens</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Kanit -->
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- เรียกใช้ไฟล์ตกแต่ง CSS ภายนอก -->
  <link rel="stylesheet" href="style.css">

  <!-- สไตล์ตารางปฏิทินวันว่าง (ทำงานบน Localhost ได้ 100% ไม่ต้องใช้อินเทอร์เน็ต) -->
  <style>
    .calendar-box {
      background: #ffffff;
      border: 2px solid #ffd3e2;
      border-radius: 20px;
      padding: 20px;
      max-width: 480px;
      margin: 0 auto;
      box-shadow: 0 4px 15px rgba(255, 107, 139, 0.08);
    }
    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    .calendar-weekdays {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      text-align: center;
      font-weight: 600;
      color: #888;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }
    .calendar-days {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
    }
    .cal-day {
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 0.9rem;
      font-weight: 500;
      user-select: none;
      transition: all 0.2s ease;
    }
    .cal-day.empty {
      visibility: hidden;
    }
    .cal-day.past {
      color: #ccc;
      background-color: #f8f9fa;
      cursor: not-allowed;
    }
    /* วันติดคิวจองแล้ว (สีแดง + ขีดฆ่า) */
    .cal-day.booked {
      background-color: #ffe6e6;
      color: #d9534f;
      text-decoration: line-through;
      font-weight: bold;
      border: 1px solid #f5c2c7;
      cursor: not-allowed;
    }
    /* วันว่าง (สีเขียว) */
    .cal-day.available {
      background-color: #e8f5e9;
      color: #198754;
      border: 1px solid #c3e6cb;
      cursor: pointer;
    }
    .cal-day.available:hover {
      background-color: #c8e6c9;
      transform: scale(1.1);
    }
    /* วันที่ถูกกดเลือก */
    .cal-day.selected-start, .cal-day.selected-end {
      background-color: #ff6b8b !important;
      color: #ffffff !important;
      font-weight: bold;
      border: 2px solid #e85575 !important;
    }
    .cal-day.selected-range {
      background-color: #ffdeeb !important;
      color: #ff6b8b !important;
      border-radius: 10px;
    }
  </style>
</head>
<body>

  <!-- แถบผู้ใช้งานด้านบน -->
  <nav class="bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
      <span class="text-dark fw-medium">ยินดีต้อนรับคุณ, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
      <div>
        <a href="my_bookings.php" class="btn btn-outline-primary btn-sm me-2">ติดตามสถานะการจอง</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a>
      </div>
  </nav>

  <!-- HEADER / HERO SECTION -->
  <header class="hero-section py-5 mb-5 text-center">
    <div class="container">
      <span class="badge badge-pink rounded-pill px-3 py-2 fs-6 mb-3">Chubby Lens Camera Rental</span>
      <h1 class="fw-bold text-dark display-5 mb-2">บริการเช่ากล้องดิจิทัล Y2K</h1>
      <p class="text-muted fs-5 mb-0">โทนผิวละมุน แฟลชฉ่ำ งานผิวสวยสไตล์ Y2K ถ่ายง่าย พกพาสะดวก</p>
    </div>
  </header>

  <div class="container pb-5">

    <!-- SECTION: ขั้นตอนการเช่าง่ายๆ 4 ขั้นตอน -->
    <section class="mb-5 text-center">
      <h3 class="fw-bold text-dark mb-4">ขั้นตอนการเช่ากล้องกับ Chubby Lens</h3>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold text-pink">1. เช็ควันว่าง</h6>
            <p class="small text-muted mb-0">เลือกรุ่นที่ต้องการและระบุวันเช่า</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold text-pink">2. กรอกฟอร์ม & โอนเงิน</h6>
            <p class="small text-muted mb-0">แนบสลิปโอนเงินและบัตรยืนยันตัวตน</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold text-pink">3. ยืนยันการจอง</h6>
            <p class="small text-muted mb-0">แอดมินตรวจหลักฐานและยืนยันคิว</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-4 bg-white rounded-4 border h-100 shadow-sm">
            <h6 class="fw-bold text-pink">4. รับกล้อง & ส่งคืน</h6>
            <p class="small text-muted mb-0">รับกล้องตามนัด คืนปุ๊บรับมัดจำคืนปั๊บ</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION: ฟอร์มส่งคำขอจอง & ชำระเงิน -->
    <section>
      <div class="card card-custom p-4 p-md-5">
        <div class="border-bottom pb-3 mb-4">
          <h3 class="fw-bold text-dark mb-1">กรอกข้อมูลการจองเช่ากล้อง</h3>
          <p class="text-muted small mb-0">กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการยืนยันคิว</p>
        </div>

        <form action="process_booking.php" method="POST" enctype="multipart/form-data">

          <!-- เลือกรุ่นกล้อง -->
          <h5 class="fw-bold text-dark mb-3">1. เลือกรุ่นกล้องที่ต้องการ</h5>
          <div class="mb-4">
            <select name="camera_id" id="cameraSelect" class="form-select form-select-lg" onchange="loadCalendarForCamera(); calculateTotal();" required>
                <option value="" data-price="0">-- กรุณาเลือกกล้อง --</option>
                <?php 
                $cameras->data_seek(0);
                while($row =$cameras->fetch_assoc()): 
                ?>
                    <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['daily_rate']; ?>">
                        <?php echo htmlspecialchars($row['camera_name']); ?> (<?php echo number_format($row['daily_rate']); ?> บาท/วัน)
                    </option>
                <?php endwhile; ?>
            </select>
          </div>

          <!-- ตารางแสดงปฏิทินคิววันว่างประจำเดือน -->
          <div class="card p-4 border-0 bg-light rounded-4 mb-4 text-center">
            <h6 class="fw-bold text-dark mb-2">📅 ตารางคิวเช่าประจำเดือน (คลิกเลือกวันบนปฏิทินเพื่อใส่วันที่ให้อัตโนมัติ)</h6>
            <p class="text-muted small mb-3">คลิกครั้งแรก = วันเริ่มเช่า / คลิกครั้งที่สอง = วันส่งคืนกล้อง</p>
            
            <div class="calendar-box">
              <div class="calendar-header">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="prevMonth()">❮</button>
                <h6 class="fw-bold text-dark mb-0" id="calendarMonthTitle">เดือน</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="nextMonth()">❯</button>
              </div>
              <div class="calendar-weekdays">
                <span>อา</span><span>จ</span><span>อ</span><span>พ</span><span>พฤ</span><span>ศ</span><span>ส</span>
              </div>
              <div class="calendar-days" id="calendarDays"></div>
            </div>

            <div class="d-flex gap-3 justify-content-center mt-3 fs-7">
              <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill">
                🟢 วันที่ว่าง (คลิกเลือกจองได้)
              </span>
              <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 rounded-pill">
                🔴 วันติดคิวจองแล้ว (ห้ามเลือก)
              </span>
            </div>
          </div>

          <!-- ส่วนข้อมูลผู้เช่า -->
          <h5 class="fw-bold text-dark mb-3">2. ข้อมูลติดต่อผู้เช่า</h5>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">ชื่อ-นามสกุล</label>
              <input type="text" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">เบอร์โทรศัพท์ติดต่อ</label>
              <input type="tel" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['phone']); ?>" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-medium text-dark">Line ID</label>
              <input type="text" class="form-control py-2 bg-light" value="<?php echo htmlspecialchars($_SESSION['line_id']); ?>" readonly>
            </div>
          </div>

          <!-- ส่วนเลือกระยะเวลาเช่า -->
          <h5 class="fw-bold text-dark mb-3">3. กำหนดวันที่เช่า</h5>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">วันที่เริ่มเช่า (รับกล้อง) *</label>
              <input type="date" name="start_date" id="startDate" class="form-control py-2" onchange="manualDateInput()" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium text-dark">วันที่ส่งคืนกล้อง *</label>
              <input type="date" name="end_date" id="endDate" class="form-control py-2" onchange="manualDateInput()" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-medium text-dark">อุปกรณ์เสริมเพิ่มเติม (Add-ons)</label>
              <div class="d-flex flex-wrap gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="add_tripod" value="1" id="add1" onchange="calculateTotal()">
                  <label class="form-check-label text-secondary" for="add1">ขาตั้งกล้องพกพา (+30 บาท/วัน)</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="add_battery" value="1" id="add2" onchange="calculateTotal()">
                  <label class="form-check-label text-secondary" for="add2">แบตสำรองก้อนที่ 2 (+30 บาท/วัน)</label>
                </div>
              </div>
            </div>

            <!-- กล่องสรุปการคำนวณราคาอัตโนมัติ -->
            <div class="col-12 mt-4">
              <div class="price-box p-3 p-md-4">
                <h6 class="fw-bold text-dark mb-3">สรุปรายการคำนวณค่าเช่าอัตโนมัติ</h6>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">จำนวนวันที่เช่า:</span>
                  <span class="fw-bold text-dark" id="rentalDays">0 วัน</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">ค่าเช่ากล้อง:</span>
                  <span class="fw-bold text-dark" id="rentalFee">0 บาท</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">ค่าอุปกรณ์เสริมเพิ่มเติม:</span>
                  <span class="fw-bold text-dark" id="addonFee">0 บาท</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-secondary">เงินประกันตัวเครื่อง (ได้คืนเต็มจำนวน):</span>
                  <span class="fw-bold text-dark">150 บาท</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="fw-bold text-dark fs-5">ยอดโอนเงินสุทธิ:</span>
                  <span class="fw-bold text-danger fs-3" id="totalAmount">150 บาท</span>
                </div>

                <div class="p-3 bg-white rounded-3 border small text-muted">
                  <strong class="text-dark d-block mb-1">เงื่อนไขการคืนเงินประกัน/มัดจำ (150 บาท):</strong>
                  • ทางร้านจะโอนเงินคืนทันที หลังจากได้รับกล้องคืนและตรวจเช็กสภาพอุปกรณ์เรียบร้อยแล้ว<br>
                  • กล้องต้องอยู่ในสภาพสมบูรณ์ ไม่ชำรุดเสียหาย หรือสูญหาย
                </div>
              </div>
            </div>

            <div class="col-12 mt-3">
              <label class="form-label fw-medium text-dark">หมายเหตุเพิ่มเติม</label>
              <textarea name="note" class="form-control" rows="2" placeholder="เช่น ขอรับกล้องช่วงเช้า 09.00 น."></textarea>
            </div>
          </div>

          <!-- ส่วนชำระเงิน & แนบหลักฐาน -->
          <h5 class="fw-bold text-dark mb-3">4. ชำระเงิน & ยืนยันตัวตน</h5>
          <div class="p-4 rounded-4 bg-light border mb-4">
            
            <div class="card card-body border-0 shadow-sm mb-4">
              <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div>
                  <small class="text-muted d-block">ธนาคารกสิกรไทย (K-Bank)</small>
                  <span class="fs-4 fw-bold text-dark">190-0-18743-2</span>
                  <small class="text-muted d-block">ชื่อบัญชี: Chubby Lens </small>
                </div>
                <div class="mt-3 mt-sm-0 text-center">
                  <img src="my_qr .jpg" alt="QR Code ชำระเงิน" class="img-fluid rounded shadow-sm" style="max-width: 160px;">
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-medium text-dark small">แนบสลิปการโอนเงิน *</label>
                <input type="file" name="slip_image" class="form-control" accept="image/*" required>
                <small class="text-muted fs-7">ยอดโอนตามระบบสรุปราคาข้างต้น</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium text-dark small">แนบรูปถ่ายบัตรประชาชน *</label>
                <input type="file" name="id_card_image" class="form-control" accept="image/*" required>
                <small class="text-muted fs-7">เพื่อความปลอดภัยในการยืนยันตัวตนผู้เช่า</small>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-medium text-dark small">บัญชีสำหรับรับมัดจำคืน *</label>
                <input type="text" name="refund_account" class="form-control" placeholder="ระบุธนาคาร + เลขบัญชี และชื่อบัญชี" required>
              </div>
            </div>

          </div>

          <!-- ปุ่มส่งคำขอจอง -->
          <button type="submit" class="btn btn-lg w-100 py-3 fw-bold shadow-sm text-white" style="background-color: #ff6b8b;">
              ส่งคำขอจองเช่ากล้อง
          </button>

        </form>
      </div>
    </section>

  </div>

  <footer class="text-center py-4 text-muted border-top bg-white mt-5">
    <small>© 2026 Chubby Lens Camera Rental</small>
  </footer>

  <!-- Script คำนวณราคาอัตโนมัติ & สคริปต์ปฏิทินวันว่าง -->
  <script>
    const bookedData = <?php echo json_encode($booked_data); ?>;

    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();
    let selectedStart = null;
    let selectedEnd = null;

    const monthNamesTH = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    function isBooked(dateStr) {
      const cameraSelect = document.getElementById('cameraSelect');
      const cameraId = cameraSelect.value;
      if (!cameraId) return false;

      const cameraBookings = bookedData[cameraId] || [];
      for (let b of cameraBookings) {
        if (dateStr >= b.from && dateStr <= b.to) {
          return true;
        }
      }
      return false;
    }

    function formatDateISO(year, month, day) {
      let m = (month + 1).toString().padStart(2, '0');
      let d = day.toString().padStart(2, '0');
      return ${year}-${m}-${d};
    }

    function renderCalendar() {
      document.getElementById('calendarMonthTitle').innerText = ${monthNamesTH[currentMonth]} ${currentYear + 543};
      const calendarDays = document.getElementById('calendarDays');
      calendarDays.innerHTML = '';

      const firstDay = new Date(currentYear, currentMonth, 1).getDay();
      const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

      const today = new Date();
      today.setHours(0,0,0,0);

      for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'cal-day empty';
        calendarDays.appendChild(emptyCell);
      }

      for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        const dateObj = new Date(currentYear, currentMonth, day);
        const dateStr = formatDateISO(currentYear, currentMonth, day);

        dayCell.innerText = day;
        dayCell.className = 'cal-day';

        const isPast = dateObj < today;
        const booked = isBooked(dateStr);

        if (isPast) {
          dayCell.classList.add('past');
        } else if (booked) {
          dayCell.classList.add('booked');
        } else {
          dayCell.classList.add('available');

          if (selectedStart === dateStr) dayCell.classList.add('selected-start');
          if (selectedEnd === dateStr) dayCell.classList.add('selected-end');
          if (selectedStart && selectedEnd && dateStr > selectedStart && dateStr < selectedEnd) {
            dayCell.classList.add('selected-range');
          }

          dayCell.onclick = () => selectCalendarDate(dateStr);
        }

        calendarDays.appendChild(dayCell);
      }
    }

    function selectCalendarDate(dateStr) {
      if (!selectedStart || (selectedStart && selectedEnd)) {
        selectedStart = dateStr;
        selectedEnd = null;
      } else if (selectedStart && !selectedEnd) {
        if (dateStr < selectedStart) {
          selectedStart = dateStr;
        } else {
          selectedEnd = dateStr;
        }
      }

      document.getElementById('startDate').value = selectedStart || '';
      document.getElementById('endDate').value = selectedEnd || '';

      renderCalendar();
      calculateTotal();
    }

    function manualDateInput() {
      selectedStart = document.getElementById('startDate').value;
      selectedEnd = document.getElementById('endDate').value;
      renderCalendar();
      calculateTotal();
    }

    function loadCalendarForCamera() {
      renderCalendar();
    }

    function prevMonth() {
      currentMonth--;
      if (currentMonth < 0) { currentMonth = 11; currentYear--; }
      renderCalendar();
    }

    function nextMonth() {
      currentMonth++;
      if (currentMonth > 11) { currentMonth = 0; currentYear++; }
      renderCalendar();
    }

    function calculateTotal() {
      const cameraSelect = document.getElementById('cameraSelect');
      const selectedOption = cameraSelect.options[cameraSelect.selectedIndex];
      const cameraPrice = parseFloat(selectedOption ? selectedOption.getAttribute('data-price') : 0) || 0;

      const startDateVal = document.getElementById('startDate').value;
      const endDateVal = document.getElementById('endDate').value;
      const add1 = document.getElementById('add1').checked;
      const add2 = document.getElementById('add2').checked;

      let days = 0;
      let rentalFee = 0;
      let addonFee = 0;
      const depositFee = 150; // ปรับลดมัดจำเหลือ 150 บาท

      if (startDateVal && endDateVal && cameraPrice > 0) {
        const start = new Date(startDateVal);
        const end = new Date(endDateVal);

        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        if (diffDays > 0) {
          days = diffDays;
          rentalFee = days * cameraPrice;

          let addonRate = 0;
          if (add1) addonRate += 30; // ปรับลดค่าอุปกรณ์เสริมเหลือ 30 บาท/วัน
          if (add2) addonRate += 30;
          addonFee = days * addonRate;
        }
      }

      const totalAmount = rentalFee + addonFee + (days > 0 ? depositFee : 0);

      document.getElementById('rentalDays').innerText = days + " วัน";
      document.getElementById('rentalFee').innerText = rentalFee.toLocaleString() + " บาท";
      document.getElementById('addonFee').innerText = addonFee.toLocaleString() + " บาท";
      document.getElementById('totalAmount').innerText = (days > 0 ? totalAmount : depositFee).toLocaleString() + " บาท";
    }

    document.addEventListener('DOMContentLoaded', function() {
      const cameraSelect = document.getElementById('cameraSelect');
      if (cameraSelect.options.length === 2) {
        cameraSelect.selectedIndex = 1;
      }
      renderCalendar();
      calculateTotal();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
>>>>>>> 962e6859cedf71e7faa250a3eeef3121e52b6e61
