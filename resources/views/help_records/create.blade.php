@extends('housing.layout')
@section('title','เพิ่มบันทึกการช่วยเหลือ')

@section('content')
@php
  $teal  = '#0B7F6F';
  $teal2 = '#0B5B6B';
@endphp

<div class="container py-3">
  <div class="card border-0 shadow-sm" style="border-radius:18px;">
    <div class="card-body">

      {{-- HEADER --}}
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <div class="fw-bold" style="color:{{ $teal2 }};font-size:1.1rem;">
            <i class="bi bi-plus-circle me-1 text-success"></i>
            เพิ่มบันทึกการช่วยเหลือ
          </div>

          <div class="text-muted" style="font-size:13px;">
            รหัสบ้าน: <b>{{ $houseId }}</b>
            @if(!empty($surveyYear))
              · ปีสำรวจ: <b>{{ $surveyYear }}</b>
            @endif
          </div>
        </div>

        <a href="{{ route('housing.show',$houseId) }}"
           class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
        </a>
      </div>

      {{-- ERROR --}}
      @if($errors->any())
        <div class="alert alert-danger small">
          กรุณาตรวจสอบข้อมูลให้ครบถ้วน
        </div>
      @endif

      {{-- FORM --}}
      <form method="POST" action="{{ route('help.store',$houseId) }}">
        @csrf

        {{-- ✅ ปีสำรวจ ส่ง hidden ไปเสมอ (ห้ามซ้ำชื่อกับปีช่วยเหลือ) --}}
        <input type="hidden" name="survey_year" value="{{ $surveyYear }}">

        <div class="row g-3">

          {{-- ✅ ปีที่ช่วยเหลือ --}}
          <div class="col-md-3">
            <label class="form-label small text-muted">ปีที่ช่วยเหลือ</label>
            @php $thaiYearNow = date('Y') + 543; @endphp

            <input type="number"
                  name="help_year"
                  value="{{ old('help_year', $thaiYearNow) }}"
                  class="form-control form-control-sm"
                  placeholder="2568">
          </div>

          {{-- วันที่ --}}
          <div class="col-md-3">
            <label class="form-label small text-muted">วันที่ดำเนินการ</label>
            <input type="date"
                   name="action_date"
                   value="{{ old('action_date') }}"
                   class="form-control form-control-sm">
          </div>

          {{-- หน่วยงาน --}}
          <div class="col-md-6">
            <label class="form-label small text-muted">หน่วยงานที่ดำเนินการ</label>
            <input type="text"
                   name="agency"
                   value="{{ old('agency') }}"
                   class="form-control form-control-sm"
                   placeholder="อบจ.พัทลุง / อปท. / พมจ.">
          </div>

          {{-- ประเภท --}}
          <div class="col-md-6">
            <label class="form-label small text-muted">ประเภทการช่วยเหลือ</label>
            <input type="text"
                   name="action_type"
                   value="{{ old('action_type') }}"
                   class="form-control form-control-sm"
                   placeholder="ซ่อมแซมบ้าน / ปรับปรุงส้วม / น้ำสะอาด">
          </div>

          {{-- งบ --}}
          <div class="col-md-3">
            <label class="form-label small text-muted">งบประมาณ (บาท)</label>
            <input type="number"
                   step="0.01"
                   name="budget"
                   value="{{ old('budget') }}"
                   class="form-control form-control-sm">
          </div>

          {{-- สถานะ --}}
          <div class="col-md-3">
            <label class="form-label small text-muted">สถานะ</label>
            <select name="status" class="form-select form-select-sm">
              @php $st = old('status','ดำเนินการ'); @endphp
              <option value="ดำเนินการ" @selected($st==='ดำเนินการ')>ดำเนินการ</option>
              <option value="รอดำเนินการ" @selected($st==='รอดำเนินการ')>รอดำเนินการ</option>
              <option value="เสร็จสิ้น" @selected($st==='เสร็จสิ้น')>เสร็จสิ้น</option>
              <option value="ติดตามผล" @selected($st==='ติดตามผล')>ติดตามผล</option>
            </select>
          </div>

          {{-- นัดติดตาม --}}
          <div class="col-md-3">
            <label class="form-label small text-muted">นัดติดตาม</label>
            <input type="date"
                   name="next_followup"
                   value="{{ old('next_followup') }}"
                   class="form-control form-control-sm">
          </div>

          {{-- รายละเอียด --}}
          <div class="col-12">
            <label class="form-label small text-muted">รายละเอียด</label>
            <textarea name="detail" rows="4"
              class="form-control form-control-sm">{{ old('detail') }}</textarea>
          </div>

          {{-- ผลลัพธ์ --}}
          <div class="col-12">
            <label class="form-label small text-muted">ผลลัพธ์/หมายเหตุ</label>
            <textarea name="result" rows="3"
              class="form-control form-control-sm">{{ old('result') }}</textarea>
          </div>

        </div>

        {{-- BUTTON --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('housing.show',$houseId) }}"
             class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            ยกเลิก
          </a>

          <button class="btn btn-sm text-white rounded-pill px-4"
                  style="background:{{ $teal }};">
            <i class="bi bi-save me-1"></i> บันทึก
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
