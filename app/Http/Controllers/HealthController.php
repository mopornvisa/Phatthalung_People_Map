<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function index(Request $request)
    {
        $HEALTH_OPTIONS = [
            'ปกติ',
            'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)',
            'พิการพึ่งตนเองได้',
            'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้',
        ];
        $HEALTH_NULL_TOKEN = '__NULL__';

        // ======================
        // FILTERS
        // ======================
        $health      = (string) $request->get('health', '');
        $district    = (string) $request->get('district', '');
        $subdistrict = (string) $request->get('subdistrict', '');

        if (!in_array($health, array_merge(['', $HEALTH_NULL_TOKEN], $HEALTH_OPTIONS), true)) {
            $health = '';
        }

        $house_id    = trim((string) $request->get('house_id', ''));
        $survey_year = trim((string) $request->get('survey_year', ''));
        $title       = trim((string) $request->get('title', ''));
        $fname       = trim((string) $request->get('fname', ''));
        $lname       = trim((string) $request->get('lname', ''));
        $cid         = trim((string) $request->get('cid', ''));
        $agey        = trim((string) $request->get('agey', ''));
        $sex         = trim((string) $request->get('sex', ''));

        // ✅ age_range
        $age_range   = trim((string) $request->get('age_range', ''));
        $ALLOWED_AGE_RANGES = ['0-15','16-28','29-44','45-59','60-78','79-97','98+'];
        if (!in_array($age_range, array_merge([''], $ALLOWED_AGE_RANGES), true)) {
            $age_range = '';
        }

        $applyAgeRange = function ($q) use ($age_range) {
            if ($age_range === '') return $q;

            if ($age_range === '98+') {
                return $q->whereRaw("CAST(NULLIF(h.human_Age_y,'') AS UNSIGNED) >= 98");
            }

            [$min, $max] = array_map('intval', explode('-', $age_range));
            return $q->whereRaw(
                "CAST(NULLIF(h.human_Age_y,'') AS UNSIGNED) BETWEEN ? AND ?",
                [$min, $max]
            );
        };

        // ======================
        // BASE QUERY (❗ไม่ใส่ health filter ที่นี่)
        // ======================
        $base = DB::table('human_capital_2564 as h')
            ->leftJoin('household_surveys_2564 as s', 's.house_Id', '=', 'h.house_Id')
            ->when($district !== '', fn ($q) => $q->where('s.survey_District', $district))
            ->when($subdistrict !== '', fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict))
            ->when($house_id !== '', fn ($q) => $q->where('h.house_Id', 'like', "%{$house_id}%"))
            ->when($survey_year !== '', fn ($q) => $q->where('h.survey_Year', $survey_year))
            ->when($title !== '', fn ($q) => $q->where('h.human_Member_title', 'like', "%{$title}%"))
            ->when($fname !== '', fn ($q) => $q->where('h.human_Member_fname', 'like', "%{$fname}%"))
            ->when($lname !== '', fn ($q) => $q->where('h.human_Member_lname', 'like', "%{$lname}%"))
            ->when($cid !== '', fn ($q) => $q->where('h.human_Member_cid', 'like', "%{$cid}%"))
            ->when($agey !== '', fn ($q) => $q->where('h.human_Age_y', $agey))
            ->when($sex !== '', fn ($q) => $q->where('h.human_Sex', $sex));

        $base = $applyAgeRange($base);

        // ======================
        // COUNTS (การ์ดด้านบน) ✅ "ไม่" รวม health
        // ======================
        $countKey = 'health_counts_' . md5(
            $district.'|'.$subdistrict.'|'.$house_id.'|'.$survey_year.'|'.$title.'|'.$fname.'|'.$lname.'|'.$cid.'|'.$agey.'|'.$age_range.'|'.$sex
        );

        $countsRaw = Cache::remember($countKey, 300, function () use ($base, $HEALTH_OPTIONS) {
            return (clone $base)
                ->selectRaw("
                    SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) AS normal_cnt,
                    SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) AS chronic_cnt,
                    SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) AS disable_cnt,
                    SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) AS bed_cnt,
                    SUM(CASE WHEN h.human_Health IS NULL OR h.human_Health = '' OR h.human_Health = 'ไม่ระบุ' THEN 1 ELSE 0 END) AS unknown_cnt
                ", $HEALTH_OPTIONS)
                ->first();
        });

        $counts = [
            $HEALTH_OPTIONS[0] => (int)($countsRaw->normal_cnt ?? 0),
            $HEALTH_OPTIONS[1] => (int)($countsRaw->chronic_cnt ?? 0),
            $HEALTH_OPTIONS[2] => (int)($countsRaw->disable_cnt ?? 0),
            $HEALTH_OPTIONS[3] => (int)($countsRaw->bed_cnt ?? 0),
            $HEALTH_NULL_TOKEN => (int)($countsRaw->unknown_cnt ?? 0),
        ];

        // ======================
        // ROWS (TABLE) ✅ ค่อยกรอง health ตรงนี้
        // ======================
        $rowsQuery = (clone $base)->select([
            'h.house_Id',
            'h.survey_Year',
            'h.human_Order',
            'h.human_Member_title',
            'h.human_Member_fname',
            'h.human_Member_lname',
            'h.human_Member_cid',
            'h.human_Age_y',
            'h.human_Sex',
            'h.human_Health',

            's.survey_District',
            's.survey_Subdistrict',
            's.latitude',
            's.longitude',
            's.survey_Informer_phone',

            // ✅ เพิ่มใหม่
            's.house_Number',
            's.village_No',
            's.village_Name',
            's.survey_Postcode',
        ])
        // ✅ กันแถวซ้ำ/เพี้ยน เมื่อ join + paginate
        ->groupBy(
            'h.house_Id',
            'h.survey_Year',
            'h.human_Order',
            'h.human_Member_title',
            'h.human_Member_fname',
            'h.human_Member_lname',
            'h.human_Member_cid',
            'h.human_Age_y',
            'h.human_Sex',
            'h.human_Health',

            's.survey_District',
            's.survey_Subdistrict',
            's.latitude',
            's.longitude',
            's.survey_Informer_phone',

            // ✅ ต้องอยู่ใน groupBy ด้วย
            's.house_Number',
            's.village_No',
            's.village_Name',
            's.survey_Postcode'
        );

        // ✅ health filter เฉพาะตาราง
        if ($health === $HEALTH_NULL_TOKEN) {
            $rowsQuery->where(function ($q) {
                $q->whereNull('h.human_Health')
                  ->orWhere('h.human_Health', '')
                  ->orWhere('h.human_Health', 'ไม่ระบุ');
            });
        } elseif ($health !== '') {
            $rowsQuery->where('h.human_Health', $health);
        }

        $rows = $rowsQuery
            ->orderBy('h.house_Id')
            ->orderByRaw("CAST(NULLIF(h.human_Order,'') AS UNSIGNED)")
            ->paginate(15)
            ->appends($request->all());

        // ======================
        // DROPDOWN (CACHE)
        // ======================
        $districtList = Cache::remember('health_district_list', 3600, function () {
            return DB::table('household_surveys_2564')
                ->whereNotNull('survey_District')
                ->where('survey_District', '<>', '')
                ->distinct()
                ->orderBy('survey_District')
                ->pluck('survey_District');
        });

        $subdistrictList = collect([]);
        if ($district !== '') {
            $subdistrictList = Cache::remember('health_subdistrict_'.$district, 3600, function () use ($district) {
                return DB::table('household_surveys_2564')
                    ->where('survey_District', $district)
                    ->whereNotNull('survey_Subdistrict')
                    ->where('survey_Subdistrict', '<>', '')
                    ->distinct()
                    ->orderBy('survey_Subdistrict')
                    ->pluck('survey_Subdistrict');
            });
        }

        return view('test', compact(
            'rows', 'counts', 'health', 'district', 'subdistrict',
            'districtList', 'subdistrictList', 'HEALTH_OPTIONS',
            'house_id', 'survey_year', 'title', 'fname', 'lname', 'cid',
            'agey', 'age_range', 'sex',
            'HEALTH_NULL_TOKEN'
        ));
    }
}
