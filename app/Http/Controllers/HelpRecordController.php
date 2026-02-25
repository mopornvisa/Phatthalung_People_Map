<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HelpRecord;

class HelpRecordController extends Controller
{
    public function create(Request $request, string $houseId)
    {
        $surveyYear = $request->query('survey_year');

        return view('help_records.create', [
            'houseId'    => $houseId,
            'surveyYear' => $surveyYear,
        ]);
    }

    public function store(Request $request, string $houseId)
    {
        $data = $request->validate([
            // ปีสำรวจ (จาก hidden / query)
            'survey_year'   => ['nullable','integer'],

            // ปีที่ช่วยเหลือ (จาก input)
            'help_year'     => ['nullable','integer'],

            'action_date'   => ['nullable','date'],
            'agency'        => ['nullable','string','max:255'],
            'action_type'   => ['nullable','string','max:255'],
            'budget'        => ['nullable','numeric','min:0'],
            'detail'        => ['nullable','string'],
            'status'        => ['required','string','max:50'],
            'next_followup' => ['nullable','date'],
            'result'        => ['nullable','string'],
        ]);

        // ให้ตรง DB: house_Id
        $data['house_Id'] = $houseId;

        // fallback ปีสำรวจ (กันกรณี hidden ไม่มา)
        if (empty($data['survey_year'])) {
            $data['survey_year'] = $request->input('survey_year') ?: $request->query('survey_year');
        }

        // fallback ปีช่วยเหลือ (กันกรณีไม่กรอก)
        if (empty($data['help_year'])) {
            $data['help_year'] = (int) date('Y') + 543;
        }

        HelpRecord::create($data);

        return redirect()
            ->route('housing.show', $houseId)
            ->with('success', 'บันทึกการช่วยเหลือเรียบร้อยแล้ว');
    }
}
