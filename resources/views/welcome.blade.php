{{-- resources/views/welcome.blade.php --}}
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
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">

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

    .chip{
      border-radius:999px;
      padding:.35rem .7rem;
      font-size:.85rem;
      display:inline-flex;
      align-items:center;
      gap:.4rem;
      border:1px solid rgba(0,0,0,.08);
      background:#fff;
    }

    .dropdown-menu-scrollable{ max-height: 260px; overflow:auto; }
  </style>
</head>

<body class="app-bg">
@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  // ✅ ปี (กรอง)
  $year = $year ?? request('year', 'all');
  $YEAR_OPTIONS = $YEAR_OPTIONS ?? ['all','2564','2565','2566','2567','2568'];
  if (!in_array($year, $YEAR_OPTIONS, true)) $year = 'all';
  $yearLabel = ($year === 'all') ? '2564–2568' : $year;

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

  // ✅ ต้องส่งมาจาก controller เป็น list ของตำบลตามอำเภอ
  $subdistrictList = $subdistrictList ?? collect([]);

  $totalHouseholds = $totalHouseholds ?? 0;
  $totalMembers    = $totalMembers ?? 0;

  $welfareTotal       = $welfareTotal ?? 0;
  $welfareReceived    = $welfareReceived ?? 0;
  $welfareNotReceived = $welfareNotReceived ?? 0;

  $labels   = $labels ?? [];
  $datasets = $datasets ?? [];

  // ✅ ตัวเลขเพศ
  $sexCounts = $sexCounts ?? ['ชาย'=>0,'หญิง'=>0];

  // ✅ baseParams ใช้ให้ทุกลิงก์ติด query เหมือนกัน
  $baseParams = [
    'year'        => $year,
    'district'    => $district,
    'subdistrict' => $subdistrict,
    'human_Sex'   => $human_Sex,
    'age_range'   => $age_range,
    'view'        => $view,
  ];

  $makeUrl = function(string $path) use ($baseParams) {
    $u = url($path);
    $q = http_build_query(array_filter($baseParams, fn($v)=>$v!=='' && $v!==null));
    return $q ? ($u.'?'.$q) : $u;
  };

  $hhHref      = $makeUrl('/household_64');
  $testHref    = $makeUrl('/test');
  $welfareHref = $makeUrl('/welfare');

  // =========================
  // ✅ Capitals (ทุน 5 ด้าน)
  // =========================
  $capYear = (int)($capYear ?? request('cap_year', 2568));

  $capSummary = $capSummary ?? ['human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];
  $capStd     = $capStd     ?? ['human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];

  $capRadar    = $capRadar    ?? [
    (float)($capSummary['human']??0),
    (float)($capSummary['physical']??0),
    (float)($capSummary['financial']??0),
    (float)($capSummary['natural']??0),
    (float)($capSummary['social']??0)
  ];
  $capRadarStd = $capRadarStd ?? [
    (float)($capStd['human']??0),
    (float)($capStd['physical']??0),
    (float)($capStd['financial']??0),
    (float)($capStd['natural']??0),
    (float)($capStd['social']??0)
  ];

  // ✅ SAFE vars
  $sexCountsSafe   = $sexCounts   ?? ['ชาย'=>0,'หญิง'=>0];
  $capRadarSafe    = $capRadar    ?? [0,0,0,0,0];
  $capRadarStdSafe = $capRadarStd ?? [0,0,0,0,0];

  // =========================
  // ✅ Total Capitals by Year (ทุนรวม 5 ด้านรายปี)
  // =========================
  $CAP_YEARS = [2564,2565,2566,2567,2568];

  // ✅ controller "ควรส่งมา" แบบนี้: [ปี => ['human'=>..,'physical'=>..,'financial'=>..,'natural'=>..,'social'=>..]]
  $capByYear = $capByYear ?? [];

  $capTotalByYear = [];
  foreach ($CAP_YEARS as $y) {
    $row = $capByYear[$y] ?? ['human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];

    $capTotalByYear[$y] =
      (float)($row['human']??0) +
      (float)($row['physical']??0) +
      (float)($row['financial']??0) +
      (float)($row['natural']??0) +
      (float)($row['social']??0);
  }

  $capYearsLabels  = array_map(fn($y)=> (string)$y, $CAP_YEARS);
  $capTotalsSeries = array_values($capTotalByYear);
@endphp

@include('layouts.topbar')

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
            <div class="text-muted small">ปีข้อมูล: {{ $yearLabel }}</div>
          </div>
        </div>

        <div class="nav nav-pills flex-column gap-1 mb-3">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-dark' }}"
             href="{{ route('dashboard', array_filter($baseParams)) }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
          </a>

          <a class="nav-link {{ request()->is('test') ? 'active' : 'text-dark' }}"
             href="{{ $testHref }}">
            <i class="bi bi-heart-pulse-fill me-2"></i>ข้อมูลสุขภาพ
          </a>

          <a class="nav-link {{ request()->is('welfare') ? 'active' : 'text-dark' }}"
             href="{{ $welfareHref }}">
            <i class="bi bi-gift-fill me-2"></i>ข้อมูลสวัสดิการ
          </a>

          <a class="nav-link {{ request()->is('household_64') ? 'active' : 'text-dark' }}"
             href="{{ $hhHref }}">
            <i class="bi bi-table me-2"></i>ตารางครัวเรือน
          </a>

          <a class="nav-link d-flex align-items-center text-nowrap {{ request()->routeIs('housing.dashboard') ? 'active' : 'text-dark' }}"
             href="{{ route('housing.dashboard') }}">
            <i class="bi bi-house-door-fill me-2"></i>
            สภาพที่อยู่อาศัยสาธารณูปโภค
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

          {{-- District --}}
          <div class="dropdown mb-2">
            <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                    style="background:{{ $teal }};border-color:{{ $teal }};"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
              <span class="text-truncate">
                <i class="bi bi-geo-alt-fill me-1"></i>{{ $district ? "อ.$district" : "เลือกอำเภอ" }}
              </span>
              <i class="bi bi-chevron-down"></i>
            </button>

            <ul class="dropdown-menu w-100 shadow border-0 rounded-4 dropdown-menu-scrollable p-2">
              @foreach($districtList as $d)
                <li>
                  <a class="dropdown-item rounded-3 py-2"
                     href="{{ route('dashboard', array_filter(array_merge($baseParams, [
                        'district'    => $d,
                        'subdistrict' => '',
                     ]))) }}">
                    <i class="bi bi-dot me-1"></i>อ.{{ $d }}
                  </a>
                </li>
              @endforeach
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger rounded-3 py-2"
                   href="{{ route('dashboard', array_filter(array_merge($baseParams, [
                      'district'    => '',
                      'subdistrict' => '',
                   ]))) }}">
                  <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองอำเภอ
                </a>
              </li>
            </ul>
          </div>

          {{-- Subdistrict --}}
          <div class="dropdown mb-2">
            <button class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-between rounded-4"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside"
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
                     href="{{ route('dashboard', array_filter(array_merge($baseParams, ['subdistrict'=>'']))) }}">
                    <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองตำบล
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach($subdistrictList as $sd)
                  <li>
                    <a class="dropdown-item rounded-3 py-2 {{ $subdistrict===$sd ? 'active fw-semibold' : '' }}"
                       href="{{ route('dashboard', array_filter(array_merge($baseParams, ['subdistrict'=>$sd]))) }}">
                      <i class="bi bi-dot me-1"></i>ต.{{ $sd }}
                    </a>
                  </li>
                @endforeach
              @endif
            </ul>
          </div>

          {{-- Sex --}}
          <div class="dropdown mb-2">
            <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                    style="background:{{ $teal }};border-color:{{ $teal }};"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
              <span class="text-truncate">
                <i class="bi bi-gender-ambiguous me-1"></i>{{ $human_Sex ? "เพศ: $human_Sex" : "เพศ: ทั้งหมด" }}
              </span>
              <i class="bi bi-chevron-down"></i>
            </button>

            <ul class="dropdown-menu w-100 shadow border-0 rounded-4 p-2">
              <li>
                <a class="dropdown-item rounded-3 py-2"
                   href="{{ route('dashboard', array_filter(array_merge($baseParams, ['human_Sex'=>'']))) }}">ทั้งหมด</a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item rounded-3 py-2"
                   href="{{ route('dashboard', array_filter(array_merge($baseParams, ['human_Sex'=>'ชาย']))) }}">ชาย</a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2"
                   href="{{ route('dashboard', array_filter(array_merge($baseParams, ['human_Sex'=>'หญิง']))) }}">หญิง</a>
              </li>
            </ul>
          </div>

          {{-- Age --}}
          <div class="dropdown mb-2">
            <button class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-between rounded-4"
                    style="background:{{ $teal }};border-color:{{ $teal }};"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside">
              <span class="text-truncate">
                <i class="bi bi-hourglass-split me-1"></i>{{ $age_range ? ($AGE_RANGES[$age_range] ?? $age_range) : 'อายุ: ทั้งหมด' }}
              </span>
              <i class="bi bi-chevron-down"></i>
            </button>

            <ul class="dropdown-menu w-100 shadow border-0 rounded-4 dropdown-menu-scrollable p-2">
              @foreach($AGE_RANGES as $key => $label)
                <li>
                  <a class="dropdown-item rounded-3 py-2 {{ $age_range===$key ? 'active fw-semibold' : '' }}"
                     href="{{ route('dashboard', array_filter(array_merge($baseParams, ['age_range'=>$key]))) }}">
                    <i class="bi bi-dot me-1"></i>{{ $label }}
                  </a>
                </li>
              @endforeach
            </ul>
          </div>

          {{-- Active badges --}}
          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border">
              <i class="bi bi-calendar2-week me-1 text-success"></i>ปี {{ $yearLabel }}
            </span>
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

        </div>{{-- /filters --}}
      </div>
    </div>

    {{-- Content --}}
    <div class="col-lg-9">

      {{-- Header --}}
      <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 mb-3">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div class="h5 fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap" style="color:{{ $teal2 }}">
              <span>Dashboard สรุปข้อมูลภาพรวม</span>

              {{-- Year dropdown --}}
              <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        style="background:rgba(11,127,111,.08);color:{{ $teal }};border:none;border-radius:8px;padding:4px 10px;font-weight:600;">
                  <i class="bi bi-calendar2-week" style="font-size:13px;"></i>
                  <span>ปี {{ $yearLabel }}</span>
                  <i class="bi bi-chevron-down" style="font-size:11px;"></i>
                </button>

                <ul class="dropdown-menu p-2"
                    style="border-radius:10px;border:1px solid #e5e7eb;box-shadow:0 12px 28px rgba(0,0,0,.08);min-width:170px;">
                  @foreach($YEAR_OPTIONS as $y)
                    @php $yLabel = ($y==='all') ? '2564–2568' : $y; @endphp
                    <li>
                      <a class="dropdown-item d-flex justify-content-between align-items-center"
                         style="border-radius:8px;padding:7px 10px;font-weight:{{ $year===$y ? '600' : '500' }};
                                color:{{ $year===$y ? $teal : '#374151' }};
                                background:{{ $year===$y ? 'rgba(11,127,111,.08)' : 'transparent' }};"
                         href="{{ route('dashboard', array_filter(array_merge($baseParams, ['year'=>$y,'subdistrict'=>'']))) }}">
                        {{ $yLabel }}
                        @if($year===$y) <i class="bi bi-check text-success"></i> @endif
                      </a>
                    </li>
                  @endforeach
                </ul>
              </div>
            </div>

            <div class="text-muted small">
              ปี {{ $yearLabel }}
              @if($district) · อ.{{ $district }} @endif
              @if($subdistrict) · ต.{{ $subdistrict }} @endif
              · {{ $human_Sex ? "เพศ: $human_Sex" : "เพศ: ทั้งหมด" }}
              · {{ $age_range ? ($AGE_RANGES[$age_range] ?? $age_range) : 'อายุ: ทั้งหมด' }}
            </div>
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

      {{-- CHARTS --}}
      <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 overflow-hidden mb-3">
        <div class="card-header bg-white bg-opacity-50 border-0 border-bottom py-3">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="fw-semibold" style="color:{{ $teal2 }}">
              <i class="bi bi-graph-up-arrow me-2 text-success"></i>
              สถิติสุขภาพและโครงสร้างประชากร
            </div>
            <span class="badge rounded-pill text-bg-light border">
              รวม <b>{{ number_format($totalMembers ?? 0) }}</b> คน
            </span>
          </div>
        </div>

        <div class="card-body p-3 p-lg-4">
          <div class="row g-4 align-items-stretch">

            {{-- LEFT : HEALTH BAR --}}
            <div class="col-12 col-lg-8">
              <div class="border rounded-4 bg-white p-3 h-100" style="border-color:rgba(0,0,0,.06)!important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="small fw-semibold" style="color:{{ $teal2 }}">
                    <i class="bi bi-bar-chart-fill me-1 text-success"></i>กราฟสุขภาพสมาชิก
                  </div>
                  <div class="small text-muted">
                    {{ $yearLabel }}
                    @if($district) · อ.{{ $district }} @endif
                    @if($subdistrict) · ต.{{ $subdistrict }} @endif
                  </div>
                </div>

                <div style="height:380px;">
                  <canvas id="healthChart"></canvas>
                </div>

                <div class="small text-muted mt-2">
                  <i class="bi bi-info-circle me-1"></i>
                  “ไม่ระบุ” คือจำนวนที่ไม่ได้อยู่ใน 4 สถานะหลัก
                </div>
              </div>
            </div>

            {{-- RIGHT : SEX DONUT --}}
            <div class="col-12 col-lg-4">
              <div class="border rounded-4 bg-white p-3 h-100 d-flex flex-column" style="border-color:rgba(0,0,0,.06)!important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="small fw-semibold" style="color:{{ $teal2 }}">
                    <i class="bi bi-gender-ambiguous me-1 text-success"></i>สัดส่วนเพศ
                  </div>
                  <span class="badge rounded-pill text-bg-light border">
                    {{ number_format(($sexCounts['ชาย'] ?? 0)+($sexCounts['หญิง'] ?? 0)) }} คน
                  </span>
                </div>

                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                  <div style="height:250px;width:250px;">
                    <canvas id="sexChart"></canvas>
                  </div>
                </div>

                <div class="d-flex justify-content-between small text-muted mt-2">
                  <span>ชาย: <b>{{ number_format($sexCounts['ชาย'] ?? 0) }}</b></span>
                  <span>หญิง: <b>{{ number_format($sexCounts['หญิง'] ?? 0) }}</b></span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- =========================
          ✅ SECTION: ทุนทั้ง 5 ด้าน
         ========================= --}}
      <div class="mt-3">

        <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 mb-3">
          <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="h5 fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap" style="color:{{ $teal2 }}">
                <span><i class="bi bi-diagram-3-fill me-2 text-success"></i>ทุนทั้ง 5 ด้าน</span>
                <span class="chip">
                  <i class="bi bi-calendar2-week" style="color:{{ $teal }}"></i> ปี {{ $capYear }}
                </span>
              </div>
              <div class="text-muted small">
                แสดงค่าเฉลี่ย (Mean) และส่วนเบี่ยงเบนมาตรฐาน (SD)
              </div>
            </div>
          </div>
        </div>

        {{-- KPI: 5 กล่องอยู่บรรทัดเดียวกัน (desktop) --}}
        <div class="d-flex flex-nowrap justify-content-between gap-3 mb-3">
          @foreach([
            'human'     => 'ทุนมนุษย์',
            'physical'  => 'ทุนกายภาพ',
            'financial' => 'ทุนการเงิน',
            'natural'   => 'ทุนธรรมชาติ',
            'social'    => 'ทุนสังคม',
          ] as $key=>$label)

            <div class="flex-fill">
              <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 text-center h-100">
                <div class="card-body py-3">
                  <div class="small text-muted mb-1">{{ $label }}</div>
                  <div class="fw-bold fs-4" style="color:{{ $teal2 }};">
                    {{ number_format($capSummary[$key] ?? 0, 2) }}
                  </div>
                  <div class="text-muted" style="font-size:13px;">
                    SD: <b>{{ number_format($capStd[$key] ?? 0, 2) }}</b>
                  </div>
                </div>
              </div>
            </div>

          @endforeach
        </div>

        {{-- ✅ ทุนรวม (5 ด้านรวมกัน) รายปี 2564–2568 --}}
        <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 mb-3">
          <div class="card-body">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <div class="fw-semibold" style="color:{{ $teal2 }}">
                <i class="bi bi-graph-up me-1 text-success"></i> ทุนรวม (5 ด้านรวมกัน) รายปี
              </div>

              <span class="badge rounded-pill text-bg-light border">
                ปีที่เลือก {{ $capYear }}:
                <b>{{ number_format($capTotalByYear[$capYear] ?? 0, 2) }}</b>
              </span>
            </div>

            <div class="text-muted small mb-3">
              ทุนรวม = มนุษย์ + กายภาพ + การเงิน + ธรรมชาติ + สังคม (ค่าเฉลี่ยของปีนั้น)
            </div>

            <div class="row g-3 align-items-stretch">

              {{-- กราฟแนวโน้ม --}}
              <div class="col-12 col-lg-7">
                <div class="border rounded-4 bg-white p-3 h-100" style="border-color:rgba(0,0,0,.06)!important;">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small fw-semibold" style="color:{{ $teal2 }}">
                      <i class="bi bi-activity me-1 text-success"></i> แนวโน้มทุนรวม
                    </div>
                    <div class="small text-muted">2564–2568</div>
                  </div>

                  <div style="height:260px;">
                    <canvas id="capTotalTrend"></canvas>
                  </div>
                </div>
              </div>

              {{-- ตารางทุนรวมรายปี --}}
              <div class="col-12 col-lg-5">
                <div class="border rounded-4 bg-white p-3 h-100" style="border-color:rgba(0,0,0,.06)!important;">
                  <div class="small fw-semibold mb-2" style="color:{{ $teal2 }}">
                    <i class="bi bi-table me-1 text-success"></i> สรุปทุนรวมรายปี
                  </div>

                  <div class="table-responsive" style="border-radius:14px; overflow:hidden; border:1px solid rgba(0,0,0,.06);">
                    <table class="table mb-0 align-middle" style="font-size:14px;">
                      <thead style="background:rgba(11,127,111,.06);">
                        <tr class="text-muted" style="font-size:12.5px;">
                          <th style="padding:10px 12px;">ปี</th>
                          <th class="text-end" style="padding:10px 12px;">ทุนรวม</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($CAP_YEARS as $y)
                          @php $v = (float)($capTotalByYear[$y] ?? 0); @endphp
                          <tr style="border-top:1px solid rgba(0,0,0,.05); {{ (int)$y===(int)$capYear ? 'background:rgba(11,127,111,.05);' : '' }}">
                            <td style="padding:10px 12px;" class="fw-semibold">
                              {{ $y }}
                              @if((int)$y === (int)$capYear)
                                <span class="badge rounded-pill ms-2" style="background:rgba(11,127,111,.12); color:{{ $teal2 }};">ปีที่เลือก</span>
                              @endif
                            </td>
                            <td class="text-end fw-bold" style="padding:10px 12px; color:{{ $teal2 }};">
                              {{ number_format($v, 2) }}
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- Radar + Table --}}
        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100">
              <div class="card-body">
                <div class="fw-semibold mb-2" style="color:{{ $teal2 }}">
                  <i class="bi bi-pentagon-half me-1 text-success"></i> เรดาร์ทุน 5 ด้าน 
                </div>
                <div class="border rounded-4 bg-white p-2" style="border-color:rgba(0,0,0,.06)!important;">
                  <div style="height:320px;">
                    <canvas id="capitalsRadar"></canvas>
                  </div>
                </div>
                <div class="small text-muted mt-2">
                  <i class="bi bi-info-circle me-1"></i> เส้นประกอบ: Mean, Mean+SD, Mean-SD
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
  <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 h-100">
    <div class="card-body">

      {{-- Header --}}
      <div class="fw-semibold mb-2" style="color:{{ $teal2 }}">
        <i class="bi bi-table me-1 text-success"></i> ตารางสรุป (Average / SD)
      </div>

      <div class="text-muted small mb-3">
        ค่าเฉลี่ยและส่วนเบี่ยงเบนมาตรฐานของทุนแต่ละมิติ
      </div>
    <span class="badge rounded-pill text-bg-light border">
                ปีที่เลือก {{ $capYear }}
             
              </span>
      <div class="table-responsive"
           style="border-radius:16px; overflow:hidden; border:1px solid rgba(0,0,0,.06);">

        <table class="table align-middle mb-0 text-center"
               style="font-size:14px; table-layout:fixed;">

          {{-- header --}}
          <thead style="background:rgba(11,127,111,.06);">
            <tr class="text-muted" style="font-size:12.5px;">
              <th style="width:50%; padding:12px;">ทุน 5 ด้าน</th>
              <th style="width:25%; padding:12px;">Mean</th>
              <th style="width:25%; padding:12px;">SD</th>
            </tr>
          </thead>

          <tbody>

          @php
            $rowsCap = [
              ['k'=>'human','name'=>'มนุษย์','icon'=>'bi-person-heart','bg'=>'rgba(13,110,253,.08)','ic'=>'#0d6efd'],
              ['k'=>'physical','name'=>'กายภาพ','icon'=>'bi-house-heart','bg'=>'rgba(25,135,84,.10)','ic'=>'#198754'],
              ['k'=>'financial','name'=>'เศรษฐกิจ','icon'=>'bi-cash-coin','bg'=>'rgba(255,193,7,.18)','ic'=>'#b45309'],
              ['k'=>'natural','name'=>'ธรรมชาติ','icon'=>'bi-tree-fill','bg'=>'rgba(32,201,151,.14)','ic'=>'#0f766e'],
              ['k'=>'social','name'=>'ทางสังคม','icon'=>'bi-people-fill','bg'=>'rgba(111,66,193,.10)','ic'=>'#6f42c1'],
            ];
          @endphp

          @foreach($rowsCap as $r)

            @php
              $mean = (float)($capSummary[$r['k']] ?? 0);
              $sd   = (float)($capStd[$r['k']] ?? 0);
            @endphp

            <tr style="height:60px; transition:.15s;"
                onmouseover="this.style.background='rgba(11,127,111,.05)'"
                onmouseout="this.style.background='transparent'">

              {{-- name --}}
              <td class="text-start px-3">
                <div class="d-flex align-items-center gap-2">
                  <span style="width:32px;height:32px;border-radius:10px;
                               display:flex;align-items:center;justify-content:center;
                               background:{{ $r['bg'] }};">
                    <i class="bi {{ $r['icon'] }}" style="color:{{ $r['ic'] }}"></i>
                  </span>
                  <span class="fw-semibold">{{ $r['name'] }}</span>
                </div>
              </td>

              {{-- mean --}}
              <td>
                <span class="badge rounded-pill font-monospace"
                      style="min-width:80px;
                             background:rgba(11,127,111,.12);
                             color:{{ $teal2 }};
                             font-weight:700;">
                  {{ number_format($mean, 2) }}
                </span>
              </td>

              {{-- sd --}}
              <td>
                <span class="badge rounded-pill font-monospace"
                      style="min-width:80px;
                             background:rgba(0,0,0,.06);
                             color:#374151;
                             font-weight:700;">
                  {{ number_format($sd, 2) }}
                </span>
              </td>

            </tr>
          @endforeach

          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

        </div>

      </div>{{-- /capitals section --}}
    </div>{{-- /col-lg-9 --}}
  </div>{{-- /row --}}
