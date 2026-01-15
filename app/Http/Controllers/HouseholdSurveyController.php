<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HouseholdSurvey;

class HouseholdSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = HouseholdSurvey::query();

        // ถ้ามีคำค้นหา (q) จากฟอร์ม
        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($sub) use ($q) {
                $sub->where('house_Id', 'LIKE', "%{$q}%")
                    ->orWhere('house_Number', 'LIKE', "%{$q}%")
                    ->orWhere('village_Name', 'LIKE', "%{$q}%")
                    ->orWhere('survey_Subdistrict', 'LIKE', "%{$q}%")
                    ->orWhere('survey_District', 'LIKE', "%{$q}%")
                    ->orWhere('survey_Province', 'LIKE', "%{$q}%")
                    ->orWhere('survey_Householder_fname', 'LIKE', "%{$q}%")
                    ->orWhere('survey_Householder_lname', 'LIKE', "%{$q}%")
                    ->orWhere('survey_Householder_cid', 'LIKE', "%{$q}%");
            });
        }

        // ดึงข้อมูลเรียงตามปีที่สำรวจ จากมากไปน้อย และแบ่งหน้า
        $surveys = $query->orderBy('survey_Year', 'desc')->paginate(20);

        // ส่งตัวแปร $surveys ไปให้หน้า household_64.blade.php
        return view('household_64', compact('surveys'));
    }
}
