{{-- resources/views/household_64.blade.php --}}
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />

  @php
    $survey_year = $survey_year ?? request('survey_year','');
  @endphp

  <title>ข้อมูลครัวเรือน {{ $survey_year ? "ปี {$survey_year}" : "ทุกปี" }}</title>

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
      style="font-family:'Prompt',system-ui,sans-serif;
             min-height:100vh;
             background:
               radial-gradient(1200px 600px at 10% 0%, rgba(11,127,111,.10), transparent 55%),
               radial-gradient(1000px 600px at 90% 0%, rgba(15,155,216,.12), transparent 55%),
               linear-gradient(135deg,#CFEFF3 0%,#DFF7EF 50%,#F0F8FB 100%);">


@include('layouts.topbar')

@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  $q = request('q','');
  $district = request('district','');
  $subdistrict = request('subdistrict','');
  $village = request('village','');
  $house_id = request('house_id','');
  $cid = request('cid','');
  $has_book = request('has_book','');
  $agri_no = request('agri_no','');
  $house_no = request('house_no','');
  $village_no = request('village_no','');
  $postcode = request('postcode','');
  $title = request('title','');
  $fname = request('fname','');
  $lname = request('lname','');

  $yearList = [2564,2565,2566,2567,2568];
@endphp



{{-- ✅ หน้าเดียว --}}
<div class="container my-4">




  {{-- ================= HEADER ================= --}}
  <div class="d-flex justify-content-between align-items-center mb-2">

    <div class="fw-bold fs-5" style="color:{{ $teal2 }}">
      <i class="bi bi-house-door-fill me-1"></i>
      ข้อมูลครัวเรือน {{ $survey_year ? "ปี {$survey_year}" : "ทุกปี" }}
    </div>

    <span style="display:inline-flex;align-items:center;gap:.4rem;
                 padding:.4rem .8rem;border-radius:999px;
                 border:1px solid {{ $teal }};
                 background:#E6F4F2;
                 color:{{ $teal2 }};
                 font-size:13px;">
      ทั้งหมด <strong>{{ number_format($surveys->total()) }}</strong> รายการ
    </span>

  </div>



  {{-- ================= CARD ================= --}}
  <div
style="background:rgba(255,255,255,.92);
            border:1px solid rgba(148,163,184,.35);
            box-shadow:0 18px 55px rgba(2,6,23,.10);
            border-radius:18px;">

           



    {{-- ===== Search bar ===== --}}
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">

      <div class="fw-semibold" style="color:{{ $teal2 }}">
        <i class="bi bi-table me-1"></i> ตารางครัวเรือน
      </div>

      <form method="GET" action="{{ route('household_64') }}" class="d-flex">

        <div class="input-group input-group-sm" style="width:380px">

          <span class="input-group-text bg-white">
            <i class="bi bi-search text-secondary"></i>
          </span>

          <input name="q" value="{{ $q }}" class="form-control"
                 placeholder="ค้นหา...">

          {{-- ✅ สีเขียวเดิม --}}
          <button class="btn fw-semibold"
                  style="background:{{ $teal }};
                         color:#fff;
                         border-color:{{ $teal }};">
            ค้นหา
          </button>

        </div>
      </form>

    </div>



    {{-- ================= TABLE (เลื่อนเฉพาะกรอบ) ================= --}}
    <form method="GET" action="{{ route('household_64') }}" id="filterForm">

      {{-- จังหวัดล็อก --}}
      <input type="hidden" name="province" value="พัทลุง">

      <div class="table-responsive" style="border-top:1px solid #E2E8F0;">


        <table class="table table-bordered table-hover align-middle mb-0 text-nowrap">

          <thead class="table-light">

          <tr>
            <th>รหัสบ้าน</th>
            <th>ปี</th>
            <th>สมุดเขียว</th>
            <th>เลขเกษตร</th>
            <th>บ้านเลขที่</th>
            <th>หมู่</th>
            <th>หมู่บ้าน</th>
            <th>ตำบล</th>
            <th>อำเภอ</th>
            <th>จังหวัด</th>
            <th>ไปรษณีย์</th>
            <th>คำนำหน้า</th>
            <th>ชื่อ</th>
            <th>สกุล</th>
            <th>เลขบัตร</th>
          </tr>

          {{-- FILTER ROW --}}
          <tr>

            <th><input name="house_id" value="{{ $house_id }}" class="form-control form-control-sm"></th>

            <th>
              <select name="survey_year" class="form-select form-select-sm">
                <option value="">ทั้งหมด</option>
                @foreach($yearList as $y)
                  <option value="{{ $y }}" @selected($survey_year==$y)>{{ $y }}</option>
                @endforeach
              </select>
            </th>

            <th>
              <select name="has_book" class="form-select form-select-sm">
                <option value="">ทั้งหมด</option>
                <option value="1" @selected($has_book==='1')>มี</option>
                <option value="0" @selected($has_book==='0')>ไม่มี</option>
              </select>
            </th>

            <th><input name="agri_no" value="{{ $agri_no }}" class="form-control form-control-sm"></th>
            <th><input name="house_no" value="{{ $house_no }}" class="form-control form-control-sm"></th>
            <th><input name="village_no" value="{{ $village_no }}" class="form-control form-control-sm"></th>
            <th><input name="village" value="{{ $village }}" class="form-control form-control-sm"></th>
            <th><input name="subdistrict" value="{{ $subdistrict }}" class="form-control form-control-sm"></th>
            <th><input name="district" value="{{ $district }}" class="form-control form-control-sm"></th>

            {{-- จังหวัดล็อก --}}
            <th><input value="พัทลุง" class="form-control form-control-sm" readonly></th>

            <th><input name="postcode" value="{{ $postcode }}" class="form-control form-control-sm"></th>
            <th><input name="title" value="{{ $title }}" class="form-control form-control-sm"></th>
            <th><input name="fname" value="{{ $fname }}" class="form-control form-control-sm"></th>
            <th><input name="lname" value="{{ $lname }}" class="form-control form-control-sm"></th>
            <th><input name="cid" value="{{ $cid }}" class="form-control form-control-sm"></th>

          </tr>
          </thead>



          <tbody>
          @forelse($surveys as $row)
            @php
              $has = in_array(strtolower(trim($row->survey_Has_agri_book)), ['1','y','yes','มี'], true);
            @endphp

            <tr>
              <td class="fw-semibold">{{ $row->house_Id }}</td>
              <td>{{ $row->survey_Year }}</td>

              <td>
                @if($has)
                  <span class="badge bg-success">มี</span>
                @else
                  <span class="badge bg-danger">ไม่มี</span>
                @endif
              </td>

              <td>{{ $row->survey_Agri_household_no }}</td>
              <td>{{ $row->house_Number }}</td>
              <td>{{ $row->village_No }}</td>
              <td>{{ $row->village_Name }}</td>
              <td>{{ $row->survey_Subdistrict }}</td>
              <td>{{ $row->survey_District }}</td>
              <td>พัทลุง</td>
              <td>{{ $row->survey_Postcode }}</td>
              <td>{{ $row->survey_Householder_title }}</td>
              <td>{{ $row->survey_Householder_fname }}</td>
              <td>{{ $row->survey_Householder_lname }}</td>
              <td class="font-monospace">{{ $row->survey_Householder_cid }}</td>
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
    <div class="p-2 border-top text-center bg-white">
      {{ $surveys->links('pagination::bootstrap-5') }}
    </div>

  </div>
</div>



<script>
document.addEventListener('keydown', function(e){
  if(e.key==='Enter' && e.target.closest('thead')){
    e.preventDefault();
    document.getElementById('filterForm').submit();
  }
});
</script>

</body>
</html>
