<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Phatthalung People Map</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
  <div class="container">
    <div class="row align-items-center justify-content-center g-4">

      <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 text-center mx-auto"
             style="max-width:360px;background:rgba(255,255,255,0.93);backdrop-filter:blur(6px);">

          <!-- โลโก้ -->
          <img
            src="{{ asset('images/phatthalung-logo.png') }}"
            alt="Phatthalung People Map Logo"
            class="img-fluid mb-2 mx-auto d-block"
            style="
              max-width:150px;
              filter:drop-shadow(0 3px 8px rgba(0,0,0,0.15));
              transition:all 0.3s ease;
            "
            onmouseover="this.style.transform='scale(1.1) translateY(-4px)'; this.style.filter='drop-shadow(0 6px 15px rgba(0,0,0,0.25))';"
            onmouseout="this.style.transform='scale(1) translateY(0)'; this.style.filter='drop-shadow(0 3px 8px rgba(0,0,0,0.15))';"
          />

          <h5 class="fw-bold mb-1" style="color:#0B5B6B;">Phatthalung People Map</h5>
          <p class="text-muted small mb-3">ระบบฐานข้อมูลพัทลุงโมเดล</p>

          {{-- แก้ตรงนี้: ปิด quote ให้ครบ --}}
          <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-2 text-start">
              <label class="form-label text-secondary small mb-1">ชื่อผู้ใช้</label>
              <input
                type="text"
                name="username"
                class="form-control form-control-sm rounded-3 shadow-sm"
                placeholder="กรอกชื่อผู้ใช้"
                required>
            </div>

            <div class="mb-3 text-start">
              <label class="form-label text-secondary small mb-1">รหัสผ่าน</label>
              <input
                type="password"
                name="password"
                class="form-control form-control-sm rounded-3 shadow-sm"
                placeholder="กรอกรหัสผ่าน"
                required>

              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label small text-secondary" for="remember">
                  จดจำฉันไว้
                </label>
              </div>
            </div>

            <button
              type="submit"
              class="btn w-100 fw-semibold py-2 rounded-pill shadow-sm"
              onmouseover="this.style.backgroundColor='#09685C'"
              onmouseout="this.style.backgroundColor='#0B7F6F'"
              style="background-color:#0B7F6F;border:none;color:#fff;">
              เข้าสู่ระบบ
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="{{ url('/') }}" class="small text-muted text-decoration-none">
              ← ย้อนกลับสู่หน้าหลัก
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
