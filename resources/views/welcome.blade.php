<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Phatthalung People Map</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
</head>

<body 
  class="m-0"
  style="font-family:'Prompt',system-ui,sans-serif;
         min-height:100vh;
         background:linear-gradient(135deg,#CFEFF3 0%,#DFF7EF 50%,#F0F8FB 100%);"
>

  <!-- กล่องหัวบน -->
  <div class="container-fluid p-0">
    <div class="card border-0 shadow-sm rounded-0 bg-white bg-opacity-90 w-100">
      <div class="card-body d-flex flex-column flex-lg-row align-items-center justify-content-center gap-4 py-4 px-3 px-lg-5">

        <div class="text-center text-lg-start mb-3 mb-lg-0">
          <img 
            src="{{ asset('images/phatthalung-logo.png') }}"
            alt="Phatthalung People Map Logo"
            class="img-fluid mx-auto d-block"
            style="max-width:150px;transition:all .3s ease;"
            onmouseover="this.style.transform='scale(1.08)';"
            onmouseout="this.style.transform='scale(1)';"
          >
        </div>

        <div class="text-center text-lg-start">
          <h3 class="fw-bold mb-2" style="color:#0B5B6B;">Phatthalung People Map</h3>
          <p class="text-secondary small mb-3">
            ระบบด้านสุขภาพจังหวัดพัทลุง
          </p>

          <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
            <a href="{{ url('/') }}" 
               class="btn btn-sm px-4 fw-semibold shadow-sm" 
               style="border-radius:50px;background-color:#0B7F6F;color:#fff;">
              หน้าหลัก
            </a>
            <a href="{{ url('/login') }}" 
               class="btn btn-sm px-4 fw-semibold shadow-sm" 
               style="border-radius:50px;background-color:#0B7F6F;color:#fff;">
              เข้าสู่ระบบ
            </a>
            <a href="{{ url('/register') }}" 
               class="btn btn-sm px-4 fw-semibold shadow-sm" 
               style="border-radius:50px;background-color:#0B7F6F;color:#fff;">
              ลงทะเบียน
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

<link rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">

  <!-- ปุ่มจังหวัด -->
  <a href="#" 
     class="btn btn-sm mb-3 d-inline-flex align-items-center gap-1 shadow-sm"
     style="background-color:#0B7F6F; color:#fff; border-radius:6px;">
    <i class="bi bi-house-door-fill"></i>
    จังหวัดพัทลุง
  </a>

  <!-- กล่องลิสต์อำเภอ -->
  <div class="p-3 bg-white rounded shadow-sm border">

    <div class="d-flex flex-wrap gap-2 small">

      <!-- ลิงก์อำเภอ -->
      <a href="#" 
         class="text-decoration-none px-2 py-1 rounded"
         style="color:#0B7F6F;">
        เมืองพัทลุง
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        กงหรา
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        เขาชัยสน
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ตะโหมด
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ควนขนุน
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ปากพะยูน
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ศรีบรรพต
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ป่าบอน
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        บางแก้ว
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ป่าพะยอม
      </a>
      <span class="text-secondary">/</span>

      <a href="#" class="text-decoration-none px-2 py-1 rounded" style="color:#0B7F6F;">
        ศรีนครินทร์
      </a>

    </div>
  </div>

</div>

  <!-- 🔽 ส่วน Dashboard -->
<div class="container my-5">
  <h4 class="fw-bold mb-4 text-center" style="color:#0B5B6B;">
    Dashboard สรุปข้อมูลภาพรวม
  </h4>

  <div class="row g-4">
<div class="col-md-4">
      <div 
        class="card border-0 shadow-lg rounded-4 text-center p-3 bg-white bg-opacity-75"
        style="transition:0.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <div class="card-body">
          <h6 class="fw-semibold text-secondary mb-1">จังหวัดพัทลุง</h6>
          <h3 class="fw-bold" style="color:#0B7F6F;">11 อำเภอ</h3>
          <p class="small text-muted mb-0">ข้อมูลจากระบบ DSS</p>
          
        </div>
      </div>
    </div>
    <!-- การ์ดแบบสวย -->
    <div class="col-md-4">
      <div 
        class="card border-0 shadow-lg rounded-4 text-center p-3 bg-white bg-opacity-75"
        style="transition:0.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <div class="card-body">
          <h6 class="fw-semibold text-secondary mb-1">จำนวนครัวเรือนทั้งหมด</h6>
          <h3 class="fw-bold" style="color:#0B7F6F;">1,245</h3>
          <p class="small text-muted mb-0">(ทั้งหมด)</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div 
        class="card border-0 shadow-lg rounded-4 text-center p-3 bg-white bg-opacity-75"
        style="transition:0.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <div class="card-body">
          <h6 class="fw-semibold text-secondary mb-1">จำนวนสมาชิก</h6>
          <h3 class="fw-bold" style="color:#E67E22;">2,000</h3>
          <p class="small text-muted mb-0">(ทั้งหมด)</p>
        </div>
      </div>
    </div>

   

  </div>
</div>

  <div class="container my-5">
 

  <div class="row g-4">

    <div class="col-md-3">
      <div 
        class="card border-0 shadow-lg rounded-4 p-3 bg-white bg-opacity-75 text-center"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="fw-semibold text-secondary mb-1">ผู้ป่วยเรื้อรัง</h6>
        <h3 class="fw-bold" style="color:#0B7F6F;">2,340</h3>
        <p class="small text-muted mb-0">DM • HT • CKD</p>
      </div>
    </div>

    <div class="col-md-3">
      <div 
        class="card border-0 shadow-lg rounded-4 p-3 bg-white bg-opacity-75 text-center"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="fw-semibold text-secondary mb-1">ผู้พิการ</h6>
        <h3 class="fw-bold text-warning">980</h3>
        <p class="small text-muted mb-0">ขึ้นทะเบียนแล้ว</p>
      </div>
    </div>

    <div class="col-md-3">
      <div 
        class="card border-0 shadow-lg rounded-4 p-3 bg-white bg-opacity-75 text-center"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="fw-semibold text-secondary mb-1">ผู้สูงอายุอยู่ลำพัง</h6>
        <h3 class="fw-bold" style="color:#E67E22;">420</h3>
        <p class="small text-muted mb-0">ต้องดูแลต่อเนื่อง</p>
      </div>
    </div>

    <div class="col-md-3">
      <div 
        class="card border-0 shadow-lg rounded-4 p-3 bg-white bg-opacity-75 text-center"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.03)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="fw-semibold text-secondary mb-1">ผู้สูงติดเตียง</h6>
        <h3 class="fw-bold" style="color:#0B5B6B;">310</h3>
        <p class="small text-muted mb-0">ข้อมูลจาก พม.</p>
      </div>
    </div>

  </div>
