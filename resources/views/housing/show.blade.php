@extends('housing.layout')
@php
  // ✅ กัน Undefined key ใน title
  $houseIdTitle = data_get($house,'house_id')
      ?? data_get($house,'house_Id')
      ?? data_get($house,'houseID')
      ?? '-';
@endphp
@section('title','รายละเอียดบ้าน '.$houseIdTitle)

@section('content')
@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';

  // =========================
  // BASIC (SAFE)
  // =========================
  $houseId     = data_get($house,'house_id') ?? data_get($house,'house_Id') ?? '-';
  $district    = data_get($house,'district') ?? data_get($house,'survey_District') ?? '-';
  $subdistrict = data_get($house,'subdistrict') ?? data_get($house,'survey_Subdistrict') ?? '-';
  $villageNo   = data_get($house,'village_no') ?? data_get($house,'village_No') ?? '-';
  $villageName = data_get($house,'village_name') ?? data_get($house,'village_Name') ?? '';

  // ✅ ปีสำรวจ (fallback ครบ)
  $surveyYear = data_get($house,'survey_Year')
            ?? data_get($house,'survey_year')
            ?? data_get($house,'data_year')
            ?? '-';

  $villageNameText = ($villageName && $villageName !== '-') ? $villageName : '';

  $lat = data_get($house,'lat') ?? data_get($house,'latitude');
  $lng = data_get($house,'lng') ?? data_get($house,'longitude');

  $mapUrl = (!empty($lat) && !empty($lng)) ? "https://www.google.com/maps?q={$lat},{$lng}" : null;

  $infoLeft = [
    ['label'=>'ปีที่สำรวจ', 'val'=>($surveyYear ?: '-')],
    ['label'=>'อำเภอ', 'val'=>$district ?: '-'],
    ['label'=>'ตำบล', 'val'=>$subdistrict ?: '-'],
    ['label'=>'หมู่ที่', 'val'=>$villageNo ?: '-'],
    ['label'=>'ชื่อหมู่บ้าน', 'val'=>($villageNameText ?: '-')],
  ];

  // =========================
  // WATER (6.1–6.3)
  // =========================
  $waterParts = [];
  if (!empty(data_get($house,'water_supply')))  $waterParts[] = 'น้ำจากระบบประปา: '.data_get($house,'water_supply');
  if (!empty(data_get($house,'water_sources'))) $waterParts[] = 'น้ำจากแหล่งน้ำต่าง ๆ: '.data_get($house,'water_sources');
  if (!empty(data_get($house,'buy_water')))     $waterParts[] = 'ซื้อน้ำ: '.data_get($house,'buy_water');
  $waterText = !empty($waterParts) ? implode("\n", $waterParts) : '-';

  // =========================
  // MOBILE (normalize)
  // =========================
  $mobileRaw =
      data_get($house,'mobilephone')
      ?? data_get($house,'mobile_phone')
      ?? data_get($house,'phone')
      ?? data_get($house,'mobile');

  if (in_array((string)$mobileRaw, ['1','มี','yes','true'], true)) {
      $mobileText = 'มีโทรศัพท์มือถือ';
  } elseif (in_array((string)$mobileRaw, ['0','ไม่มี','no','false'], true)) {
      $mobileText = 'ไม่มีโทรศัพท์มือถือ';
  } else {
      $mobileText = '-';
  }

  // =========================
  // ROUTE (road_access + home_route + other121)
  // =========================
  $routeLines = [];

  $road      = trim((string)(data_get($house,'road_access') ?? ''));
  $homeRoute = trim((string)(data_get($house,'home_route') ?? ''));
  $other121  = trim((string)(data_get($house,'other121') ?? ''));

  if ($road !== '')      $routeLines[] = 'ถนน/การเข้าถึง: '.$road;
  if ($homeRoute !== '') $routeLines[] = 'เส้นทางการเดินทางที่อยู่อาศัย: '.$homeRoute;
  if ($other121 !== '')  $routeLines[] = 'หมายเหตุอื่น ๆ: '.$other121;

  $routeText = !empty($routeLines) ? implode("\n", $routeLines) : '-';

  // =========================
  // MAIN ITEMS (cards)
  // =========================
  $items = [
    ['icon'=>'bi-house', 'label'=>'สภาพบ้าน', 'val'=> data_get($house,'house_condition','-')],
    ['icon'=>'bi-lightning-charge', 'label'=>'ไฟฟ้า', 'val'=> data_get($house,'electric','-')],
    ['icon'=>'bi-droplet-half', 'label'=>'น้ำอุปโภคบริโภค', 'val'=>$waterText],
    ['icon'=>'bi-phone', 'label'=>'โทรศัพท์มือถือ', 'val'=>$mobileText],
    ['icon'=>'bi-signpost', 'label'=>'ถนน/การเข้าถึงและการเดินทาง', 'val'=>$routeText],
  ];

  // =========================
  // HELP BUCKETS (โทนราชการ)
  // =========================
  $helpBuckets = [
    [
      'title' => 'ด้านที่อยู่อาศัยและความปลอดภัย',
      'icon'  => 'bi-house-gear',
      'items' => [
        'ตรวจประเมินและซ่อมแซมบ้านที่มีสภาพทรุดโทรม/ไม่มั่นคงแข็งแรง',
        'ปรับสภาพแวดล้อมภายในบ้านให้ปลอดภัยต่อการอยู่อาศัย',
        'จัดหา/สนับสนุนอุปกรณ์จำเป็น (เช่น เตียง ราวจับ ไฟส่องสว่าง)',
      ],
    ],
    [
      'title' => 'ด้านสุขาภิบาลและน้ำสะอาด',
      'icon'  => 'bi-droplet',
      'items' => [
        'ปรับปรุงส้วมและสุขาภิบาลให้ถูกสุขลักษณะ',
        'สนับสนุนการจัดการน้ำใช้/น้ำดื่มให้เพียงพอ',
        'แก้ไขปัญหาน้ำท่วมขัง/ระบบระบายน้ำในครัวเรือน',
      ],
    ],
    [
      'title' => 'ด้านน้ำอุปโภคบริโภค (รายละเอียดจากข้อมูลสำรวจ)',
      'icon'  => 'bi-droplet-half',
      'items' => array_filter([
        'ปีที่สำรวจ: '.($surveyYear ?: '-'),
        'สถานะน้ำ (6.1–6.3): '.($waterText ?: '-'),
        'พิจารณาปรับปรุง/ซ่อมระบบประปาและจุดจ่ายน้ำให้เพียงพอ',
        'จัดหาแหล่งน้ำสำรอง (ถังเก็บน้ำ/น้ำฝน/บ่อ ฯลฯ)',
      ]),
    ],
    [
      'title' => 'ด้านไฟฟ้าและความเสี่ยง',
      'icon'  => 'bi-lightning-charge',
      'items' => array_filter([
        'สถานะไฟฟ้า: '.(data_get($house,'electric') ?? '-'),
        'พลังงานทางเลือก: '.(data_get($house,'alternative_energy') ?? '-'),
        'โทรศัพท์มือถือ: '.$mobileText,
        'ตรวจสอบความเสี่ยงระบบไฟฟ้า (ต่อพ่วง/เสี่ยงอัคคีภัย) และกำกับให้ใช้ไฟอย่างปลอดภัย',
      ]),
    ],
    [
      'title' => 'ด้านการเข้าถึงบริการและสิ่งแวดล้อม',
      'icon'  => 'bi-signpost-2',
      'items' => array_filter([
        'สถานะการเข้าถึงและการเดินทาง: '.($routeText ?: '-'),
        'พิจารณาการเข้าถึงเส้นทางฉุกเฉิน/การส่งต่อบริการ',
        'พิจารณามาตรการจัดการสิ่งแวดล้อมและการประสานหน่วยงานพื้นที่',
      ]),
    ],
  ];
