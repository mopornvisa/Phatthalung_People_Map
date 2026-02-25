<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ทุน 5 ด้าน</title>

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

    /* เพิ่มนิดให้การ์ดทุนดูละมุน */
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
  </style>
</head>

<body class="app-bg">
@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  // ====== (1) ตัวแปรฝั่ง Dashboard layout (คงไว้ให้เข้าระบบเดิม) ======
  $yearDash = $yearDash ?? request('year', 'all');
  $YEAR_OPTIONS = $YEAR_OPTIONS ?? ['all','2564','2565','2566','2567','2568'];
  if (!in_array($yearDash, $YEAR_OPTIONS, true)) $yearDash = 'all';
  $yearLabel = ($yearDash === 'all') ? '2564–2568' : $yearDash;

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

  $baseParams = [
    'year'        => $yearDash,
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

  // ====== (2) ตัวแปรฝั่ง “ทุน 5 ด้าน” ======
  $mode = $mode ?? request('mode','year');
  if (!in_array($mode, ['year','trend'], true)) $mode = 'year';

  $capYear = (int)($capYear ?? request('capYear', request('year_cap', request('year2', request('year_capital', request('year', 2568))))));
  // แต่ controller ของคุณใช้ key ชื่อ year อยู่แล้ว:
  $capYear = (int)($capYear ?: (int)request('year', 2568));
  $YEARS = $YEARS ?? [2564,2565,2566,2567,2568];
  if (!in_array($capYear, $YEARS, true)) $capYear = 2568;

  $radarSafe    = $radar ?? [0,0,0,0,0];
  $radarStdSafe = $radarStd ?? [0,0,0,0,0];
  $stdSafe      = $std ?? ['total'=>0,'human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];
  $summary      = $summary ?? ['human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];
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

          {{-- ✅ เพิ่มเมนู “ทุน 5 ด้าน” ให้ active เมื่ออยู่หน้า capitals --}}
          <a class="nav-link {{ request()->routeIs('capitals.index') ? 'active' : 'text-dark' }}"
             href="{{ route('capitals.index', array_filter(array_merge($baseParams, ['mode'=>'year','year'=>$capYear]))) }}">
            <i class="bi bi-diagram-3-fill me-2"></i>ทุนทั้ง 5 ด้าน
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

        {{-- Filters (คงไว้เหมือนเดิม) --}}
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

            <ul class="dropdown-menu w-100 shadow border-0 rounded-4 p-2">
              @foreach($districtList as $d)
                <li>
                  <a class="dropdown-item rounded-3 py-2"
                     href="{{ route('capitals.index', array_filter(array_merge($baseParams, [
                       'mode'        => $mode,
                       'year'        => $capYear,
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
                   href="{{ route('capitals.index', array_filter(array_merge($baseParams, [
                     'mode'        => $mode,
                     'year'        => $capYear,
                     'district'    => '',
                     'subdistrict' => '',
                   ]))) }}">
                  <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองอำเภอ
                </a>
              </li>
            </ul>
          </div>

          {{-- Subdistrict --}}
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-between rounded-4"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    @if(empty($district)) disabled @endif>
              <span class="text-truncate">
                <i class="bi bi-pin-map-fill me-1"></i>{{ $subdistrict ? "ต.$subdistrict" : "เลือกตำบล" }}
              </span>
              <i class="bi bi-chevron-down"></i>
            </button>

            <ul class="dropdown-menu w-100 shadow border-0 rounded-4 p-2">
              @if(empty($district))
                <li class="dropdown-item text-muted">กรุณาเลือกอำเภอก่อน</li>
              @else
                <li>
                  <a class="dropdown-item text-danger rounded-3 py-2"
                     href="{{ route('capitals.index', array_filter(array_merge($baseParams, [
                       'mode'=>$mode,'year'=>$capYear,'subdistrict'=>''
                     ]))) }}">
                    <i class="bi bi-x-circle me-1"></i>ล้างตัวกรองตำบล
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach($subdistrictList as $sd)
                  <li>
                    <a class="dropdown-item rounded-3 py-2"
                       href="{{ route('capitals.index', array_filter(array_merge($baseParams, [
                         'mode'=>$mode,'year'=>$capYear,'subdistrict'=>$sd
                       ]))) }}">
                      <i class="bi bi-dot me-1"></i>ต.{{ $sd }}
                    </a>
                  </li>
                @endforeach
              @endif
            </ul>
          </div>

        </div>
      </div>
    </div>

    {{-- Content --}}
    <div class="col-lg-9">

      {{-- Header card --}}
      <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 mb-3">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div class="h5 fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap" style="color:{{ $teal2 }}">
              <span><i class="bi bi-diagram-3-fill me-1 text-success"></i>ทุนทั้ง 5 ด้าน</span>
              <span class="chip">
                <i class="bi bi-calendar2-week" style="color:{{ $teal }}"></i>
                ปี {{ $capYear }}
              </span>
              @if($district) <span class="chip">อ.{{ $district }}</span> @endif
              @if($subdistrict) <span class="chip">ต.{{ $subdistrict }}</span> @endif
            </div>
            <div class="text-muted small">แสดงค่าเฉลี่ย (Mean) และส่วนเบี่ยงเบนมาตรฐาน (SD) พร้อมช่วง Mean±SD</div>
          </div>
        </div>
      </div>

      {{-- Tabs ปี --}}
      <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($YEARS as $y)
          <a class="btn btn-sm {{ ((int)$capYear === (int)$y) ? 'btn-success' : 'btn-outline-success' }}"
             style="border-radius:999px;"
             href="{{ route('capitals.index', array_filter(array_merge($baseParams, [
               'mode'=>'year','year'=>$y
             ]))) }}">
            ปี {{ $y }}
          </a>
        @endforeach
      </div>

      {{-- KPI (Mean + SD) --}}
      <div class="row g-3 mb-3">
        @foreach([
          'human'     => 'ทุนมนุษย์',
          'physical'  => 'ทุนกายภาพ',
          'financial' => 'ทุนการเงิน',
          'natural'   => 'ทุนธรรมชาติ',
          'social'    => 'ทุนสังคม',
        ] as $key=>$label)
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75 text-center h-100">
              <div class="card-body py-3">
                <div class="small text-muted">{{ $label }}</div>
                <div class="fw-bold fs-5" style="color:{{ $teal2 }};">
                  {{ number_format($summary[$key] ?? 0, 2) }}
                </div>
                <div class="text-muted" style="font-size:12px;">
                  SD: <b>{{ number_format($stdSafe[$key] ?? 0, 2) }}</b>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Radar + Table --}}
      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75">
            <div class="card-body">
              <div class="fw-semibold mb-2" style="color:{{ $teal2 }}">
                <i class="bi bi-pentagon-half me-1 text-success"></i> เรดาร์ทุน 5 ด้าน (Mean±SD)
              </div>
              <div class="border rounded-4 bg-white p-2">
                <div style="height:320px;">
                  <canvas id="radarCapitals"></canvas>
                </div>
              </div>
              <div class="small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i> เส้นประกอบ: Mean, Mean+SD, Mean-SD
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card border-0 rounded-4 shadow-soft bg-white bg-opacity-75">
            <div class="card-body">
              <div class="fw-semibold mb-2" style="color:{{ $teal2 }}">
                <i class="bi bi-table me-1 text-success"></i> ตารางสรุป (Average / SD)
              </div>

              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead class="table-light">
                    <tr class="text-muted" style="font-size:13px;">
                      <th style="width:40%;">ทุน 5 ด้าน</th>
                      <th class="text-end" style="width:30%;">ค่าเฉลี่ย</th>
                      <th class="text-end" style="width:30%;">SD</th>
                    </tr>
                  </thead>
                  <tbody style="font-size:14px;">
                    @php
                      $rows = [
                        ['k'=>'human',     'name'=>'มนุษย์'],
                        ['k'=>'physical',  'name'=>'กายภาพ'],
                        ['k'=>'financial', 'name'=>'เศรษฐกิจ'],
                        ['k'=>'natural',   'name'=>'ธรรมชาติ'],
                        ['k'=>'social',    'name'=>'ทางสังคม'],
                      ];
                    @endphp
                    @foreach($rows as $r)
                      <tr>
                        <td class="fw-semibold">{{ $r['name'] }}</td>
                        <td class="text-end fw-bold" style="color:{{ $teal2 }};">
                          {{ number_format($summary[$r['k']] ?? 0, 2) }}
                        </td>
                        <td class="text-end fw-bold">
                          {{ number_format($stdSafe[$r['k']] ?? 0, 2) }}
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

    </div>{{-- /col-lg-9 --}}
  </div>
</div>

<script>
  const radarData = @json($radarSafe);
  const radarStd  = @json($radarStdSafe);

  const radarEl = document.getElementById('radarCapitals');
  if (radarEl) {
    const mean = (radarData || []).map(v => Number(v || 0));
    const sd   = (radarStd  || []).map(v => Number(v || 0));

    const meanPlus  = mean.map((v,i) => v + (sd[i] || 0));
    const meanMinus = mean.map((v,i) => Math.max(0, v - (sd[i] || 0)));

    new Chart(radarEl, {
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
        plugins: { legend: { position:'bottom' } },
        scales: { r: { beginAtZero:true } }
      }
    });
  }
</script>

</body>
</html>