</div>
<div class="container mb-5">
 

  <div class="row g-4">

    <div class="col-lg-4">
      <div 
        class="card border-0 shadow-lg rounded-4 h-100 p-3 bg-white bg-opacity-75"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.02)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="text-center text-muted mb-3">สัดส่วนผู้ใช้</h6>
        <canvas id="chartUsersType"></canvas>
      </div>
    </div>

    <div class="col-lg-4">
      <div 
        class="card border-0 shadow-lg rounded-4 h-100 p-3 bg-white bg-opacity-75"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.02)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="text-center text-muted mb-3">ครัวเรือนต่ออำเภอ</h6>
        <canvas id="chartHouseholdsDist"></canvas>
      </div>
    </div>

    <div class="col-lg-4">
      <div 
        class="card border-0 shadow-lg rounded-4 h-100 p-3 bg-white bg-opacity-75"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.02)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <h6 class="text-center text-muted mb-3">แนวโน้มการช่วยเหลือ</h6>
        <canvas id="chartAssistanceTrend"></canvas>
      </div>
    </div>

  </div>
</div>



<!-- 🔽 กราฟด้านสุขภาพ (เวอร์ชันสวย ลอย มี hover) -->
<div class="container mb-5">
 

  <div class="row g-4">

    <!-- โรคเรื้อรัง -->
    <div class="col-lg-6">
      <div 
        class="card border-0 shadow-lg rounded-4 h-100 bg-white bg-opacity-75"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.02)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <div class="card-body">
          <h6 class="text-center fw-semibold mb-3" style="color:#0B5B6B;">
            สถิติกลุ่มโรคเรื้อรัง
          </h6>
          <canvas id="chartDisease"></canvas>
        </div>
      </div>
    </div>

    <!-- ผู้สูงอายุตามอำเภอ -->
    <div class="col-lg-6">
      <div 
        class="card border-0 shadow-lg rounded-4 h-100 bg-white bg-opacity-75"
        style="transition:.3s;"
        onmouseover="this.style.transform='scale(1.02)'"
        onmouseout="this.style.transform='scale(1)'"
      >
        <div class="card-body">
          <h6 class="text-center fw-semibold mb-3" style="color:#0B5B6B;">
            ผู้สูงอายุตามอำเภอ
          </h6>
          <canvas id="chartElderDistrict"></canvas>
        </div>
      </div>
    </div>

  </div>
