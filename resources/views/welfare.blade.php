<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ข้อมูลสวัสดิการ</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body{ font-family:'Prompt',system-ui,sans-serif; }
    .pagination{ gap:6px; }
    .page-link{
      border-radius:999px!important;
      padding:6px 12px;
      border:1px solid #d7e2ea;
      color:#0B7F6F;
      font-size:13px;
    }
    .page-link:hover{ background:rgba(11,127,111,.08); border-color:#0B7F6F; color:#0B7F6F; }
    .page-item.active .page-link{ background:#0B7F6F; border-color:#0B7F6F; color:#fff; }
    .page-item.disabled .page-link{ color:#9aa7b2; background:#fff; }

    .dd-scroll{ max-height:320px; overflow:auto; }

    thead .filter-row th{
      background:#fff;
      position:sticky;
      top:42px;
      z-index:5;
      vertical-align:top;
    }
    thead .filter-row .form-select,
    thead .filter-row .form-control{ position:relative; z-index:10; }

    .shadow-soft{ box-shadow: 0 12px 28px rgba(2,6,23,.08)!important; }
  </style>
</head>

<body class="m-0"
  style="min-height:100vh;background:linear-gradient(135deg,#CFEFF3 0%,#DFF7EF 50%,#F0F8FB 100%);">

@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  $actionUrl = $actionUrl ?? route('welfare.index');

  $district = $district ?? '';
  $subdistrict = $subdistrict ?? '';

  $districtList = $districtList ?? collect([]);
  $subdistrictList = $subdistrictList ?? collect([]);

  // received | not_received | ''
  $welfare = $welfare ?? '';

  // any=OR | all=AND
  $welfare_match = $welfare_match ?? request('welfare_match','any');
  if (!in_array($welfare_match, ['any','all'], true)) $welfare_match = 'any';

  $house_id = $house_id ?? '';
  $title = $title ?? '';
  $fname = $fname ?? '';
  $lname = $lname ?? '';
  $cid = $cid ?? '';

  $survey_year = $survey_year ?? request('survey_year','');
  $age_range   = $age_range ?? request('age_range','');
  $sex         = $sex ?? request('sex','');

  $welfare_type = (array)($welfare_type ?? []);

  $counts = $counts ?? ['received'=>0,'not_received'=>0];
  $rows = $rows ?? collect([]);

  $receivedCount = (int)($counts['received'] ?? 0);
  $notReceivedCount = (int)($counts['not_received'] ?? 0);

  $types = [
    'a7_1' => 'เด็กแรกเกิด',
    'a7_2' => 'เบี้ยผู้สูงอายุ/คนชรา',
    'a7_3' => 'เบี้ยคนพิการ',
    'a7_4' => 'ประกันสังคม (ม.33)',
    'a7_5' => 'ประกันตนเอง (ม.40)',
    'a7_6' => 'บัตรสวัสดิการแห่งรัฐ',
  ];

  $welfareLabel =
    $welfare === 'received' ? 'ได้รับ' :
    ($welfare === 'not_received' ? 'ไม่ได้รับ' : 'ทั้งหมด');

  $typeCount = count($welfare_type);
  $matchLabel = $welfare_match === 'all' ? 'AND ได้รับ ครบทุกประเภท' : 'ได้รับ อย่างน้อย 1 ประเภท';
@endphp

<div class="container my-4">

  {{-- Header + Dropdown --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <a href="{{ url('/') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0" style="color:{{ $teal2 }};">
          <i class="bi bi-gift-fill"></i> ข้อมูลสวัสดิการ
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
                 'welfare'=>$welfare,
                 'welfare_match'=>$welfare_match,
                 'welfare_type'=>$welfare_type,
                 'house_id'=>$house_id,'title'=>$title,'fname'=>$fname,'lname'=>$lname,'cid'=>$cid,
                 'survey_year'=>$survey_year,'age_range'=>$age_range,'sex'=>$sex,
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
                   'welfare'=>$welfare,
                   'welfare_match'=>$welfare_match,
                   'welfare_type'=>$welfare_type,
                   'house_id'=>$house_id,'title'=>$title,'fname'=>$fname,'lname'=>$lname,'cid'=>$cid,
                   'survey_year'=>$survey_year,'age_range'=>$age_range,'sex'=>$sex,
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
                 'welfare'=>$welfare,
                 'welfare_match'=>$welfare_match,
                 'welfare_type'=>$welfare_type,
                 'house_id'=>$house_id,'title'=>$title,'fname'=>$fname,'lname'=>$lname,'cid'=>$cid,
                 'survey_year'=>$survey_year,'age_range'=>$age_range,'sex'=>$sex,
               ])) }}">
              ล้างตัวกรองตำบล
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>

          @foreach($subdistrictList as $sd)
            <li>
              <a class="dropdown-item"
                 href="{{ $actionUrl }}?{{ http_build_query(array_filter([
                   'district'=>$district,'subdistrict'=>$sd,
                   'welfare'=>$welfare,
                   'welfare_match'=>$welfare_match,
                   'welfare_type'=>$welfare_type,
                   'house_id'=>$house_id,'title'=>$title,'fname'=>$fname,'lname'=>$lname,'cid'=>$cid,
                   'survey_year'=>$survey_year,'age_range'=>$age_range,'sex'=>$sex,
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

  {{-- Cards --}}
  <div class="row g-4 mb-3">
    <div class="col-md-6">
      <div class="card border-0 shadow rounded-4 p-3 bg-white bg-opacity-90 h-100">
        <div class="fw-semibold text-secondary d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill" style="color:{{ $teal }}"></i>
          ได้รับสวัสดิการ
        </div>
        <div class="fw-bold display-6 mt-2" style="color:{{ $teal }};">
          {{ number_format($receivedCount) }}
        </div>
        <div class="small text-muted">(คน)</div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-0 shadow rounded-4 p-3 bg-white bg-opacity-90 h-100">
        <div class="fw-semibold text-secondary d-flex align-items-center gap-2">
          <i class="bi bi-x-circle-fill" style="color:#6B7280"></i>
          ไม่ได้รับสวัสดิการ
        </div>
        <div class="fw-bold display-6 mt-2" style="color:#6B7280;">
          {{ number_format($notReceivedCount) }}
        </div>
        <div class="small text-muted">(คน)</div>
      </div>
    </div>
  </div>

  {{-- ตาราง --}}
  <div class="card border-0 shadow-lg rounded-4 bg-white bg-opacity-90">
    <div class="card-body pb-0">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div class="fw-semibold" style="color:{{ $teal2 }};">
          <i class="bi bi-table"></i> รายชื่อบุคคล
        </div>
        <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
          <span>
            แสดงผลทั้งหมด
            <strong>{{ method_exists($rows,'total') ? number_format($rows->total()) : number_format(count($rows)) }}</strong>
            รายการ
          </span>

          @if($welfare === 'received')
            <span class="badge rounded-pill bg-success-subtle text-success border">ได้รับ</span>
          @elseif($welfare === 'not_received')
            <span class="badge rounded-pill bg-secondary-subtle text-secondary border">ไม่ได้รับ</span>
          @else
            <span class="badge rounded-pill bg-light text-dark border">ทั้งหมด</span>
          @endif

          @if($welfare === 'received' && $typeCount > 0)
            <span class="badge rounded-pill bg-light text-dark border">{{ $matchLabel }}</span>
          @endif
        </div>
      </div>
    </div>

    <form method="GET" action="{{ $actionUrl }}" id="filterForm">
      @if(!empty($district)) <input type="hidden" name="district" value="{{ $district }}"> @endif
      @if(!empty($subdistrict)) <input type="hidden" name="subdistrict" value="{{ $subdistrict }}"> @endif

      <input type="hidden" name="welfare" id="welfareHidden" value="{{ $welfare }}">
      <input type="hidden" name="welfare_match" id="welfareMatchHidden" value="{{ $welfare_match }}">

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead style="background:#F1F5F9;">
            <tr>
              <th style="min-width:90px;color:{{ $teal2 }};">ปีที่สำรวจ</th>
              <th style="min-width:150px;color:{{ $teal2 }};">รหัสบ้าน</th>
              <th style="min-width:150px;color:{{ $teal2 }};">ชื่อ</th>
              <th style="min-width:150px;color:{{ $teal2 }};">สกุล</th>
              <th style="min-width:150px;color:{{ $teal2 }};">อายุ</th>
              <th style="min-width:150px;color:{{ $teal2 }};">เพศ</th>
              <th style="min-width:160px;color:{{ $teal2 }};">สวัสดิการ</th>
              <th style="min-width:340px;color:{{ $teal2 }};">ประเภทสวัสดิการ</th>
              <th style="min-width:140px;color:{{ $teal2 }};">รายละเอียด</th>
            </tr>

            {{-- ✅ FIX: filter-row ต้องมี 9 <th> ให้ครบ --}}
            <tr class="filter-row">
              <th>
                <select class="form-select form-select-sm" name="survey_year">
                  <option value="">ปีที่สำรวจ (ทั้งหมด)</option>
                  @foreach([2564,2565,2566,2567,2568,2569] as $y)
                    <option value="{{ $y }}" @selected((string)$survey_year === (string)$y)>{{ $y }}</option>
                  @endforeach
                </select>
              </th>

              <th>
                <input class="form-control form-control-sm" style="max-width:120px"
                       name="house_id" value="{{ $house_id }}" placeholder="รหัสบ้าน">
              </th>

              <th>
                <input class="form-control form-control-sm" style="max-width:140px"
                       name="fname" value="{{ $fname }}" placeholder="ชื่อ">
              </th>

              <th>
                <input class="form-control form-control-sm" style="max-width:140px"
                       name="lname" value="{{ $lname }}" placeholder="สกุล">
              </th>

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

              {{-- ตัวกรองสวัสดิการ --}}
              <th>
                <div class="dropdown">
                  <button class="btn btn-sm dropdown-toggle w-100"
                          type="button"
                          data-bs-toggle="dropdown"
                          data-bs-auto-close="outside"
                          data-bs-boundary="viewport"
                          aria-expanded="false"
                          style="background:#fff;border:1px solid #E2E8F0;text-align:left;">
                    <i class="bi bi-funnel-fill text-success me-1"></i>
                    ตัวกรองข้อมูลสวัสดิการ
                  </button>

                  <div class="dropdown-menu p-3 border-0 shadow rounded-4"
                       style="width:380px; max-width:90vw; z-index:2000;">

                    <div class="overflow-auto overflow-x-hidden" style="max-height:60vh;">

                      <div class="fw-semibold text-secondary mb-2">
                        ขั้นตอนที่ 1 : เลือกสถานะสวัสดิการ
                      </div>
                      <div class="d-flex gap-2 mb-3 flex-wrap">
                        <button type="button"
                                class="btn btn-sm {{ $welfare==='' ? 'btn-success' : 'btn-outline-success' }}"
                                onclick="setWelfare('', true)">
                          ทั้งหมด
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ $welfare==='received' ? 'btn-success' : 'btn-outline-success' }}"
                                onclick="setWelfare('received', true)">
                          ได้รับสวัสดิการ
                        </button>
                        <button type="button"
                                class="btn btn-sm {{ $welfare==='not_received' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                                onclick="setWelfare('not_received', true)">
                          ไม่ได้รับสวัสดิการ
                        </button>
                      </div>

                      <div class="fw-semibold text-secondary mb-2">
                        ขั้นตอนที่ 2 : เลือกประเภทสวัสดิการ
                      </div>

                      <div class="row g-2 mb-3">
                        @foreach($types as $key=>$label)
                          <div class="col-6">
                            <label class="form-check w-100">
                              <input class="form-check-input"
                                     type="checkbox"
                                     name="welfare_type[]"
                                     value="{{ $key }}"
                                     @checked(in_array($key, $welfare_type))
                                     onchange="ensureReceived()">

                              <span class="badge rounded-pill w-100 text-start d-block text-truncate
                                {{ in_array($key,$welfare_type) ? 'bg-success-subtle text-success' : 'bg-light text-dark border' }}"
                                style="padding:.6rem .75rem;" title="{{ $label }}">
                                {{ $label }}
                              </span>
                            </label>
                          </div>
                        @endforeach
                      </div>

                      @if($typeCount > 1)
                        <div class="fw-semibold text-secondary mb-2">
                          ขั้นตอนที่ 3 : เงื่อนไขการแสดงผล
                        </div>

                        <div class="vstack gap-2 mb-3 small">
                          <label class="border rounded-3 p-2 d-flex gap-2">
                            <input type="radio" class="form-check-input mt-1"
                                   name="welfare_match_ui"
                                   value="any"
                                   @checked($welfare_match==='any')
                                   onclick="setMatch('any')">
                            <div>ได้รับ <strong>อย่างน้อย 1 ประเภท</strong></div>
                          </label>

                          <label class="border rounded-3 p-2 d-flex gap-2">
                            <input type="radio" class="form-check-input mt-1"
                                   name="welfare_match_ui"
                                   value="all"
                                   @checked($welfare_match==='all')
                                   onclick="setMatch('all')">
                            <div>ได้รับ <strong>ครบทุกประเภท</strong></div>
                          </label>
                        </div>
                      @endif

                    </div>

                    <div class="d-flex gap-2 pt-2 border-top">
                      <button type="button"
                              class="btn btn-sm btn-outline-secondary w-100"
                              onclick="clearWelfareTypes()">
                        ล้างการเลือก
                      </button>
                      <button type="submit"
                              class="btn btn-sm btn-success w-100">
                        แสดงผลข้อมูล
                      </button>
                    </div>

                  </div>
                </div>
              </th>

              {{-- ✅ FIX: ช่อง filter ของ "ประเภทสวัสดิการ" --}}
              <th></th>

              {{-- ✅ FIX: ช่อง filter ของ "รายละเอียด" --}}
              <th></th>
            </tr>
          </thead>

          <tbody>
            @forelse($rows as $r)
              @php
                $a70 = trim((string)($r->a7_0 ?? ''));
                $isNotReceivedRow = in_array($a70, ['ใช่','ไม่ได้รับ'], true);

                $statusLabel = $isNotReceivedRow ? 'ไม่ได้รับ' : 'ได้รับ';
                $statusClass = $isNotReceivedRow
                  ? 'bg-secondary-subtle text-secondary border'
                  : 'bg-success-subtle text-success border';

                $wMap = [
                  'a7_1' => 'เด็กแรกเกิด',
                  'a7_2' => 'เบี้ยผู้สูงอายุ/คนชรา',
                  'a7_3' => 'เบี้ยคนพิการ',
                  'a7_4' => 'ประกันสังคม (ม.33)',
                  'a7_5' => 'ประกันตนเอง (ม.40)',
                  'a7_6' => 'บัตรสวัสดิการแห่งรัฐ',
                ];

                $isYes = fn($v) => trim((string)$v) === 'ได้รับ';

                $receivedList = [];
                if(!$isNotReceivedRow){
                  $selectedCols = array_values(array_intersect($welfare_type, array_keys($wMap)));
                  $showCols = !empty($selectedCols) ? $selectedCols : array_keys($wMap);

                  foreach($showCols as $col){
                    $label = $wMap[$col] ?? $col;
                    if(isset($r->$col) && $isYes($r->$col)){
                      $receivedList[] = $label;
                    }
                  }
                }
              @endphp

              <tr
                data-house="{{ $r->house_Id ?? '' }}"
                data-year="{{ $r->survey_Year ?? '' }}"

                {{-- ที่อยู่ --}}
                data-house_number="{{ $r->house_Number ?? '' }}"
                data-village_no="{{ $r->village_No ?? '' }}"
                data-village_name="{{ $r->village_Name ?? '' }}"
                data-postcode="{{ $r->survey_Postcode ?? '' }}"

                data-subdistrict="{{ $r->survey_Subdistrict ?? '' }}"
                data-district="{{ $r->survey_District ?? '' }}"

                {{-- พิกัด --}}
                data-lat="{{ $r->latitude ?? '' }}"
                data-lng="{{ $r->longitude ?? '' }}"

                {{-- บุคคล --}}
                data-order="{{ $r->human_Order ?? '' }}"
                data-title="{{ $r->human_Member_title ?? '' }}"
                data-fname="{{ $r->human_Member_fname ?? '' }}"
                data-lname="{{ $r->human_Member_lname ?? '' }}"
                data-agey="{{ $r->human_Age_y ?? '' }}"
                data-sex="{{ $r->human_Sex ?? '' }}"
                data-cid="{{ $r->human_Member_cid ?? '' }}"

                {{-- เบอร์โทร --}}
                data-phone="{{ $r->survey_Informer_phone ?? '' }}"

                data-health="{{ $r->human_Health ?? '' }}"
                data-welfare='@json(array_values(array_unique($receivedList ?? [])))'
              >
                <td class="ps-3 fw-semibold">{{ $r->survey_Year ?? '-' }}</td>
                <td class="fw-semibold">{{ $r->house_Id ?? '-' }}</td>
                <td>{{ $r->human_Member_fname ?? '-' }}</td>
                <td>{{ $r->human_Member_lname ?? '-' }}</td>
                <td>{{ $r->human_Age_y ?? '-' }}</td>
                <td>{{ $r->human_Sex ?? '-' }}</td>

                <td>
                  <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">
                    {{ $statusLabel }}
                  </span>
                </td>

                <td class="welfare-cell">
                  @if($isNotReceivedRow)
                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border">-</span>
                  @else
                    @if(count($receivedList) === 0)
                      <span class="badge rounded-pill bg-secondary-subtle text-secondary border">ไม่ระบุ</span>
                    @else
                      <div class="d-flex flex-wrap gap-1">
                        @foreach(array_unique($receivedList) as $item)
                          <span class="badge rounded-pill bg-light text-dark border">{{ $item }}</span>
                        @endforeach
                      </div>
                    @endif
                  @endif
                </td>

                <td class="text-end pe-3" style="width:1%;white-space:nowrap;">
                  <button type="button"
                          class="btn btn-sm fw-semibold shadow-sm d-inline-flex align-items-center gap-1"
                          style="border-radius:999px;background:{{ $teal }};color:#fff;padding:.35rem .75rem;"
                          data-bs-toggle="modal" data-bs-target="#detailModal"
                          onclick="openDetail(this.closest('tr'))">
                    <i class="bi bi-eye"></i>
                    <span>ดูรายละเอียด</span>
                  </button>
                </td>
              </tr> {{-- ✅ FIX: ปิดแถวให้ครบ --}}
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{-- Pagination --}}
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