@endphp

<style>
  :root{
    --teal: {{ $teal }};
    --teal2: {{ $teal2 }};
  }
  .pp-card{ border-radius: 1rem; }
  .pp-soft{
    background: linear-gradient(135deg, rgba(11,127,111,.12), rgba(11,91,107,.06));
  }
  .pp-icon{
    width: 54px; height: 54px;
    background: rgba(11,127,111,.12);
    color: var(--teal);
  }
  .pp-mini{ font-size: 13px; }
  .pp-label{ font-size: 12px; color: #6c757d; }
  .pp-tile{
    background: linear-gradient(135deg,#ffffff,rgba(11,127,111,.05));
  }
  .pp-kv{
    border-left: 4px solid rgba(11,127,111,.25);
    background: #fff;
  }
</style>

{{-- HEADER --}}
<div class="card border-0 shadow-sm pp-card mb-3 overflow-hidden">
  <div class="card-body d-flex align-items-start justify-content-between flex-wrap gap-3 pp-soft">
    <div class="d-flex align-items-start gap-3">
      <div class="pp-icon rounded-4 d-flex align-items-center justify-content-center">
        <i class="bi bi-house-door-fill fs-4"></i>
      </div>

      <div class="lh-sm">
        <div class="fw-bold" style="color:var(--teal2);font-size:1.15rem;">
          รายละเอียดครัวเรือน/ที่อยู่อาศัย : {{ $houseId }}
        </div>

        <div class="text-secondary pp-mini">
          ปี {{ $surveyYear ?: '-' }} ·
          {{ $district ?: '-' }} / {{ $subdistrict ?: '-' }} / หมู่ {{ $villageNo ?: '-' }}
          @if($villageNameText) · {{ $villageNameText }} @endif
        </div>

        <div class="d-flex flex-wrap gap-2 mt-2">
          <span class="badge rounded-pill bg-light text-dark border">
            <i class="bi bi-calendar2-week me-1"></i> ปีสำรวจ: <b class="ms-1">{{ $surveyYear ?: '-' }}</b>
          </span>

          <span class="badge rounded-pill bg-light text-dark border">
            <i class="bi bi-clipboard-data me-1"></i> คะแนนความเร่งด่วน: <b class="ms-1">{{ $score ?? 0 }}</b>
          </span>

          <span class="badge rounded-pill {{ $badge ?? 'bg-secondary' }}">
            <i class="bi bi-flag-fill me-1"></i> ระดับ: {{ $level ?? 'ไม่ระบุ' }}
          </span>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      @if($mapUrl)
        <a class="btn btn-sm btn-success rounded-pill px-3" target="_blank" href="{{ $mapUrl }}">
          <i class="bi bi-geo-alt-fill me-1"></i> เปิดแผนที่
        </a>
      @endif
      <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ url()->previous() }}">
        <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
      </a>
    </div>
  </div>
