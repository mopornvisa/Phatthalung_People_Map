<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HealthExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Collection $rows) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'ปีที่สำรวจ',
            'รหัสบ้าน',
            'ชื่อ',
            'สกุล',
            'อายุ(ปี)',
            'เพศ',
            'สุขภาพ',
            'อำเภอ',
            'ตำบล',
            'บัตรประชาชน',
            'เบอร์โทร',
            'ละติจูด',
            'ลองจิจูด',
            'บ้านเลขที่',
            'หมู่ที่',
            'ชื่อหมู่บ้าน',
            'รหัสไปรษณีย์',
        ];
    }

    public function map($r): array
    {
        return [
            $r->survey_Year ?? '',
            $r->house_Id ?? '',
            $r->human_Member_fname ?? '',
            $r->human_Member_lname ?? '',
            $r->human_Age_y ?? '',
            $r->human_Sex ?? '',
            $r->human_Health ?? '',
            $r->survey_District ?? '',
            $r->survey_Subdistrict ?? '',
            $r->human_Member_cid ?? '',
            $r->survey_Informer_phone ?? '',
            $r->latitude ?? '',
            $r->longitude ?? '',
            $r->house_Number ?? '',
            $r->village_No ?? '',
            $r->village_Name ?? '',
            $r->survey_Postcode ?? '',
        ];
    }
}