</div>


<script>
  const green = "#0B7F6F";
  const orange2 = "#E67E22";
  const blue2 = "#0F9BD8";
  const gray2 = "#6c757d";

  /* 🔹 กราฟโรคเรื้อรัง (Bar) */
  new Chart(document.getElementById('chartDisease'), {
    type: 'bar',
    data: {
      labels: ['เบาหวาน', 'ความดัน', 'หัวใจ', 'หลอดเลือดสมอง', 'ไตเรื้อรัง'],
      datasets: [{
        label: 'จำนวนผู้ป่วย',
        data: [820, 930, 340, 220, 180],
        backgroundColor: [green, green, green, green, green]
      }]
    },
    options: {
      plugins: { legend: { display: false }},
      scales: {
        x: { ticks: { color: gray2 }},
        y: { ticks: { color: gray2 }, beginAtZero:true }
      }
    }
  });

  /* 🔹 กราฟผู้สูงอายุตามอำเภอ (Line) */
  new Chart(document.getElementById('chartElderDistrict'), {
    type: 'line',
    data: {
      labels: ['เมือง', 'กงหรา', 'ควนขนุน', 'ปากพะยูน', 'ศรีนครินทร์', 'เขาชัยสน'],
      datasets: [{
        label: 'จำนวนผู้สูงอายุ',
        data: [120, 70, 95, 60, 40, 55],
        borderColor: green,
        backgroundColor: 'rgba(11,127,111,.15)',
        fill: true,
        tension: .3
      }]
    },
    options: {
      plugins: { legend: { display: false }},
      scales: {
        x: { ticks: { color: gray2 }},
        y: { ticks: { color: gray2 }, beginAtZero:true }
      }
    }
  });
</script>

  <!-- Script Bootstrap + Chart -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const teal = '#0B7F6F';
    const orange = '#E67E22';
    const blue = '#0F9BD8';
    const gray = '#6c757d';

    // Doughnut Chart
    new Chart(document.getElementById('chartUsersType'), {
      type: 'doughnut',
      data: {
        labels: ['หน่วยงานภาครัฐ', 'นักวิจัย', 'อาสาสมัคร', 'ดูแลระบบ'],
        datasets: [{
          data: [35, 25, 30, 10],
          backgroundColor: [teal, orange, blue, '#4FB9A9']
        }]
      },
      options: {
        plugins: { legend: { position: 'bottom', labels: { color: gray } } },
        cutout: '60%'
      }
    });

    // Bar Chart
    new Chart(document.getElementById('chartHouseholdsDist'), {
      type: 'bar',
      data: {
        labels: ['เมือง', 'กงหรา', 'เขาชัยสน', 'ควนขนุน', 'ป่าบอน', 'ศรีนครินทร์'],
        datasets: [{
          label: 'ครัวเรือน',
          data: [240, 110, 180, 220, 140, 95],
          backgroundColor: teal
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: gray } },
          y: { ticks: { color: gray }, beginAtZero: true }
        }
      }
    });

    // Line Chart
    new Chart(document.getElementById('chartAssistanceTrend'), {
      type: 'line',
      data: {
        labels: ['มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.'],
        datasets: [{
          data: [40, 55, 62, 70, 88, 95],
          borderColor: teal,
          backgroundColor: 'rgba(11,127,111,0.1)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: gray } },
          y: { ticks: { color: gray }, beginAtZero: true }
        }
      }
    });
  </script>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 
</body>
</html>
