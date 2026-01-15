<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Phatthalung People Map</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body{ font-family:'Prompt',system-ui,sans-serif; }
    .app-bg{ background:linear-gradient(135deg,#CFEFF3 0%,#DFF7EF 50%,#F0F8FB 100%); min-height:100vh; }
    .shadow-soft{ box-shadow: 0 12px 28px rgba(2, 6, 23, .08) !important; }
    .sidebar{
      width: 280px;
      position: sticky;
      top: 74px;
      height: calc(100vh - 90px);
      overflow: auto;
    }
    .nav-pills .nav-link.active{ background:#0B7F6F !important; }
    .nav-pills .nav-link{ border-radius: 14px; padding:10px 12px; }
    .kpi-icon{
      width:46px;height:46px;
      display:flex;align-items:center;justify-content:center;
      border-radius:16px;
    }
  </style>
</head>

@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  $view = $view ?? request('view', 'district');

  $districtList = [
    'เมืองพัทลุง','กงหรา','เขาชัยสน','ควนขนุน','ตะโหมด','บางแก้ว',
    'ปากพะยูน','ศรีบรรพต','ป่าบอน','ป่าพะยอม','ศรีนครินทร์'
  ];

  $district    = $district ?? request('district','');
  $subdistrict = $subdistrict ?? request('subdistrict','');
  $human_Sex   = $human_Sex ?? request('human_Sex','');
  $age_range   = $age_range ?? request('age_range','');

  $AGE_RANGES  = $AGE_RANGES ?? [
    ''      => 'อายุ: ทั้งหมด',
    '0-15'  => '0–15 ปี',
    '16-28' => '16–28 ปี',
    '29-44' => '29–44 ปี',
    '45-59' => '45–59 ปี',
    '60-78' => '60–78 ปี',
    '79-97' => '79–97 ปี',
    '98+'   => '98 ปีขึ้นไป',
  ];

  $subdistrictList = $subdistrictList ?? collect([]);

  $totalHouseholds = $totalHouseholds ?? 0;
  $totalMembers    = $totalMembers ?? 0;

  $welfareTotal       = $welfareTotal ?? 0;
  $welfareReceived    = $welfareReceived ?? 0;
  $welfareNotReceived = $welfareNotReceived ?? 0;

  $labels   = $labels ?? [];
  $datasets = $datasets ?? [];
  $labelDistrictMap = $labelDistrictMap ?? [];
@endphp

<body class="app-bg">

  {{-- Topbar --}}
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

        <span class="badge rounded-pill text-bg-light border">
          <i class="bi bi-calendar-event me-1"></i> ปี 2564
        </span>

        @if($district)
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-geo-alt-fill me-1 text-success"></i> อ.{{ $district }}
          </span>
        @endif
        @if($subdistrict)
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-pin-map-fill me-1 text-success"></i> ต.{{ $subdistrict }}
          </span>
        @endif
        @if($human_Sex)
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-gender-ambiguous me-1 text-success"></i> เพศ: {{ $human_Sex }}
          </span>
        @endif
        @if($age_range)
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-hourglass-split me-1 text-success"></i> {{ $AGE_RANGES[$age_range] ?? $age_range }}
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
            <li><a class="dropdown-item" href="{{ route('household_64') }}"><i class="bi bi-table me-2"></i>ตารางครัวเรือน 2564</a></li>
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

  {{-- Layout --}}
  <div class="container-fluid px-3 px-lg-4 py-3">
    <div class="row g-3">

      {{-- Sidebar (desktop) --}}
      <div class="col-lg-3 d-none d-lg-block">
        <div class="bg-white bg-opacity-75 border rounded-4 p-3 sidebar shadow-soft">

          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="kpi-icon" style="background:rgba(11,127,111,.12);color:{{ $teal }};">
              <i class="bi bi-grid-1x2-fill"></i>
            </div>
            <div>
              <div class="fw-bold" style="color:{{ $teal2 }}">เมนูระบบ</div>
              <div class="text-muted small">ปีข้อมูล: 2564</div>
            </div>
          </div>

          <div class="nav nav-pills flex-column gap-1 mb-3">
            <a class="nav-link {{ request()->is('dashboard') ? 'active' : 'text-dark' }}" href="{{ route('dashboard') }}">
              <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a class="nav-link {{ request()->is('test') ? 'active' : 'text-dark' }}" href="{{ url('/test') }}">
              <i class="bi bi-heart-pulse-fill me-2"></i>ข้อมูลสุขภาพ
            </a>
            <a class="nav-link {{ request()->is('welfare') ? 'active' : 'text-dark' }}" href="{{ url('/welfare') }}">
              <i class="bi bi-gift-fill me-2"></i>ข้อมูลสวัสดิการ
            </a>
            <a class="nav-link text-dark" href="{{ route('household_64') }}">
              <i class="bi bi-table me-2"></i>ตารางครัวเรือน 2564
            </a>

            @if(!session('user_firstname'))
              <a class="nav-link text-dark" href="{{ url('/login') }}">
                <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
              </a>
              <a class="nav-link text-dark" href="{{ url('/register') }}">
                <i class="bi bi-person-plus me-2"></i>ลงทะเบียน
              </a>
            @else
              <a class="nav-link text-danger" href="{{ url('/logout') }}">
                <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
              </a>
            @endif
          </div>

          {{-- Filters --}}
          <div class="border-top pt-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="text-muted small">ตัวกรองข้อมูล</div>
              <span class="badge rounded-pill text-bg-light border">
                <i class="bi bi-funnel-fill me-1 text-success"></i> Filters
              </span>
            </div>

            {{-- District dropdown --}}
            <div class="dropdown mb-2">
              <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                      style="background:{{ $teal }};border-color:{{ $teal }};"
                      data-bs-toggle="dropdown"
                      data-bs-auto-close="outside">
                <span class="text-truncate">
                  <i class="bi bi-geo-alt-fill me-1"></i>{{ $district ? "อ.$district" : "เลือกอำเภอ" }}
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>

              <ul class="dropdown-menu w-100 shadow border-0 rounded-4 dropdown-menu-scrollable p-2">
                @foreach($districtList as $d)
                  <li>
                    <a class="dropdown-item rounded-3 py-2"
                       href="{{ route('dashboard', array_filter([
                          'district'    => $d,
                          'subdistrict' => '',
                          'human_Sex'   => $human_Sex,
                          'age_range'   => $age_range,
                          'view'        => $view
                       ])) }}">
                      <i class="bi bi-dot me-1"></i>อ.{{ $d }}
                    </a>
                  </li>
                @endforeach
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger rounded-3 py-2"
                     href="{{ route('dashboard', array_filter([
                        'subdistrict' => '',
                        'human_Sex'   => $human_Sex,
                        'age_range'   => $age_range,
                        'view'        => $view
                     ])) }}">
                    <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองอำเภอ
                  </a>
                </li>
              </ul>
            </div>

            {{-- Sex dropdown --}}
            <div class="dropdown mb-2">
              <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                      style="background:{{ $teal }};border-color:{{ $teal }};"
                      data-bs-toggle="dropdown"
                      data-bs-auto-close="outside">
                <span class="text-truncate">
                  <i class="bi bi-gender-ambiguous me-1"></i>{{ $human_Sex ? "เพศ: $human_Sex" : "เพศ: ทั้งหมด" }}
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>

              <ul class="dropdown-menu w-100 shadow border-0 rounded-4 p-2">
                <li>
                  <a class="dropdown-item rounded-3 py-2"
                     href="{{ route('dashboard', array_filter([
                        'district'    => $district,
                        'subdistrict' => $subdistrict,
                        'human_Sex'   => '',
                        'age_range'   => $age_range,
                        'view'        => $view
                     ])) }}">
                    ทั้งหมด
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item rounded-3 py-2"
                     href="{{ route('dashboard', array_filter([
                        'district'    => $district,
                        'subdistrict' => $subdistrict,
                        'human_Sex'   => 'ชาย',
                        'age_range'   => $age_range,
                        'view'        => $view
                     ])) }}">
                    ชาย
                  </a>
                </li>
                <li>
                  <a class="dropdown-item rounded-3 py-2"
                     href="{{ route('dashboard', array_filter([
                        'district'    => $district,
                        'subdistrict' => $subdistrict,
                        'human_Sex'   => 'หญิง',
                        'age_range'   => $age_range,
                        'view'        => $view
                     ])) }}">
                    หญิง
                  </a>
                </li>
              </ul>
            </div>

            {{-- Age dropdown (sidebar) --}}
            <div class="dropdown mb-2">
              <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                      style="background:{{ $teal }};border-color:{{ $teal }};"
                      data-bs-toggle="dropdown"
                      data-bs-auto-close="outside">
                <span class="text-truncate">
                  <i class="bi bi-hourglass-split me-1"></i>{{ $age_range ? ($AGE_RANGES[$age_range] ?? $age_range) : 'อายุ: ทั้งหมด' }}
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>

              <ul class="dropdown-menu w-100 shadow border-0 rounded-4 dropdown-menu-scrollable p-2">
                @foreach($AGE_RANGES as $key => $label)
                  <li>
                    <a class="dropdown-item rounded-3 py-2 {{ $age_range===$key ? 'active fw-semibold' : '' }}"
                       href="{{ route('dashboard', array_filter([
                          'district'    => $district,
                          'subdistrict' => $subdistrict,
                          'human_Sex'   => $human_Sex,
                          'age_range'   => $key,
                          'view'        => $view
                       ])) }}">
                      <i class="bi bi-dot me-1"></i>{{ $label }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>

            {{-- Subdistrict dropdown --}}
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-between rounded-4"
                      data-bs-toggle="dropdown"
                      data-bs-auto-close="outside"
                      @if(empty($district)) disabled @endif>
                <span class="text-truncate">
                  <i class="bi bi-pin-map-fill me-1"></i>{{ $subdistrict ? "ต.$subdistrict" : "เลือกตำบล" }}
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>

              <ul class="dropdown-menu w-100 shadow border-0 rounded-4 dropdown-menu-scrollable p-2">
                @if(empty($district))
                  <li class="dropdown-item text-muted">กรุณาเลือกอำเภอก่อน</li>
                @else
                  <li>
                    <a class="dropdown-item text-danger rounded-3 py-2"
                       href="{{ route('dashboard', array_filter([
                          'district'    => $district,
                          'subdistrict' => '',
                          'human_Sex'   => $human_Sex,
                          'age_range'   => $age_range,
                          'view'        => $view
                       ])) }}">
                      <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองตำบล
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  @foreach($subdistrictList as $sd)
                    <li>
                      <a class="dropdown-item rounded-3 py-2"
                         href="{{ route('dashboard', array_filter([
                            'district'    => $district,
                            'subdistrict' => $sd,
                            'human_Sex'   => $human_Sex,
                            'age_range'   => $age_range,
                            'view'        => $view
                         ])) }}">
                        <i class="bi bi-dot me-1"></i>ต.{{ $sd }}
                      </a>
                    </li>
                  @endforeach
                @endif
              </ul>
            </div>

            {{-- Active badges --}}
            <div class="mt-3 d-flex flex-wrap gap-2">
              @if($district)
                <span class="badge rounded-pill text-bg-light border"><i class="bi bi-geo-alt-fill me-1 text-success"></i>อ.{{ $district }}</span>
              @endif
              @if($subdistrict)
                <span class="badge rounded-pill text-bg-light border"><i class="bi bi-pin-map-fill me-1 text-success"></i>ต.{{ $subdistrict }}</span>
              @endif
              <span class="badge rounded-pill text-bg-light border">
                <i class="bi bi-gender-ambiguous me-1 text-success"></i>{{ $human_Sex ? "เพศ: $human_Sex" : "เพศ: ทั้งหมด" }}
              </span>
              <span class="badge rounded-pill text-bg-light border">
                <i class="bi bi-hourglass-split me-1 text-success"></i>{{ $age_range ? ($AGE_RANGES[$age_range] ?? $age_range) : 'อายุ: ทั้งหมด' }}
              </span>
            </div>

          </div>
        </div>
      </div>

      {{-- Content --}}
      <div class="col-lg-9">

        {{-- Page header --}}
        <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 mb-3">
          <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="h5 fw-bold mb-1" style="color:{{ $teal2 }}">
                Dashboard สรุปข้อมูลภาพรวม
              </div>
              <div class="text-muted small">
                ปี 2564
                @if($district) · อ.{{ $district }} @endif
                @if($subdistrict) · ต.{{ $subdistrict }} @endif
                · {{ $human_Sex ? "เพศ: $human_Sex" : "เพศ: ทั้งหมด" }}
                · {{ $age_range ? ($AGE_RANGES[$age_range] ?? $age_range) : 'อายุ: ทั้งหมด' }}
              </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-outline-success rounded-pill" href="{{ url('/test') }}">
                <i class="bi bi-heart-pulse-fill me-1"></i> ข้อมูลสุขภาพ
              </a>
              <a class="btn btn-outline-success rounded-pill" href="{{ url('/welfare') }}">
                <i class="bi bi-gift-fill me-1"></i> ข้อมูลสวัสดิการ
              </a>
            </div>
          </div>
        </div>

        {{-- KPI cards --}}
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100">
              <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted small">จำนวนครัวเรือน</div>
                  <div class="h4 fw-bold mb-0" style="color:{{ $teal }}">{{ number_format($totalHouseholds) }}</div>
                  <div class="text-muted small">(ครัวเรือน)</div>
                </div>
                <div class="kpi-icon" style="background:rgba(11,127,111,.12);color:{{ $teal }};">
                  <i class="bi bi-house-door-fill"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100">
              <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted small">จำนวนสมาชิก</div>
                  <div class="h4 fw-bold mb-0 text-warning-emphasis">{{ number_format($totalMembers) }}</div>
                  <div class="text-muted small">(คน)</div>
                </div>
                <div class="kpi-icon bg-warning-subtle text-warning-emphasis">
                  <i class="bi bi-people-fill"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100">
              <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                  <div class="text-muted small">สวัสดิการทั้งหมด</div>
                  <div class="h4 fw-bold mb-0" style="color:{{ $teal }}">{{ number_format($welfareTotal) }}</div>
                  <div class="text-muted small">
                    ได้รับ {{ number_format($welfareReceived) }} · ไม่ได้รับ {{ number_format($welfareNotReceived) }}
                  </div>
                </div>
                <div class="kpi-icon bg-success-subtle text-success">
                  <i class="bi bi-gift-fill"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Charts --}}
        <div class="row g-3">

          {{-- ✅ เพศ --}}
          <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100 overflow-hidden">
              <div class="card-header bg-white bg-opacity-50 border-0 border-bottom">
                <div class="fw-semibold" style="color:{{ $teal2 }}">
                  <i class="bi bi-gender-ambiguous me-1 text-success"></i> กราฟเพศสมาชิก
                </div>
              </div>

              <div class="card-body">
                <div class="d-flex justify-content-between small text-muted mb-2">
                  <span>ชาย: <b class="text-dark">{{ number_format($sexCounts['ชาย'] ?? 0) }}</b></span>
                  <span>หญิง: <b class="text-dark">{{ number_format($sexCounts['หญิง'] ?? 0) }}</b></span>
                </div>

                <div class="border rounded-4 bg-white p-2">
                  <div style="height:230px;">
                    <canvas id="sexChart"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>

         {{-- ✅ สุขภาพ (สวยขึ้น/อ่านง่าย/ดูเป็นทางการ) --}}
