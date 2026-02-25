<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>@yield('title','ระบบสภาพบ้าน (ทุนกายภาพ)')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  @yield('head')
  <style>
    body{ font-family:"Prompt",system-ui,-apple-system,sans-serif; background:#f6f8fb; }
    .topbar{ background:linear-gradient(135deg,#0B7F6F,#0B5B6B); color:#fff; border-radius:22px; padding:16px 18px; }
    .card{ border:0; border-radius:18px; }
    .kpi .icon{ width:44px;height:44px;border-radius:14px; display:flex;align-items:center;justify-content:center; background:rgba(11,127,111,.10); color:#0B7F6F; }
    .nav-pill{ border-radius:999px; }
    .table thead th{ font-size:13px; color:#64748b; }
  </style>
</head>
<body>
@include('layouts.topbar')
  <div class="container py-4">
    <div class="topbar mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="fw-semibold">ระบบติดตามสภาพบ้านและสาธารณูปโภค </div>
        <div class="opacity-75" style="font-size:13px;">สำหรับหน่วยงานจังหวัด / อบจ.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        
        <a class="btn btn-light btn-sm nav-pill" href="{{ route('housing.map') }}"><i class="bi bi-geo-alt me-1"></i>แผนที่</a>
        
      </div>
    </div>

    @yield('content')
  </div>
</body>
</html>