</div>

{{-- ✅ SUCCESS FLASH (บันทึกเรียบร้อยแล้ว) --}}
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show small shadow-sm mb-3"
       style="border-radius:12px;">
    <i class="bi bi-check-circle-fill me-1"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="row g-3">
  {{-- LEFT --}}
  <div class="col-lg-8">
    {{-- BASIC INFO --}}
    <div class="card border-0 shadow-sm pp-card mb-3">
      <div class="card-body">
        <div class="fw-semibold mb-2" style="color:var(--teal2);">
          <i class="bi bi-info-circle me-1"></i> ข้อมูลพื้นที่/บ้าน (สรุป)
        </div>

        <div class="row g-2">
          @foreach($infoLeft as $it)
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-4">
                <div class="pp-label">{{ $it['label'] }}</div>
                <div class="fw-semibold">{{ $it['val'] }}</div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-3 p-3 rounded-4 border pp-kv">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="fw-semibold" style="color:var(--teal2);">
                <i class="bi bi-pin-map-fill me-1 text-success"></i> พิกัดที่ตั้ง (สำหรับการลงพื้นที่)
              </div>
              <div class="text-secondary pp-mini">
                Lat: {{ $lat ?? '-' }} / Lng: {{ $lng ?? '-' }}
              </div>
            </div>

            @if($mapUrl)
              <a class="btn btn-outline-success btn-sm rounded-pill px-3" target="_blank" href="{{ $mapUrl }}">
                เปิดใน Google Maps
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- PHYSICAL CARDS --}}
    <div class="card border-0 shadow-sm pp-card">
      <div class="card-body">
        <div class="fw-semibold mb-2" style="color:var(--teal2);">
          <i class="bi bi-house-check me-1"></i> สภาพบ้านและสาธารณูปโภค
        </div>

        <div class="row g-2">
          @foreach($items as $it)
            @php
              $val = $it['val'] ?? '-';
              $valClass = ($it['label'] === 'สภาพบ้าน') ? 'text-dark' : '';
              $displayVal = ($val === '-' || $val === null || $val === '') ? '-' : $val;
            @endphp

            <div class="col-md-6">
              <div class="p-3 rounded-4 border h-100 pp-tile">
                <div class="d-flex align-items-start gap-2">
                  <div class="rounded-4 d-flex align-items-center justify-content-center"
                       style="width:40px;height:40px;background:rgba(11,127,111,.12);color:var(--teal);">
                    <i class="bi {{ $it['icon'] }}"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="pp-label">{{ $it['label'] }}</div>
                    <div class="fw-semibold {{ $valClass }} pp-mini" style="white-space:normal; line-height:1.55;">
                      {!! nl2br(e($displayVal)) !!}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-3 text-secondary" style="font-size:12px;">
          <i class="bi bi-shield-check me-1"></i>
          ข้อมูลชุดนี้ใช้เพื่อประกอบการ “คัดกรองและจัดลำดับความเร่งด่วน” สำหรับการช่วยเหลือเชิงพื้นที่
        </div>
      </div>
    </div>
  </div>

  {{-- RIGHT --}}
  <div class="col-lg-4">
    {{-- HELP --}}
    <div class="card border-0 shadow-sm pp-card mb-3">
      <div class="card-body">
        <div class="fw-semibold mb-2" style="color:var(--teal2);">
          <i class="bi bi-hand-heart me-1 text-success"></i> แนวทางการช่วยเหลือ (ข้อเสนอเชิงมาตรการ)
        </div>
        <div class="text-secondary pp-mini">
          ใช้เพื่อประกอบการนำเสนอหน่วยงานจังหวัด / อบจ. / อปท. สำหรับกำหนดมาตรการช่วยเหลือที่เหมาะสม
        </div>

        <div class="mt-3 d-grid gap-2">
          @foreach($helpBuckets as $b)
            <div class="p-3 rounded-4 border bg-white">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi {{ $b['icon'] }} text-success"></i>
                <div class="fw-semibold">{{ $b['title'] }}</div>
              </div>
              <ul class="mb-0 ps-3 pp-mini">
                @foreach($b['items'] as $x)
                  <li class="text-secondary" style="line-height:1.5;">{!! nl2br(e($x)) !!}</li>
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- STATUS (placeholder) --}}
    <div class="card border-0 shadow-sm pp-card">
      <div class="card-body">
        <div class="fw-semibold mb-2" style="color:var(--teal2);">
          <i class="bi bi-clipboard-check me-1"></i> สถานะการดำเนินการ
        </div>

        <div class="text-secondary pp-mini">
          หน้านี้ยังไม่ได้เชื่อม “ตารางบันทึกการช่วยเหลือ” (เช่น วันที่ดำเนินการ/หน่วยงาน/งบประมาณ/ผลลัพธ์)
          หากต้องการ สามารถเพิ่มโมดูลบันทึกผลการช่วยเหลือได้ทันที
        </div>

        <div class="d-grid gap-2 mt-3">
          <a href="{{ route('help.create', ['houseId'=>$houseId, 'survey_year'=>$surveyYear]) }}" 
             class="btn btn-success rounded-pill text-white"
             style="background:#0B7F6F;border-color:#0B7F6F;">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มบันทึกการช่วยเหลือ
          </a>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
