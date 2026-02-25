@extends('housing.layout')
@section('title','แผนที่สภาพบ้าน')

@section('head')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>#map{ height: 72vh; border-radius:18px; }</style>
@endsection

@section('content')
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="fw-semibold"><i class="bi bi-map me-1"></i> แผนที่จุดบ้าน</div>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.blade') }}">ไปหน้า</a>
      </div>
      <div id="map"></div>
    </div>
  </div>

  <script>
    const pins = @json($pins);

    const map = L.map('map').setView([7.55, 100.0], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18, attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    function colorByLevel(level){
      if(level==='ด่วนมาก') return 'red';
      if(level==='ด่วน') return 'orange';
      if(level==='เฝ้าระวัง') return 'blue';
      return 'green';
    }

    pins.forEach(p => {
      if(!p.lat || !p.lng) return;

      const c = colorByLevel(p.level);
      const marker = L.circleMarker([p.lat, p.lng], {
        radius: 8, weight: 2, color: c, fillColor: c, fillOpacity: 0.7
      }).addTo(map);

      const html = `
        <div style="min-width:220px">
          <div style="font-weight:600">${p.house_id}</div>
          <div style="font-size:12px;color:#64748b">${p.district} / ${p.subdistrict} / หมู่ ${p.village_no}</div>
          <div style="margin-top:6px">
            <span class="badge ${p.badge}">${p.level}</span>
            <span style="margin-left:6px;font-weight:600">คะแนน ${p.score}</span>
          </div>
          <div style="margin-top:8px">
            <a class="btn btn-sm btn-primary" href="/housing/house/${p.house_id}">รายละเอียด</a>
          </div>
        </div>
      `;
      marker.bindPopup(html);
    });
  </script>
@endsection
