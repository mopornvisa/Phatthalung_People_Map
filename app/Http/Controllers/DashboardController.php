<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HouseholdSurvey2564;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ======================
        // รับค่าจาก query
        // ======================
        $district    = $request->get('district');
        $subdistrict = $request->get('subdistrict');
        $view        = $request->get('view', 'district');

        // ✅ เพศ
        $human_Sex = (string) $request->get('human_Sex', '');
        if (!in_array($human_Sex, ['', 'ชาย', 'หญิง'], true)) {
            $human_Sex = '';
        }

        // ✅ ช่วงอายุ (human_Age_y)
        $age_range = (string) $request->get('age_range', ''); // '' = ทั้งหมด
        $AGE_RANGES = [
            ''      => 'อายุ: ทั้งหมด',
            '0-15'  => '0–15 ปี',
            '16-28' => '16–28 ปี',
            '29-44' => '29–44 ปี',
            '45-59' => '45–59 ปี',
            '60-78' => '60–78 ปี',
            '79-97' => '79–97 ปี',
            '98+'   => '98 ปีขึ้นไป',
        ];
        if (!array_key_exists($age_range, $AGE_RANGES)) {
            $age_range = '';
        }

        // ✅ helper ใส่ช่วงอายุให้ query
        $applyAgeRange = function ($q) use ($age_range) {
            // กัน null (ถ้าคุณอยากนับ null ด้วย ให้เอาบรรทัดนี้ออก)
            $q->whereNotNull('h.human_Age_y');

            switch ($age_range) {
                case '0-15':  $q->whereBetween('h.human_Age_y', [0, 15]); break;
                case '16-28': $q->whereBetween('h.human_Age_y', [16, 28]); break;
                case '29-44': $q->whereBetween('h.human_Age_y', [29, 44]); break;
                case '45-59': $q->whereBetween('h.human_Age_y', [45, 59]); break;
                case '60-78': $q->whereBetween('h.human_Age_y', [60, 78]); break;
                case '79-97': $q->whereBetween('h.human_Age_y', [79, 97]); break;
                case '98+':   $q->where('h.human_Age_y', '>=', 98); break;
                default: /* '' ทั้งหมด */ break;
            }
            return $q;
        };

        $HEALTH_OPTIONS = [
            'ปกติ',
            'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)',
            'พิการพึ่งตนเองได้',
            'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้',
        ];

        // ======================
        // Query ครัวเรือน
        // ======================
        $houseQ = HouseholdSurvey2564::query()
            ->when($district, fn ($q) => $q->where('survey_District', $district))
            ->when($subdistrict, fn ($q) => $q->where('survey_Subdistrict', $subdistrict));

        // ======================
        // Query JOIN คน
        // ======================
        $joinHumans = DB::table('human_capital_2564 as h')
            ->join('household_surveys_2564 as s', 's.house_Id', '=', 'h.house_Id')
            ->when($district, fn ($q) => $q->where('s.survey_District', $district))
            ->when($subdistrict, fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict))
            ->when($human_Sex !== '', fn ($q) => $q->where('h.human_Sex', $human_Sex));

        // ✅ ใส่ตัวกรองช่วงอายุเข้า join หลักเลย (มีผลกับทุกสถิติ/กราฟ)
        $applyAgeRange($joinHumans);

        // =========================================================
        // 🔥 CACHE: ตัวเลขหลักทั้งหมด (JOIN หนัก)
        // =========================================================
        $statKey = 'dash_stat_' . ($district ?? '') . '_' . ($subdistrict ?? '') . '_' . $human_Sex . '_' . $age_range;

        $stats = Cache::remember($statKey, 300, function () use ($joinHumans, $HEALTH_OPTIONS) {
            $r = (clone $joinHumans)->selectRaw("
                COUNT(*) as total_members,
                SUM(CASE WHEN TRIM(COALESCE(h.a7_0,'')) = 'ใช่' THEN 1 ELSE 0 END) as welfare_not,
                SUM(CASE WHEN h.human_Sex = 'ชาย' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN h.human_Sex = 'หญิง' THEN 1 ELSE 0 END) as female,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h0,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h1,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h2,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h3
            ", $HEALTH_OPTIONS)->first();

            return [
                'totalMembers' => (int) ($r->total_members ?? 0),
                'welfareNot'   => (int) ($r->welfare_not ?? 0),
                'welfareYes'   => (int) ($r->total_members ?? 0) - (int) ($r->welfare_not ?? 0),
                'sex' => [
                    'ชาย'  => (int) ($r->male ?? 0),
                    'หญิง' => (int) ($r->female ?? 0),
                ],
                'health' => [
                    (int) ($r->h0 ?? 0),
                    (int) ($r->h1 ?? 0),
                    (int) ($r->h2 ?? 0),
                    (int) ($r->h3 ?? 0),
                ],
            ];
        });

        // ======================
        // ค่าที่ใช้ใน Blade
        // ======================
        $totalHouseholds    = (clone $houseQ)->count();
        $totalMembers       = $stats['totalMembers'];
        $welfareNotReceived = $stats['welfareNot'];
        $welfareReceived    = $stats['welfareYes'];
        $welfareTotal       = $totalMembers;
        $sexCounts          = $stats['sex'];

        // ======================
        // ครัวเรือนต่ออำเภอ
        // ======================
        $householdsByDistrict = (clone $houseQ)
            ->selectRaw('survey_District as label, COUNT(*) as total')
            ->groupBy('survey_District')
            ->orderByDesc('total')
            ->get();

        // ======================
        // รายการตำบล
        // ======================
        $subdistrictList = HouseholdSurvey2564::query()
            ->when($district, fn ($q) => $q->where('survey_District', $district))
            ->select('survey_Subdistrict')
            ->whereNotNull('survey_Subdistrict')
            ->where('survey_Subdistrict', '!=', '')
            ->distinct()
            ->orderBy('survey_Subdistrict')
            ->pluck('survey_Subdistrict');


           // ======================
// กราฟสุขภาพ (รองรับ show ตำบล+อำเภอ)
// ======================
if ($view === 'subdistrict' && empty($district)) {
    $view = 'district';
}

$groupField = ($view === 'subdistrict')
    ? 's.survey_Subdistrict'
    : 's.survey_District';

$graphKey = 'dash_graph_' . $view . '_' . ($district ?? '') . '_' . ($subdistrict ?? '') . '_' . $human_Sex . '_' . $age_range;

$raw = Cache::remember($graphKey, 300, function () use ($joinHumans, $groupField, $HEALTH_OPTIONS) {

    $qq = (clone $joinHumans)
        ->selectRaw("$groupField as label");

    // ✅ ถ้าเป็น subdistrict ให้ดึง district มาด้วย
    if ($groupField === 's.survey_Subdistrict') {
        $qq->addSelect(DB::raw("s.survey_District as district_label"));
    }

    // ✅ สำคัญ: total ต่อ label เพื่อคำนวณ "ไม่ระบุ" รายอำเภอ/ตำบล
    $qq->selectRaw("COUNT(*) as total_members");

    $qq->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h0", [$HEALTH_OPTIONS[0]])
       ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h1", [$HEALTH_OPTIONS[1]])
       ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h2", [$HEALTH_OPTIONS[2]])
       ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h3", [$HEALTH_OPTIONS[3]])
       ->whereNotNull(DB::raw($groupField))
       ->where(DB::raw($groupField), '!=', '');

    // ✅ groupBy ให้ถูก
    if ($groupField === 's.survey_Subdistrict') {
        $qq->groupBy('label', 'district_label');
    } else {
        $qq->groupBy('label');
    }

    return $qq->orderBy('label')->get();
});

$labels = $raw->pluck('label')->values();

// ✅ map ตำบล -> อำเภอ
$labelDistrictMap = $raw->mapWithKeys(function ($r) {
    return [$r->label => ($r->district_label ?? '')];
});

// ✅ datasets หลัก
$h0 = $raw->pluck('h0')->map(fn($v)=>(int)$v)->values();
$h1 = $raw->pluck('h1')->map(fn($v)=>(int)$v)->values();
$h2 = $raw->pluck('h2')->map(fn($v)=>(int)$v)->values();
$h3 = $raw->pluck('h3')->map(fn($v)=>(int)$v)->values();

// ✅ "ไม่ระบุ" ต่อ label = total_members - (h0+h1+h2+h3)
$notSpecifiedArr = $raw->map(function($r){
    $total = (int) ($r->total_members ?? 0);
    $known = (int) ($r->h0 ?? 0) + (int) ($r->h1 ?? 0) + (int) ($r->h2 ?? 0) + (int) ($r->h3 ?? 0);
    return max(0, $total - $known);
})->values();

$datasets = [
    ['label' => $HEALTH_OPTIONS[0], 'data' => $h0],
    ['label' => $HEALTH_OPTIONS[1], 'data' => $h1],
    ['label' => $HEALTH_OPTIONS[2], 'data' => $h2],
    ['label' => $HEALTH_OPTIONS[3], 'data' => $h3],
];

// ✅ ถ้ามี "ไม่ระบุ" จริง ค่อยเพิ่มเข้า legend
if ($notSpecifiedArr->sum() > 0) {
    $datasets[] = ['label' => 'ไม่ระบุ', 'data' => $notSpecifiedArr];
}


        return view('welcome', compact(
            'district',
            'subdistrict',
            'view',
            'human_Sex',
            'age_range',     // ✅ ส่งช่วงอายุไป blade
            'AGE_RANGES',    // ✅ ส่ง label ช่วงอายุไป blade
            'totalHouseholds',
            'totalMembers',
            'householdsByDistrict',
            'subdistrictList',
            'labels',
            'labelDistrictMap',
            'datasets',
            'welfareTotal',
            'welfareReceived',
            'welfareNotReceived',
            'sexCounts'
        ));
    }
}