<div class="col-lg-8">
  <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 overflow-hidden">

    <div class="card-header bg-white bg-opacity-50 border-0 border-bottom py-3">
      <div class="d-flex align-items-center justify-content-between gap-2">
        <div class="fw-semibold text-truncate" style="color:{{ $teal2 }}">
          <i class="bi bi-bar-chart-fill me-2 text-success"></i> กราฟสุขภาพสมาชิก
        </div>

        {{-- optional: badge รวม --}}
        <span class="badge rounded-pill text-bg-light border">
          รวม <b>{{ number_format($totalMembers ?? 0) }}</b> คน
        </span>
      </div>
    </div>

    <div class="card-body p-3 p-lg-4">
      <div class="border rounded-4 bg-white p-2">
        <div style="height:460px;">
          <canvas id="healthChart"></canvas>
        </div>
      </div>

      <div class="small text-muted mt-2">
        <i class="bi bi-info-circle me-1"></i>
        หมายเหตุ: “ไม่ระบุ” คือจำนวนที่ไม่ได้อยู่ใน 4 สถานะหลัก
      </div>
    </div>

  </div>
</div>

        </div>

      </div>{{-- /col-lg-9 --}}
    </div>
  </div>

  <script>
  // =========================
  // ✅ HEALTH CHART
  // =========================
  const labels = @json($labels ?? []);
  const datasetsRaw = @json($datasets ?? []);
  const totalMembers = Number(@json($totalMembers ?? 0));

  const labelShortMap = {
    'ปกติ': 'ปกติ',
    'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)': 'เรื้อรัง',
    'พิการพึ่งตนเองได้': 'พิการ',
    'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้': 'ติดเตียง',
    'ไม่ระบุ': 'ไม่ระบุ',
  };

  const palette = {
    'ปกติ':   '#0B7F6F',
    'เรื้อรัง': 'rgba(220,53,69,.82)',
    'พิการ':   'rgba(13,110,253,.82)',
    'ติดเตียง': 'rgba(255,193,7,.88)',
    'ไม่ระบุ': 'rgba(108,117,125,.75)',
  };

  const healthDatasets = datasetsRaw.map(ds => {
    const short = labelShortMap[ds.label] || ds.label;
    return {
      label: short,
      data: (ds.data || []).map(v => Number(v || 0)),
      backgroundColor: palette[short] ?? 'rgba(108,117,125,.75)',
      borderRadius: 10,
      barPercentage: .85,
      categoryPercentage: .72,
      maxBarThickness: 34,
    };
  });

  const sumKnown = healthDatasets.reduce((acc, ds) => {
    return acc + (ds.data || []).reduce((s, v) => s + Number(v || 0), 0);
  }, 0);

  const notSpecified = Math.max(0, totalMembers - sumKnown);

  if (notSpecified > 0) {
    labels.push('ไม่ระบุ');
    healthDatasets.forEach(ds => ds.data.push(0));

    const notArr = new Array(labels.length).fill(0);
    notArr[labels.length - 1] = notSpecified;

    healthDatasets.push({
      label: 'ไม่ระบุ',
      data: notArr,
      backgroundColor: palette['ไม่ระบุ'],
      borderRadius: 10,
      barPercentage: .85,
      categoryPercentage: .72,
      maxBarThickness: 34,
    });
  }

  const valueLabelPlugin = {
    id: 'valueLabel',
    afterDatasetsDraw(chart){
      const {ctx} = chart;
      ctx.save();
      ctx.font = '600 12px system-ui';
      ctx.fillStyle = '#111827';
      ctx.textAlign = 'center';

      chart.data.datasets.forEach((ds, di)=>{
        const meta = chart.getDatasetMeta(di);
        if(meta.hidden) return;

        meta.data.forEach((bar, i)=>{
          const v = Number(ds.data[i] || 0);
          if(!v) return;
          if (v < 150) return; // กันรก
          ctx.fillText(v.toLocaleString(), bar.x, bar.y - 6);
        });
      });

      ctx.restore();
    }
  };

  const healthCanvas = document.getElementById('healthChart');
  if (healthCanvas) {
    new Chart(healthCanvas, {
      type: 'bar',
      data: { labels, datasets: healthDatasets },
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
          x: { grid: { display:false }, ticks: { font: { size:12 }, maxRotation:0 } },
          y: {
            beginAtZero:true,
            grid:{ color:'rgba(229,231,235,.9)' },
            ticks:{ callback:v=>Number(v).toLocaleString(), font:{ size:12 } }
          }
        },
        plugins: {
          legend: {
            position:'top',
            labels:{ usePointStyle:true, pointStyle:'circle', boxWidth:10, font:{ size:12, weight:'600' }, padding:14 }
          },
          tooltip: {
            padding: 10,
            callbacks: { label:(ctx)=>` ${ctx.dataset.label}: ${Number(ctx.raw||0).toLocaleString()} คน` }
          }
        }
      }
    });
  }

  // =========================
  // ✅ SEX CHART (อย่าหาย)
  // =========================
  const sexCounts = @json($sexCounts ?? ['ชาย'=>0,'หญิง'=>0]);

  const sexCanvas = document.getElementById('sexChart');
  if (sexCanvas) {
    new Chart(sexCanvas, {
      type: 'doughnut',
      data: {
        labels: ['ชาย','หญิง'],
        datasets: [{
          data: [Number(sexCounts['ชาย']||0), Number(sexCounts['หญิง']||0)],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle:true, boxWidth:10 } },
          tooltip: { callbacks: { label:(ctx)=>` ${ctx.label}: ${Number(ctx.raw||0).toLocaleString()} คน` } }
        }
      }
    });
  }
</script>


</body>
</html>
