<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลครัวเรือน ปี 2564</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body style="background:#F2F6F8; padding:20px; font-family: 'Segoe UI', sans-serif;">

    <div class="container-fluid">

        {{-- หัวเรื่อง --}}
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
            <div style="
                width:48px; height:48px; border-radius:14px;
                background:linear-gradient(135deg,#0F9BD8,#0B7F6F);
                display:flex; justify-content:center; align-items:center;
                color:#fff; font-size:20px;">
                <i class="bi bi-house-door-fill"></i>
            </div>

            <div>
                <div style="font-size:22px; font-weight:600; color:#02252B;">
                    ข้อมูลครัวเรือน ปี 2564
                </div>
                <div style="font-size:13px; color:#6c757d;">
                    Household Survey 2564 — ข้อมูลเพื่อการวิเคราะห์จังหวัดพัทลุง
                </div>
            </div>
        </div>

        {{-- การ์ด --}}
        <div style="
            background:#fff;
            border-radius:16px;
            padding:0;
            box-shadow:0 10px 25px rgba(0,0,0,0.06);
            overflow:hidden;
        ">

            {{-- ส่วนค้นหา --}}
            <div style="
                padding:16px 20px;
                border-bottom:1px solid #e8edf2;
                background:linear-gradient(90deg,rgba(15,155,216,0.08),rgba(11,127,111,0.05));
            ">
                <form action="{{ route('household_64') }}" method="GET" style="display:flex; gap:8px;">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="ค้นหา: รหัสบ้าน / ชื่อเจ้าบ้าน / หมู่บ้าน / เลขบัตร ฯลฯ"
                           class="form-control form-control-sm"
                           style="
                                border-radius:50px;
                                padding-left:14px;
                                height:36px;
                                font-size:13px;
                                border:1px solid #c8d4dd;
                           ">

                    <button type="submit"
                            class="btn btn-sm"
                            style="
                                background:#0B7F6F;
                                color:#fff;
                                border-radius:50px;
                                padding:6px 16px;
                            ">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>

                    @if(request('q'))
                    <a href="{{ route('household_64') }}"
                       class="btn btn-sm"
                       style="
                            border-radius:50px;
                            padding:6px 14px;
                            border:1px solid #b9c3c9;
                            background:#fff;
                            font-size:13px;
                       ">
                        ล้างการค้นหา
                    </a>
                    @endif
                </form>

                {{-- จำนวนรายการ --}}
                <div style="margin-top:6px; text-align:right;">
                    <span style="font-size:12px; color:#6c757d;">จำนวนทั้งหมด</span>
                    <span style="font-size:15px; color:#0B7F6F; font-weight:600;">
                        {{ number_format($surveys->total()) }} รายการ
                    </span>
                </div>
            </div>

            {{-- ตาราง --}}
            <div style="max-height:70vh; overflow:auto;">

                <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                    <thead style="position:sticky; top:0; z-index:10; background:linear-gradient(90deg,#0F9BD8,#0B7F6F); color:#fff;">
                        <tr>
                            <th>รหัสบ้าน</th>
                            <th>ปีสำรวจ</th>
                            <th>ครั้งที่สำรวจ</th>
                            <th>สมุดเขียว</th>
                            <th>เลขครัวเรือนเกษตร</th>
                            <th>บ้านเลขที่</th>
                            <th>หมู่ที่</th>
                            <th>ชื่อหมู่บ้าน</th>
                            <th>ซอย</th>
                            <th>ถนน</th>
                            <th>ตำบล (ID)</th>
                            <th>ตำบล</th>
                            <th>อำเภอ (ID)</th>
                            <th>อำเภอ</th>
                            <th>จังหวัด (ID)</th>
                            <th>จังหวัด</th>
                            <th>ไปรษณีย์</th>
                            <th>คำนำหน้า</th>
                            <th>ชื่อเจ้าบ้าน</th>
                            <th>สกุล</th>
                            <th>เลข ปชช.</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($surveys as $row)
                        <tr>
                            <td>{{ $row->house_Id }}</td>
                            <td>{{ $row->survey_Year }}</td>
                            <td>{{ $row->survey_No }}</td>
                            <td>
                                @php $v = trim($row->survey_Has_agri_book); @endphp
                                @if(in_array($v, ['1','Y','y','yes','มี']))
                                    <span style="background:#D1F2EB; color:#0B7F6F; padding:2px 8px; border-radius:50px; font-size:11px;">มี</span>
                                @else
                                    <span style="background:#FDE2E1; color:#C0392B; padding:2px 8px; border-radius:50px; font-size:11px;">ไม่มี</span>
                                @endif
                            </td>
                            <td>{{ $row->survey_Agri_household_no }}</td>
                            <td>{{ $row->house_Number }}</td>
                            <td>{{ $row->village_No }}</td>
                            <td>{{ $row->village_Name }}</td>
                            <td>{{ $row->survey_Soi }}</td>
                            <td>{{ $row->survey_Road }}</td>
                            <td>{{ $row->id_Subdistrict }}</td>
                            <td>{{ $row->survey_Subdistrict }}</td>
                            <td>{{ $row->id_District }}</td>
                            <td>{{ $row->survey_District }}</td>
                            <td>{{ $row->id_Province }}</td>
                            <td>{{ $row->survey_Province }}</td>
                            <td>{{ $row->survey_Postcode }}</td>
                            <td>{{ $row->survey_Householder_title }}</td>
                            <td>{{ $row->survey_Householder_fname }}</td>
                            <td>{{ $row->survey_Householder_lname }}</td>
                            <td>{{ $row->survey_Householder_cid }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="21" style="text-align:center; color:#888; padding:20px;">
                                ไม่มีข้อมูล
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

           
        </div>
    </div>

</body>
</html>