{{-- =========================
     MODAL
========================= --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content rounded-4 shadow">

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

      <div class="modal-body bg-light">
        <div class="row g-3">

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

          <div class="col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
              <div class="card-header bg-white border-0 pb-0">
                <div class="fw-semibold text-success small">
                  <i class="bi bi-person-fill"></i> ข้อมูลบุคคล
                </div>
              </div>

              <div class="card-body pt-2">
                <div class="row g-2 small">

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

                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-white">
                      <div class="text-secondary small">บัตรประชาชน</div>
                      <div class="fw-semibold font-monospace" id="m_cid"></div>
                    </div>
                  </div>

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

                  {{-- ✅ สวัสดิการ --}}
                  <div class="col-12">
                    <div class="border rounded-3 p-2 bg-success-subtle">
                      <div class="text-secondary small">สวัสดิการ</div>
                      <div class="fw-semibold" id="m_welfare"></div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

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

      <div class="modal-footer bg-white border-0">
        <button class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">
          ปิด
        </button>
      </div>

    </div>
  </div>
</div>

<script>
  // ✅ ทำให้ dropdown ใน table-responsive ไม่โดน overflow ตัด
  function initDropdownFixed() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((el) => {
      if (el.dataset.ddFixedInit === '1') return;
      el.dataset.ddFixedInit = '1';

      new bootstrap.Dropdown(el, {
        popperConfig: {
          strategy: 'fixed',
          modifiers: [
            { name: 'preventOverflow', options: { boundary: 'viewport' } },
            { name: 'flip', options: { boundary: 'viewport' } }
          ]
        }
      });
    });
  }
  document.addEventListener('DOMContentLoaded', initDropdownFixed);

  function setMatch(val){
    const m = document.getElementById('welfareMatchHidden');
    if(m) m.value = val;
  }

  function setWelfare(val, autoSubmit = false){
    const hidden = document.getElementById('welfareHidden');
    if(hidden) hidden.value = val;

    if(val !== 'received'){
      clearWelfareTypes();
      setMatch('any');
    }

    if(autoSubmit){
      document.getElementById('filterForm').submit();
    }
  }

  function ensureReceived(){
    const hidden = document.getElementById('welfareHidden');
    if(hidden) hidden.value = 'received';

    const m = document.getElementById('welfareMatchHidden');
    if(m && !m.value) m.value = 'any';
  }

  function clearWelfareTypes(){
    document.querySelectorAll('input[name="welfare_type[]"]').forEach(cb => cb.checked = false);
  }

  // ✅ เติมข้อมูลเข้า modal
  function openDetail(tr){
    if(!tr) return;
    const get = (k) => (tr.dataset[k] ?? '').toString().trim();

    // บ้าน/พื้นที่
    document.getElementById('m_house').textContent = get('house') || '-';
    document.getElementById('m_year').textContent  = get('year') || '-';
    document.getElementById('m_house_number').textContent = get('house_number') || '-';
    document.getElementById('m_village_no').textContent   = get('village_no') || '-';
    document.getElementById('m_village_name').textContent = get('village_name') || '-';
    document.getElementById('m_subdistrict').textContent  = get('subdistrict') || '-';
    document.getElementById('m_district').textContent     = get('district') || '-';
    document.getElementById('m_postcode').textContent     = get('postcode') || '';
    document.getElementById('m_lat').textContent = get('lat') || '-';
    document.getElementById('m_lng').textContent = get('lng') || '-';

    // บุคคล
    document.getElementById('m_order').textContent = get('order') || '-';
    document.getElementById('m_title').textContent = get('title') || '';
    document.getElementById('m_fname').textContent = get('fname') || '-';
    document.getElementById('m_lname').textContent = get('lname') || '-';
    document.getElementById('m_agey').textContent  = get('agey') || '-';
    document.getElementById('m_sex').textContent   = get('sex') || '-';
    document.getElementById('m_cid').textContent   = get('cid') || '-';
    document.getElementById('m_phone').textContent = get('phone') || '-';

    // ปุ่มโทร
    const phone = (get('phone') || '').replace(/\D/g,'');
    const callBtn = document.getElementById('m_call');
    if(callBtn){
      if(phone){
        callBtn.href = `tel:${phone}`;
        callBtn.classList.remove('d-none');
      }else{
        callBtn.classList.add('d-none');
      }
    }

    // สวัสดิการ (badge)
    const welfareEl = document.getElementById('m_welfare');
    if (welfareEl) {
      let list = [];
      try { list = JSON.parse(get('welfare') || '[]'); } catch (e) { list = []; }

      if (!Array.isArray(list) || list.length === 0) {
        welfareEl.innerHTML = `<span class="badge rounded-pill bg-light text-dark border">ไม่ระบุ</span>`;
      } else {
        welfareEl.innerHTML = list
          .map(x => `<span class="badge rounded-pill bg-light text-dark border me-1 mb-1">${String(x)}</span>`)
          .join('');
      }
    }

    // แผนที่
    const lat = get('lat'), lng = get('lng');
    const mapWrap = document.getElementById('m_map_wrap');
    const mapEmpty= document.getElementById('m_map_empty');
    const mapIframe = document.getElementById('m_map_iframe');
    const mapLink = document.getElementById('m_map_link');

    if(lat && lng){
      const q = `${lat},${lng}`;
      if(mapIframe) mapIframe.src = `https://www.google.com/maps?q=${encodeURIComponent(q)}&z=16&output=embed`;
      if(mapWrap) mapWrap.style.display = '';
      if(mapEmpty) mapEmpty.style.display = 'none';
      if(mapLink){
        mapLink.href = `https://www.google.com/maps?q=${encodeURIComponent(q)}&z=16`;
        mapLink.classList.remove('d-none');
      }
    }else{
      if(mapWrap) mapWrap.style.display = 'none';
      if(mapEmpty) mapEmpty.style.display = '';
      if(mapLink) mapLink.classList.add('d-none');
    }
  }
</script>

</body>
</html>
