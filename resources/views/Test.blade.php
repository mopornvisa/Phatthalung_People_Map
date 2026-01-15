{{-- resources/views/test.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ข้อมูลสุขภาพ</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .pagination { gap: 6px; }
    .page-link{
      border-radius: 999px !important;
      padding: 6px 12px;
      border: 1px solid #d7e2ea;
      color: #0B7F6F;
      font-size: 13px;
    }
    .page-link:hover{
      background: rgba(11,127,111,.08);
      border-color: #0B7F6F;
      color: #0B7F6F;
    }
    .page-item.active .page-link{
      background: #0B7F6F;
      border-color: #0B7F6F;
      color: #fff;
    }
    .page-item.disabled .page-link{
      color: #9aa7b2;
      background: #fff;
    }

    .dd-scroll{ max-height: 320px; overflow:auto; }

    thead .filter-row th{
      background:#fff;
      position: sticky;
      top: 42px;
      z-index: 5;
    }
    thead .filter-row .form-select,
    thead .filter-row .form-control{
      position: relative;
      z-index: 10;
    }
  </style>
</head>

<body class="m-0"
  style="font-family:'Prompt',system-ui,sans-serif;
         min-height:100vh;
         background:linear-gradient(135deg,#CFEFF3 0%,#DFF7EF 50%,#F0F8FB 100%);">

@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';
  $actionUrl = route('health.index');

  $health      = $health ?? '';
  $district    = $district ?? '';
  $subdistrict = $subdistrict ?? '';
  $districtList = $districtList ?? collect([]);
  $subdistrictList = $subdistrictList ?? collect([]);

  $house_id    = $house_id ?? '';
  $survey_year = $survey_year ?? '';
  $fname       = $fname ?? '';
  $lname       = $lname ?? '';
  $cid         = $cid ?? '';
  $agey        = $agey ?? '';
  $sex         = $sex ?? '';
  $age_range   = $age_range ?? request('age_range','');

  $HEALTH_OPTIONS = $HEALTH_OPTIONS ?? [
    'ปกติ',
    'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)',
    'พิการพึ่งตนเองได้',
    'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้',
  ];

  $HEALTH_NULL_TOKEN = $HEALTH_NULL_TOKEN ?? '__NULL__';

  $counts = $counts ?? [];
  $rows = $rows ?? collect([]);

  function healthBadge($h){
    $h = trim((string)$h);
    if ($h === 'ปกติ') return ['bg'=>'#0B7F6F','text'=>'#fff','label'=>$h];
    if ($h === 'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)') return ['bg'=>'#F6C277','text'=>'#1f2937','label'=>'ป่วยเรื้อรังไม่ติดเตียง'];
    if ($h === 'พิการพึ่งตนเองได้') return ['bg'=>'#0F9BD8','text'=>'#fff','label'=>$h];
    if ($h === 'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้') return ['bg'=>'#DC3545','text'=>'#fff','label'=>'ติดเตียง/พึ่งตัวเองไม่ได้'];
    return ['bg'=>'#6B7280','text'=>'#fff','label'=>($h ?: 'ไม่ระบุ')];
  }
@endphp

<div class="container my-4">

  {{-- Header + Dropdown --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0" style="color:{{ $teal2 }};">
          <i class="bi bi-heart-pulse-fill"></i> ข้อมูลสุขภาพ
        </h4>
      </a>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">

      {{-- Dropdown อำเภอ --}}
      <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle shadow-sm"
                data-bs-toggle="dropdown"
                style="background:{{ $teal }}; color:#fff; border-radius:10px;">
          <i class="bi bi-geo-alt-fill"></i>
          {{ !empty($district) ? "อ.{$district}" : "เลือกอำเภอ" }}
        </button>

        <ul class="dropdown-menu rounded-4 border-0 shadow dd-scroll" style="min-width:260px;">
          <li>
            <a class="dropdown-item text-danger"
               href="{{ $actionUrl }}?{{ http_build_query(array_filter([
                  'health'=>$health,
                  'house_id'=>$house_id, 'survey_year'=>$survey_year, 'fname'=>$fname, 'lname'=>$lname,
                  'cid'=>$cid, 'age_range'=>$age_range, 'agey'=>$agey, 'sex'=>$sex,
               ])) }}">
              ล้างตัวกรองอำเภอ
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>

          @foreach($districtList as $d)
            <li>
              <a class="dropdown-item"
                 href="{{ $actionUrl }}?{{ http_build_query(array_filter([
                    'district'=>$d,
                    'health'=>$health,
                    'house_id'=>$house_id, 'survey_year'=>$survey_year, 'fname'=>$fname, 'lname'=>$lname,
                    'cid'=>$cid, 'age_range'=>$age_range, 'agey'=>$agey, 'sex'=>$sex,
                 ])) }}">
                อ.{{ $d }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Dropdown ตำบล --}}
      <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle shadow-sm"
                data-bs-toggle="dropdown"
                style="background:{{ $teal }}; color:#fff; border-radius:10px;"
                @if(empty($district)) disabled @endif>
          <i class="bi bi-pin-map-fill"></i>
          {{ !empty($subdistrict) ? "ต.{$subdistrict}" : "เลือกตำบล" }}
        </button>

        <ul class="dropdown-menu rounded-4 border-0 shadow dd-scroll" style="min-width:260px;">
          <li>
            <a class="dropdown-item text-danger"
               href="{{ $actionUrl }}?{{ http_build_query(array_filter([
                  'district'=>$district,
                  'health'=>$health,
                  'house_id'=>$house_id, 'survey_year'=>$survey_year, 'fname'=>$fname, 'lname'=>$lname,
                  'cid'=>$cid, 'age_range'=>$age_range, 'agey'=>$agey, 'sex'=>$sex,
               ])) }}">
              ล้างตัวกรองตำบล
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>

          @foreach($subdistrictList as $sd)
            <li>
              <a class="dropdown-item"
                 href="{{ $actionUrl }}?{{ http_build_query(array_filter([
                    'district'=>$district,
                    'subdistrict'=>$sd,
                    'health'=>$health,
                    'house_id'=>$house_id, 'survey_year'=>$survey_year, 'fname'=>$fname, 'lname'=>$lname,
                    'cid'=>$cid, 'age_range'=>$age_range, 'agey'=>$agey, 'sex'=>$sex,
                 ])) }}">
                ต.{{ $sd }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      <a class="btn btn-sm shadow-sm"
         style="background:#fff;border:1px solid #E2E8F0;color:#334155;border-radius:999px;"
         href="{{ $actionUrl }}">
        ล้างทั้งหมด
      </a>
    </div>
  </div>

  {{-- การ์ดสรุป --}}
  <div class="row g-4 mb-3">
    @foreach($HEALTH_OPTIONS as $opt)
      @php
        $isActive = ($health === $opt);
        $count = $counts[$opt] ?? 0;
        $b = healthBadge($opt);
      @endphp

      <div class="col-md-3">
        <a class="text-decoration-none"
           href="{{ $actionUrl }}?{{ http_build_query(array_filter([
              'health'=>$opt,
              'district'=>$district,
              'subdistrict'=>$subdistrict,
              'house_id'=>$house_id, 'survey_year'=>$survey_year,
              'fname'=>$fname, 'lname'=>$lname,
              'cid'=>$cid, 'age_range'=>$age_range, 'agey'=>$agey, 'sex'=>$sex,
           ])) }}">

          <div class="card border-0 shadow rounded-4 p-3 text-center bg-white bg-opacity-90"
               style="transition:.25s; {{ $isActive ? "outline:2px solid {$teal};" : "" }}"
               onmouseover="this.style.transform='scale(1.02)'"
               onmouseout="this.style.transform='scale(1)'">

            <div class="text-secondary small mb-1">{{ $opt }}</div>
            <div class="fw-bold fs-3" style="color:{{ $teal }};">{{ number_format($count) }}</div>
            <div class="small text-muted">(คน)</div>

            <div class="mt-2">
              <span class="badge" style="background:{{ $b['bg'] }};color:{{ $b['text'] }};">
                {{ $isActive ? 'กำลังกรอง' : 'กดเพื่อกรอง' }}
              </span>
            </div>

          </div>
        </a>
      </div>
    @endforeach
  </div>

  <div class="card border-0 shadow-lg rounded-4 bg-white bg-opacity-90">
    <div class="card-body pb-0">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
        <div class="fw-semibold d-flex align-items-center gap-3" style="color:{{ $teal2 }};">
          <i class="bi bi-table"></i>
          <span>รายชื่อบุคคล</span>
          @php $nullCount = $counts[$HEALTH_NULL_TOKEN] ?? 0; @endphp
          <span class="small" style="color:#6B7280;">ไม่ระบุ {{ number_format($nullCount) }} คน</span>
        </div>

        <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
          <span>
            แสดงผลทั้งหมด
            <strong>{{ method_exists($rows,'total') ? number_format($rows->total()) : number_format(count($rows)) }}</strong>
            รายการ
          </span>
        </div>
      </div>
    </div>

    <form method="GET" action="{{ $actionUrl }}" id="filterForm">
      @if(!empty($district)) <input type="hidden" name="district" value="{{ $district }}"> @endif
      @if(!empty($subdistrict)) <input type="hidden" name="subdistrict" value="{{ $subdistrict }}"> @endif
      @if(!empty($age_range)) <input type="hidden" name="age_range" value="{{ $age_range }}"> @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead style="background:#F1F5F9;">
            <tr>
              <th style="min-width:90px;color:{{ $teal2 }};">ปีที่สำรวจ</th>
              <th class="ps-3" style="min-width:120px;color:{{ $teal2 }};">รหัสบ้าน</th>
              <th style="min-width:140px;color:{{ $teal2 }};">ชื่อ</th>
              <th style="min-width:140px;color:{{ $teal2 }};">สกุล</th>
              <th style="min-width:90px;color:{{ $teal2 }};">อายุ(ปี)</th>
              <th style="min-width:90px;color:{{ $teal2 }};">เพศ</th>
              <th style="min-width:260px;color:{{ $teal2 }};">สุขภาพ</th>
              <th class="text-end pe-3" style="min-width:140px;color:{{ $teal2 }};white-space:nowrap;">เพิ่มเติม</th>
            </tr>

            <tr class="filter-row">
              <th>
                <select class="form-select form-select-sm" name="survey_year">
                  <option value="">ปีที่สำรวจ (ทั้งหมด)</option>
                  @foreach([2564,2565,2566,2567,2568,2569] as $y)
                    <option value="{{ $y }}" @selected((string)$survey_year === (string)$y)>{{ $y }}</option>
                  @endforeach
                </select>
              </th>

              <th class="ps-3"><input class="form-control form-control-sm" name="house_id" value="{{ $house_id }}" placeholder="รหัสบ้าน"></th>
              <th><input class="form-control form-control-sm" name="fname" value="{{ $fname }}" placeholder="ชื่อ"></th>
              <th><input class="form-control form-control-sm" name="lname" value="{{ $lname }}" placeholder="สกุล"></th>

              <th>
                <select class="form-select form-select-sm" name="age_range">
                  <option value="">ช่วงอายุ (ทั้งหมด)</option>
                  <option value="0-15"  @selected($age_range==='0-15')>0 – 15 ปี</option>
                  <option value="16-28" @selected($age_range==='16-28')>16 – 28 ปี</option>
                  <option value="29-44" @selected($age_range==='29-44')>29 – 44 ปี</option>
                  <option value="45-59" @selected($age_range==='45-59')>45 – 59 ปี</option>
                  <option value="60-78" @selected($age_range==='60-78')>60 – 78 ปี</option>
                  <option value="79-97" @selected($age_range==='79-97')>79 – 97 ปี</option>
                  <option value="98+"   @selected($age_range==='98+')>98 ปีขึ้นไป</option>
                </select>
              </th>

              <th>
                <select class="form-select form-select-sm" name="sex">
                  <option value="">เพศ (ทั้งหมด)</option>
                  <option value="ชาย"  @selected($sex === 'ชาย')>ชาย</option>
                  <option value="หญิง" @selected($sex === 'หญิง')>หญิง</option>
                </select>
              </th>

              <th style="width:220px; max-width:220px;">
                <select class="form-select form-select-sm" name="health" style="max-width:220px;">
                  <option value="">สุขภาพ (ทั้งหมด)</option>
                  <option value="{{ $HEALTH_NULL_TOKEN }}" @selected($health === $HEALTH_NULL_TOKEN)>ไม่ระบุ</option>
                  @foreach($HEALTH_OPTIONS as $opt)
                    <option value="{{ $opt }}" @selected($health === $opt)>{{ $opt }}</option>
                  @endforeach
                </select>
              </th>

              <th></th>
            </tr>
          </thead>

          <tbody>
            @forelse($rows as $r)
              @php $b = healthBadge($r->human_Health); @endphp

            <tr
                data-house="{{ e($r->house_Id) }}"
                data-district="{{ e($r->survey_District) }}"
                data-subdistrict="{{ e($r->survey_Subdistrict) }}"
                data-year="{{ e($r->survey_Year) }}"
                data-order="{{ e($r->human_Order) }}"
                data-title="{{ e($r->human_Member_title) }}"
                data-sex="{{ e($r->human_Sex) }}"
                data-agey="{{ e($r->human_Age_y) }}"
                data-health="{{ e($r->human_Health) }}"
                data-lat="{{ e($r->latitude) }}"
                data-lng="{{ e($r->longitude) }}"
                data-cid="{{ e($r->human_Member_cid) }}"
                data-phone="{{ e($r->survey_Informer_phone) }}"
                data-fname="{{ e($r->human_Member_fname) }}"
                data-lname="{{ e($r->human_Member_lname) }}"
                data-house-number="{{ e($r->house_Number) }}"
                data-village-no="{{ e($r->village_No) }}"
                data-village-name="{{ e($r->village_Name) }}"
                data-postcode="{{ e($r->survey_Postcode) }}"
                >
                <td class="ps-3 fw-semibold">{{ $r->survey_Year }}</td>
                <td>{{ $r->house_Id }}</td>
                <td>{{ $r->human_Member_fname }}</td>
                <td>{{ $r->human_Member_lname }}</td>
                <td>{{ $r->human_Age_y }}</td>
                <td>{{ $r->human_Sex }}</td>
                <td>
                  <span class="badge" style="background:{{ $b['bg'] }};color:{{ $b['text'] }};">
                    {{ $b['label'] }}
                  </span>
                </td>
                <td class="text-end pe-3" style="white-space:nowrap;">
                  <button type="button"
                          class="btn btn-sm fw-semibold shadow-sm d-inline-flex align-items-center gap-1"
                          style="border-radius:999px;background:{{ $teal }};color:#fff;padding:.35rem .75rem;"
                          data-bs-toggle="modal" data-bs-target="#detailModal"
                          onclick="openDetail(this.closest('tr'))">
                    <i class="bi bi-eye"></i> ดูรายละเอียด
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    @if($rows instanceof \Illuminate\Pagination\LengthAwarePaginator)
      <div class="py-3">
        <div class="text-center text-muted small mb-2">
          แสดง {{ $rows->firstItem() }}–{{ $rows->lastItem() }}
          จาก {{ number_format($rows->total()) }} รายการ
        </div>
        <div class="d-flex justify-content-center">
          {{ $rows->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
      </div>
    @endif
  </div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content rounded-4 shadow">

      <!-- Header -->
      <div class="modal-header text-white"
           style="background:linear-gradient(135deg,#0B7F6F,#0B5B6B)">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-vcard fs-5"></i>
          <div>
            <div class="fw-semibold small">ข้อมูลเพิ่มเติม</div>
            <div class="opacity-75" style="font-size:12px;">
              รายละเอียดบุคคล • ที่อยู่ • พิกัด
            </div>
          </div>
        </div>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body bg-light">
        <div class="row g-3">

          <!-- บ้าน / พื้นที่ -->
          <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-header bg-white border-0 pb-0">
                <div class="fw-semibold text-success small">
                  <i class="bi bi-geo-alt-fill"></i> ข้อมูลบ้าน/พื้นที่
                </div>
              </div>

              <div class="card-body pt-2">
                <div class="row g-2 small">

                  <div class="col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                      <div class="text-secondary small">รหัสบ้าน</div>
                      <div class="fw-semibold" id="m_house"></div>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                      <div class="text-secondary small">ปีที่สำรวจ</div>
                      <div class="fw-semibold" id="m_year"></div>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-white">
                      <div class="text-secondary small">ที่อยู่</div>
                      <div class="fw-semibold lh-base">
                        บ้านเลขที่ <span id="m_house_number"></span>
                        หมู่ที่ <span id="m_village_no"></span>
                        บ้าน<span id="m_village_name"></span><br>
                        ตำบล<span id="m_subdistrict"></span>
                        อำเภอ<span id="m_district"></span>
                        จังหวัดพัทลุง
                        <span id="m_postcode"></span>
                      </div>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                      <div class="text-secondary small">ละติจูด</div>
                      <div class="fw-semibold font-monospace" id="m_lat"></div>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="border rounded-3 p-2 bg-white h-100">
                      <div class="text-secondary small">ลองจิจูด</div>
                      <div class="fw-semibold font-monospace" id="m_lng"></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <!-- บุคคล -->
          <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-header bg-white border-0 pb-0">
                <div class="fw-semibold text-success small">
                  <i class="bi bi-person-fill"></i> ข้อมูลบุคคล
                </div>
              </div>

              <div class="card-body pt-2">
                <div class="row g-2 small">

                  <!-- ลำดับ + คำนำหน้า/ชื่อสกุล -->
                  <div class="col-12">
                    <div class="row g-2">

                      <div class="col-4 col-md-3">
                        <div class="border rounded-3 p-2 bg-white h-100">
                          <div class="text-secondary small">ลำดับที่</div>
                          <div class="fw-semibold" id="m_order"></div>
                        </div>
                      </div>

                      <div class="col-8 col-md-9">
                        <div class="border rounded-3 p-2 bg-white h-100">
                          <div class="text-secondary small">คำนำหน้า / ชื่อ - สกุล</div>
                          <div class="fw-bold fs-6">
                            <span id="m_title" class="me-1"></span>
                            <span id="m_fname"></span>
                            <span id="m_lname" class="ms-1"></span>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- อายุ + เพศ -->
                  <div class="col-12">
                    <div class="row g-2">
                      <div class="col-6 col-md-4">
                        <div class="border rounded-3 p-2 bg-white h-100">
                          <div class="text-secondary small">อายุ</div>
                          <div class="fw-semibold">
                            <span id="m_agey"></span> <span id="m_age_suffix">ปี</span>
                          </div>
                        </div>
                      </div>

                      <div class="col-6 col-md-4">
                        <div class="border rounded-3 p-2 bg-white h-100">
                          <div class="text-secondary small">เพศ</div>
                          <div class="fw-semibold" id="m_sex"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- บัตรประชาชน -->
                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-white">
                      <div class="text-secondary small">บัตรประชาชน</div>
                      <div class="fw-semibold font-monospace" id="m_cid"></div>
                    </div>
                  </div>

                  <!-- เบอร์ติดต่อ + ปุ่มโทร -->
                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-white d-flex align-items-center justify-content-between gap-2">
                      <div>
                        <div class="text-secondary small">เบอร์ติดต่อ</div>
                        <div class="fw-semibold font-monospace" id="m_phone"></div>
                      </div>

                      <a id="m_call"
                         class="btn btn-sm btn-success rounded-pill px-3 d-none"
                         target="_self">
                        <i class="bi bi-telephone-fill"></i> โทร
                      </a>
                    </div>
                  </div>

                  <!-- สุขภาพ -->
                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-success-subtle">
                      <div class="text-secondary small">สุขภาพ</div>
                      <div class="fw-semibold text-success" id="m_health"></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <!-- แผนที่ -->
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
              <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div class="fw-semibold text-success small">
                  <i class="bi bi-map"></i> แผนที่
                </div>
                <a id="m_map_link"
                   target="_blank"
                   class="btn btn-sm btn-outline-success rounded-pill d-none">
                  เปิด Google Maps
                </a>
              </div>

              <div id="m_map_wrap" style="display:none;">
                <iframe id="m_map_iframe"
                        class="w-100"
                        style="height:300px;border:0"
                        loading="lazy"></iframe>
              </div>

              <div id="m_map_empty" class="text-center text-secondary small py-3">
                ไม่มีพิกัดแผนที่
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer bg-white border-0">
        <button class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">
          ปิด
        </button>
      </div>

    </div>
  </div>
</div>

<script>
  // ✅ Enter ในช่องค้นหา -> submit (แก้ "ค้นหาไม่ได้")
  document.getElementById('filterForm')?.addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      this.submit();
    }
  });

  // ✅ เปลี่ยน select -> submit
  document.querySelectorAll('#filterForm thead .filter-row select').forEach(el=>{
    el.addEventListener('change', ()=> document.getElementById('filterForm').submit());
  });

  function openDetail(row){
    const d = row.dataset;

    const set = (id, val, fallback='-') => {
      const el = document.getElementById(id);
      if (!el) return;
      const v = (val ?? '').toString().trim();
      el.textContent = v !== '' ? v : fallback;
    };

    // บ้าน/พื้นที่
    set('m_house', d.house);
    set('m_house_number', d.houseNumber);
    set('m_village_no', d.villageNo);
    set('m_village_name', d.villageName);
    set('m_postcode', d.postcode);
    set('m_district', d.district);
    set('m_subdistrict', d.subdistrict);
    set('m_year', d.year);

    const latv = (d.lat ?? '').toString().trim();
    const lngv = (d.lng ?? '').toString().trim();
    set('m_lat', latv);
    set('m_lng', lngv);

    // บุคคล
    set('m_order', d.order);
    set('m_sex', d.sex);
    set('m_agey', d.agey);
    set('m_title', d.title, ''); // ✅ คำนำหน้า
    set('m_fname', d.fname);
    set('m_lname', d.lname);
    set('m_cid', d.cid);
    set('m_phone', d.phone);

    const hv = (d.health ?? '').toString().trim();
    const healthEl = document.getElementById('m_health');
    if (healthEl) healthEl.textContent = hv !== '' ? hv : 'ไม่ระบุ';

    // แผนที่
    const wrap   = document.getElementById('m_map_wrap');
    const empty  = document.getElementById('m_map_empty');
    const iframe = document.getElementById('m_map_iframe');
    const link   = document.getElementById('m_map_link');

    const latNum = Number(latv);
    const lngNum = Number(lngv);
    const hasLatLng = Number.isFinite(latNum) && Number.isFinite(lngNum);

    if (wrap && empty && iframe && link) {
      if (hasLatLng) {
        const q = encodeURIComponent(`${latNum},${lngNum}`);
        iframe.src = `https://www.google.com/maps?q=${q}&z=16&output=embed`;
        link.href  = `https://www.google.com/maps?q=${q}&z=16`;

        wrap.style.display  = 'block';
        empty.style.display = 'none';
        link.classList.remove('d-none');
      } else {
        iframe.src = '';
        link.href  = '#';

        wrap.style.display  = 'none';
        empty.style.display = 'block';
        link.classList.add('d-none');
      }
    }
  }
</script>

</body>
</html>
