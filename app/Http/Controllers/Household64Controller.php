<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HouseholdSurvey2564;

class Household64Controller extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->get('q', ''));

        $surveys = HouseholdSurvey2564::query()
            // ✅ ดึงเฉพาะคอลัมน์ที่ใช้ (ช่วยให้เร็วขึ้น)
            ->select([
                'house_Id',
                'survey_Householder_fname',
                'survey_Householder_lname',
                'survey_Householder_cid',
                'village_Name',
                'survey_Subdistrict',
                'survey_District',
            ])

            // ✅ ค้นหาแบบครอบ OR ทั้งหมด (logic ถูก + ต่อยอดง่าย)
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('house_Id', 'like', "%{$keyword}%")
                       ->orWhere('survey_Householder_fname', 'like', "%{$keyword}%")
                       ->orWhere('survey_Householder_lname', 'like', "%{$keyword}%")
                       ->orWhere('survey_Householder_cid', 'like', "%{$keyword}%")
                       ->orWhere('village_Name', 'like', "%{$keyword}%")
                       ->orWhere('survey_Subdistrict', 'like', "%{$keyword}%")
                       ->orWhere('survey_District', 'like', "%{$keyword}%");
                });
            })

            // ✅ เรียงบ้าน
            ->orderBy('house_Id')

            // ✅ แบ่งหน้า (สำคัญมาก)
            ->paginate(25)

            // ✅ จำ keyword เวลาเปลี่ยนหน้า
            ->appends($request->query());

        return view('household_64', compact('surveys', 'keyword'));
    }
}
