<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ลงทะเบียนผู้ใช้งานระบบ | Phatthalung People Map</title>

 <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
ั
<div class="container">
  <div class="row align-items-center justify-content-center g-4">
    <!-- กล่องลงทะเบียน -->
    <div class="col-md-10 col-lg-8">
      <div
        class="card border-0 shadow-lg rounded-5 p-2 p-md-3 text-center mx-auto"
        style="max-width:500px;background:rgba(255,255,255,0.95);backdrop-filter:blur(6px);padding-top:1.25rem!important;padding-bottom:1.25rem!important;"
      >
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

        <h5 class="fw-bold mb-2" style="color:#0B5B6B;">Phatthalung People Map</h5>
        <p class="text-muted small mb-3">กรุณากรอกข้อมูลเพื่อลงทะเบียนเข้าใช้งานระบบ:</p>

        <form method="POST" action="{{ url('/register') }}">
          @csrf
          <div class="row g-2">
            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">ชื่อผู้ใช้</label>
              <input type="text" name="username" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="กรอกชื่อผู้ใช้" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">รหัสผ่าน</label>
              <input type="password" name="password" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">ชื่อ</label>
              <input type="text" name="first_name" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="ชื่อ" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">นามสกุล</label>
              <input type="text" name="last_name" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="นามสกุล" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">เลขบัตรประชาชน</label>
              <input type="text" name="citizen_id" maxlength="13" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="เลขบัตร 13 หลัก" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">เบอร์โทรศัพท์</label>
              <input type="tel" name="phone" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="เช่น 0812345678" required>
            </div>

            <div class="col-md-6 text-start">
              <label class="form-label small text-secondary">อีเมล</label>
              <input type="email" name="email" class="form-control form-control-sm rounded-3 shadow-sm" placeholder="example@email.com" required>
            </div>

            <div class="col-md-6 text-start">
  <label class="form-label small text-secondary">ประเภทผู้ใช้</label>
  <select name="user_type" class="form-select form-select-sm rounded-3 shadow-sm" required>
      <option value="">-- เลือกประเภทผู้ใช้ --</option>
      <option value="1">หน่วยงานภาครัฐ</option>
      <option value="2">ดูแลระบบ</option>
      <option value="3">ผู้ช่วยวิจัย/นักวิจัย/OM</option>
  </select>
</div>

          </div>

          <button
            type="submit"
            class="btn w-100 fw-semibold py-2 mt-3 rounded-pill shadow-sm"
            style="background-color:#0B7F6F;border:none;color:#fff;"
            onmouseover="this.style.backgroundColor='#09685C'"
            onmouseout="this.style.backgroundColor='#0B7F6F'"
          >
            ลงทะเบียน
          </button>
        </form>

        <div class="text-center mt-2">
          <a href="{{ url('/') }}" class="small text-muted text-decoration-none">
            ← ย้อนกลับสู่หน้าหลัก
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif



<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
