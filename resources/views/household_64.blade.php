<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>ข้อมูลครัวเรือน ปี 2564</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .pagination{ gap:6px; }
    .page-link{
      border-radius:999px !important;
      padding:6px 12px;
      border:1px solid #d7e2ea;
      color:#0B7F6F;
      font-size:13px;
    }
    .page-link:hover{ background:rgba(11,127,111,.08); border-color:#0B7F6F; color:#0B7F6F; }
    .page-item.active .page-link{ background:#0B7F6F; border-color:#0B7F6F; color:#fff; }
    .page-item.disabled .page-link{ color:#9aa7b2; background:#fff; }

    /* sticky filter row ใต้หัวตาราง */
    thead .filter-row th{
      background:#fff;
      position: sticky;
      top: 44px;
      z-index: 5;
      vertical-align: top;
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

  // ช่องค้นหาเดิม
  $q = request('q','');

  // ✅ ตัวกรองใหม่ (ถ้าอยากใช้)
  $survey_year = request('survey_year','');
  $district    = request('district','');
  $subdistrict = request('subdistrict','');
  $village     = request('village','');
  $house_id    = request('house_id','');
  $cid         = request('cid','');

  $has_book    = request('has_book',''); // '' | 1 | 0

  // รายการ dropdown ปี (แก้เพิ่มได้)
  $yearList = [2564,2565,2566,2567,2568,2569];

  // สร้าง list อำเภอ/ตำบลจากชุดข้อมูลที่มีในหน้า (ไม่ต้องไป query เพิ่ม)
  $districtList = collect($surveys->items())->pluck('survey_District')->filter()->unique()->values();
  $subdistrictList = collect($surveys->items())
      ->when($district !== '', fn($c)=>$c->where('survey_District',$district))
      ->pluck('survey_Subdistrict')->filter()->unique()->values();
@endphp

<div class="container my-4">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <a href="{{ route('dashboard') }}" class="text-decoration-none d-inline-flex align-items-center gap-2">
        <h4 class="fw-bold mb-0" style="color:{{ $teal2 }};">
          <i class="bi bi-house-door-fill"></i> ข้อมูลครัวเรือน ปี 2564
        </h4>
      </a>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="badge rounded-pill text-bg-light border">
        ทั้งหมด <strong class="ms-1">{{ number_format($surveys->total()) }}</strong> รายการ
      </span>

      <a class="btn btn-sm shadow-sm"
         style="background:#fff;border:1px solid #E2E8F0;color:#334155;border-radius:999px;"
         href="{{ route('household_64') }}">
        ล้างทั้งหมด
      </a>
    </div>
  </div>

  {{-- Card --}}
  <div class="card border-0 shadow-lg rounded-4 bg-white bg-opacity-90 overflow-hidden">
    <div class="card-body pb-0">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div class="fw-semibold" style="color:{{ $teal2 }};">
          <i class="bi bi-table"></i> ตารางครัวเรือน
        </div>

        <form action="{{ route('household_64') }}" method="GET" class="d-flex gap-2 flex-wrap">
          {{-- ส่งค่าตัวกรองอื่นคงไว้ (เวลาใช้ช่อง q) --}}
          <input type="hidden" name="survey_year" value="{{ $survey_year }}">
          <input type="hidden" name="district" value="{{ $district }}">
          <input type="hidden" name="subdistrict" value="{{ $subdistrict }}">
          <input type="hidden" name="village" value="{{ $village }}">
          <input type="hidden" name="house_id" value="{{ $house_id }}">
          <input type="hidden" name="cid" value="{{ $cid }}">
          <input type="hidden" name="has_book" value="{{ $has_book }}">

          <div class="input-group input-group-sm" style="min-width:320px;">
            <span class="input-group-text bg-white border" style="border-top-left-radius:999px;border-bottom-left-radius:999px;">
              <i class="bi bi-search"></i>
            </span>
            <input type="text"
                   name="q"
                   value="{{ $q }}"
                   class="form-control border"
                   placeholder="ค้นหา: รหัสบ้าน / ชื่อเจ้าบ้าน / หมู่บ้าน / เลขบัตร ฯลฯ"
                   style="border-top-right-radius:999px;border-bottom-right-radius:999px;">
          </div>

          <button class="btn btn-sm fw-semibold shadow-sm"
                  style="border-radius:999px;background:{{ $teal }};color:#fff;">
            ค้นหา
          </button>

          @if($q)
            <a class="btn btn-sm shadow-sm"
               style="border-radius:999px;background:#fff;border:1px solid #E2E8F0;color:#334155;"
               href="{{ route('household_64', array_filter([
                 'survey_year'=>$survey_year,'district'=>$district,'subdistrict'=>$subdistrict,'village'=>$village,
                 'house_id'=>$house_id,'cid'=>$cid,'has_book'=>$has_book
               ])) }}">
              ล้างคำค้น
            </a>
          @endif
        </form>
      </div>
    </div>

    <form method="GET" action="{{ route('household_64') }}" id="filterForm">
      {{-- คงค่า q ไว้ --}}
      @if($q !== '') <input type="hidden" name="q" value="{{ $q }}"> @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead style="background:#F1F5F9;">
            <tr>
              <th style="min-width:120px;color:{{ $teal2 }};">รหัสบ้าน</th>
              <th style="min-width:90px;color:{{ $teal2 }};">ปีสำรวจ</th>
              <th style="min-width:110px;color:{{ $teal2 }};">สมุดเขียว</th>
              <th style="min-width:160px;color:{{ $teal2 }};">เลขครัวเรือนเกษตร</th>
              <th style="min-width:110px;color:{{ $teal2 }};">บ้านเลขที่</th>
              <th style="min-width:80px;color:{{ $teal2 }};">หมู่ที่</th>
              <th style="min-width:160px;color:{{ $teal2 }};">ชื่อหมู่บ้าน</th>
              <th style="min-width:140px;color:{{ $teal2 }};">ตำบล</th>
              <th style="min-width:140px;color:{{ $teal2 }};">อำเภอ</th>
              <th style="min-width:120px;color:{{ $teal2 }};">จังหวัด</th>
              <th style="min-width:110px;color:{{ $teal2 }};">ไปรษณีย์</th>
              <th style="min-width:90px;color:{{ $teal2 }};">คำนำหน้า</th>
              <th style="min-width:160px;color:{{ $teal2 }};">ชื่อเจ้าบ้าน</th>
              <th style="min-width:160px;color:{{ $teal2 }};">สกุล</th>
              <th style="min-width:160px;color:{{ $teal2 }};">เลข ปชช.</th>
            </tr>


          </thead>

          <tbody>
            @forelse($surveys as $row)
              @php
                $v = trim((string)$row->survey_Has_agri_book);
                $has = in_array($v, ['1','Y','y','yes','มี'], true);
              @endphp

              <tr>
                <td class="fw-semibold ps-3">{{ $row->house_Id }}</td>
                <td>{{ $row->survey_Year }}</td>

                <td>
                  @if($has)
                    <span class="badge rounded-pill bg-success-subtle text-success border">มี</span>
                  @else
                    <span class="badge rounded-pill bg-danger-subtle text-danger border">ไม่มี</span>
                  @endif
                </td>

                <td>{{ $row->survey_Agri_household_no }}</td>
                <td>{{ $row->house_Number }}</td>
                <td>{{ $row->village_No }}</td>
                <td>{{ $row->village_Name }}</td>

                <td>{{ $row->survey_Subdistrict }}</td>
                <td>{{ $row->survey_District }}</td>
                <td>{{ $row->survey_Province }}</td>
                <td>{{ $row->survey_Postcode }}</td>
                <td>{{ $row->survey_Householder_title }}</td>
                <td>{{ $row->survey_Householder_fname }}</td>
                <td>{{ $row->survey_Householder_lname }}</td>
                <td>{{ $row->survey_Householder_cid }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="15" class="text-center text-muted py-4">ไม่มีข้อมูล</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{-- Pagination --}}
    <div class="py-3">
      <div class="text-center text-muted small mb-2">
        @if($surveys->total() > 0)
          แสดง {{ $surveys->firstItem() }}–{{ $surveys->lastItem() }} จาก {{ number_format($surveys->total()) }} รายการ
        @else
          แสดง 0 รายการ
        @endif
      </div>

      <div class="d-flex justify-content-center">
        {{ $surveys->onEachSide(1)->links('pagination::bootstrap-5') }}
      </div>
    </div>

  </div>
</div>

<script>
  // Enter ในช่อง filter ใต้หัวตาราง = submit
  document.addEventListener('keydown', function(e){
    if(e.key === 'Enter' && e.target.closest('thead')){
      e.preventDefault();
      document.getElementById('filterForm').submit();
    }
  });
</script>

</body>
</html>