</div>{{-- /container --}}

<script>
  // =========================
  // ✅ Data from server (SAFE)
  // =========================
  const labelsFromServer   = @json($labels ?? []);
  const datasetsFromServer = @json($datasets ?? []);
  const sexCounts          = @json($sexCountsSafe);

  const labels = Array.isArray(labelsFromServer) ? [...labelsFromServer] : [];
  const datasetsRaw = Array.isArray(datasetsFromServer)
    ? JSON.parse(JSON.stringify(datasetsFromServer))
    : [];

  let _healthChart = null;
  let _sexChart = null;

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
      borderRadius: 14,
      borderSkipped: false,
      barPercentage: .72,
      categoryPercentage: .58,
      maxBarThickness: 34,
    };
  });

  const valueLabelPlugin = {
    id: 'valueLabel',
    afterDatasetsDraw(chart){
      const {ctx} = chart;
      ctx.save();
      ctx.font = '600 12px Prompt, system-ui';
      ctx.fillStyle = '#111827';
      ctx.textAlign = 'center';

      chart.data.datasets.forEach((ds, di)=>{
        const meta = chart.getDatasetMeta(di);
        if(meta.hidden) return;

        meta.data.forEach((bar, i)=>{
          const v = Number(ds.data[i] || 0);
          if(!v) return;
          if (v < 150) return;
          ctx.fillText(v.toLocaleString(), bar.x, bar.y - 6);
        });
      });

      ctx.restore();
    }
  };

  // =========================
  // ✅ Health Chart
  // =========================
  const healthCanvas = document.getElementById('healthChart');
  if (healthCanvas) {
    if (_healthChart) { _healthChart.destroy(); _healthChart = null; }

    _healthChart = new Chart(healthCanvas, {
      type: 'bar',
      data: { labels, datasets: healthDatasets },
      plugins: [valueLabelPlugin],
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        interaction: { mode: 'index', intersect: false },
        layout: { padding: { top: 6, right: 8, left: 4, bottom: 0 } },
        scales: {
          x: { grid: { display:false }, ticks: { font: { size:12, weight:'600' }, maxRotation:0 } },
          y: {
            beginAtZero:true,
            grid:{ color:'rgba(229,231,235,.9)' },
            ticks:{ callback:v=>Number(v).toLocaleString(), font:{ size:12 } }
          }
        },
        plugins: {
          legend: {
            position:'bottom',
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
  // ✅ Sex Chart
  // =========================
  const sexCanvas = document.getElementById('sexChart');
  if (sexCanvas) {
    if (_sexChart) { _sexChart.destroy(); _sexChart = null; }

    const male = Number(sexCounts['ชาย']||0);
    const female = Number(sexCounts['หญิง']||0);

    _sexChart = new Chart(sexCanvas, {
      type: 'doughnut',
      data: {
        labels: ['ชาย','หญิง'],
        datasets: [{
          data: [male, female],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        animation: false,
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle:true, boxWidth:10 } },
          tooltip: { callbacks: { label:(ctx)=>` ${ctx.label}: ${Number(ctx.raw||0).toLocaleString()} คน` } }
        }
      }
    });
  }

  // =========================
  // ✅ Capitals Radar (SAFE)
  // =========================
  const capRadarData = @json($capRadarSafe);
  const capRadarStd  = @json($capRadarStdSafe);

  const capEl = document.getElementById('capitalsRadar');
  if (capEl) {
    const mean = (capRadarData || []).map(v => Number(v || 0));
    const sd   = (capRadarStd  || []).map(v => Number(v || 0));

    const meanPlus  = mean.map((v,i) => v + (sd[i] || 0));
    const meanMinus = mean.map((v,i) => Math.max(0, v - (sd[i] || 0)));

    new Chart(capEl, {
      type: 'radar',
      data: {
        labels: ['มนุษย์','กายภาพ','การเงิน','ธรรมชาติ','สังคม'],
        datasets: [
          { label:'Mean+SD', data: meanPlus,  borderWidth:2, fill:false, pointRadius:2, borderDash:[6,4] },
          { label:'Mean-SD', data: meanMinus, borderWidth:2, fill:false, pointRadius:2, borderDash:[6,4] },
          { label:'Mean',    data: mean,      borderWidth:3, fill:true,  pointRadius:3 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position:'bottom' } },
        scales: { r: { beginAtZero:true } }
      }
    });
  }

  // =========================
  // ✅ Total Capitals Trend (ทุนรวมรายปี)
  // =========================
  const capYearsLabels  = @json($capYearsLabels ?? []);
  const capTotalsSeries = @json($capTotalsSeries ?? []);

  const capTrendEl = document.getElementById('capTotalTrend');
  if (capTrendEl) {
    new Chart(capTrendEl, {
      type: 'line',
      data: {
        labels: capYearsLabels,
        datasets: [{
          label: 'ทุนรวม',
          data: (capTotalsSeries || []).map(v => Number(v || 0)),
          tension: 0.35,
          borderWidth: 3,
          pointRadius: 4,
          pointHoverRadius: 5,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${Number(ctx.raw||0).toFixed(2)}` } }
        },
        scales: {
          x: { grid: { display:false } },
          y: { beginAtZero: true, grid: { color: 'rgba(229,231,235,.8)' } }
        }
      }
    });
  }
</script>

</body>
</html>
