{{-- resources/views/layouts/topbar.blade.php --}}
@php
  // ธีมสี (กัน undefined)
  $teal  = $teal  ?? '#0B7F6F';
  $teal2 = $teal2 ?? '#0B5B6B';

  // ตัวกรอง (กัน undefined)
  $district    = $district ?? '';
  $subdistrict = $subdistrict ?? '';
  $sex         = $sex ?? '';
  $age_range   = $age_range ?? '';

  // map ช่วงอายุ (กัน undefined)
  $AGE_RANGES = $AGE_RANGES ?? [
    '0-15'  => '0 – 15 ปี',
    '16-28' => '16 – 28 ปี',
    '29-44' => '29 – 44 ปี',
    '45-59' => '45 – 59 ปี',
    '60-78' => '60 – 78 ปี',
    '79-97' => '79 – 97 ปี',
    '98+'   => '98 ปีขึ้นไป',
  ];
@endphp

<nav class="navbar navbar-expand-lg bg-white bg-opacity-75 border-bottom sticky-top"
     style="backdrop-filter: blur(8px);">
  <div class="container-fluid px-3 px-lg-4">

    <button class="btn btn-outline-success d-lg-none me-2"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar">
      <i class="bi bi-list"></i>
    </button>

    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
      <img src="{{ asset('images/phatthalung-logo.png') }}" alt="logo"
           class="rounded-3 border"
           style="width:38px;height:38px;object-fit:cover;">
      <div class="lh-sm">
        <div class="fw-bold" style="color:{{ $teal2 }}">Phatthalung People Map</div>
        <div class="text-muted small" style="font-size:.75rem;">ระบบฐานข้อมูลพัทลุงโมเดล</div>
      </div>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2 flex-wrap justify-content-end">

     

      @if(!empty($district))
        <span class="badge rounded-pill text-bg-light border">
          <i class="bi bi-geo-alt-fill me-1 text-success"></i> อ.{{ $district }}
        </span>
      @endif

      @if(!empty($subdistrict))
        <span class="badge rounded-pill text-bg-light border">
          <i class="bi bi-pin-map-fill me-1 text-success"></i> ต.{{ $subdistrict }}
        </span>
      @endif

      {{-- ✅ ใช้ $sex (เพราะ filter ชื่อ sex) --}}
      @if(!empty($sex))
        <span class="badge rounded-pill text-bg-light border">
          <i class="bi bi-gender-ambiguous me-1 text-success"></i> เพศ: {{ $sex }}
        </span>
      @endif

      @if(!empty($age_range))
        <span class="badge rounded-pill text-bg-light border">
          <i class="bi bi-hourglass-split me-1 text-success"></i>
          {{ $AGE_RANGES[$age_range] ?? $age_range }}
        </span>
      @endif

      <div class="dropdown">
        <button class="btn btn-success btn-sm dropdown-toggle rounded-pill px-3"
                style="background:{{ $teal }};border-color:{{ $teal }};"
                data-bs-toggle="dropdown">
          <i class="bi bi-grid-1x2-fill me-1"></i> เมนู
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4">
          

          <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
          <li><a class="dropdown-item" href="{{ url('/test') }}"><i class="bi bi-heart-pulse-fill me-2"></i>ข้อมูลสุขภาพ</a></li>
          <li><a class="dropdown-item" href="{{ url('/welfare') }}"><i class="bi bi-gift-fill me-2"></i>ข้อมูลสวัสดิการ</a></li>
          <li><a class="dropdown-item" href="{{ route('household_64') }}"><i class="bi bi-table me-2"></i>ตารางครัวเรือน</a></li>
          <li>
            <a class="dropdown-item" href="{{ route('housing.dashboard') }}">
              <i class="bi bi-house-door-fill me-2"></i>สภาพที่อยู่อาศัยและสาธารณูปโภค
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>

          @if(session('user_firstname'))
            <li>
              <span class="dropdown-item-text small text-muted">
                <i class="bi bi-person-circle me-2"></i>{{ session('user_firstname') }}
              </span>
            </li>
            <li><a class="dropdown-item text-danger" href="{{ url('/logout') }}"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a></li>
          @else
            <li><a class="dropdown-item" href="{{ url('/login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ</a></li>
            <li><a class="dropdown-item" href="{{ url('/register') }}"><i class="bi bi-person-plus me-2"></i>ลงทะเบียน</a></li>
          @endif
        </ul>
      </div>

    </div>
  </div>
</nav>
