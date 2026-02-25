<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// ✅ Excel
use App\Exports\HealthExport;
use Maatwebsite\Excel\Facades\Excel;

class HealthController extends Controller
{
    public function index(Request $request)
    {
        $YEARS = [2564,2565,2566,2567,2568];

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
        $district    = trim((string) $request->get('district', ''));
        $subdistrict = trim((string) $request->get('subdistrict', ''));

        if (!in_array($health, array_merge(['', $HEALTH_NULL_TOKEN], $HEALTH_OPTIONS), true)) {
            $health = '';
        }

        $house_id    = trim((string) $request->get('house_id', ''));
        $survey_year = trim((string) $request->get('survey_year', '')); // 2564..2568 (ถ้าเลือก)
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
            return $q->whereRaw("CAST(NULLIF(h.human_Age_y,'') AS UNSIGNED) BETWEEN ? AND ?", [$min, $max]);
        };

        // =========================================================
        // ✅ เลือกโหมดเร็ว:
        // - ถ้าเลือกปี -> ยิงเข้าตารางปีนั้น (เร็วสุด)
        // - ถ้าไม่เลือกปี -> UNION ทุกปี
        // =========================================================
        $yearsToUse = $YEARS;
        if ($survey_year !== '' && in_array((int)$survey_year, $YEARS, true)) {
            $yearsToUse = [(int)$survey_year];
        }

        $buildSurveySub = function(array $years) {
            $surveyUnionSql = collect($years)->map(function ($y) {
                return "
                    SELECT
                        house_Id,
                        {$y} AS survey_Year,
                        survey_District,
                        survey_Subdistrict,
                        latitude,
                        longitude,
                        survey_Informer_phone,
                        house_Number,
                        village_No,
                        village_Name,
                        survey_Postcode
                    FROM household_surveys_{$y}
                ";
            })->implode(" UNION ALL ");
            return DB::query()->fromRaw("({$surveyUnionSql}) as s_all");
        };

        $buildHumanSub = function(array $years) {
            $humanUnionSql = collect($years)->map(function ($y) {
                return "
                    SELECT
                        house_Id,
                        {$y} AS survey_Year,
                        human_Order,
                        human_Member_title,
                        human_Member_fname,
                        human_Member_lname,
                        human_Member_cid,
                        human_Age_y,
                        human_Sex,
                        human_Health
                    FROM human_capital_{$y}
                ";
            })->implode(" UNION ALL ");
            return DB::query()->fromRaw("({$humanUnionSql}) as h_all");
        };

        // ✅ สร้าง base
        if (count($yearsToUse) === 1) {
            // ====== โหมดเร็ว: ปีเดียว ======
            $y = (int)$yearsToUse[0];

            $base = DB::table("human_capital_{$y} as h")
                ->leftJoin("household_surveys_{$y} as s", function ($j) {
                    $j->on('s.house_Id', '=', 'h.house_Id');
                });

            // สำหรับ dropdown list
            $surveySubForLists = DB::table("household_surveys_{$y} as s");
        } else {
            // ====== โหมดรวมทุกปี: UNION ======
            $surveySub = $buildSurveySub($yearsToUse);
            $humanSub  = $buildHumanSub($yearsToUse);

            $base = DB::query()
                ->fromSub($humanSub, 'h')
                ->leftJoinSub($surveySub, 's', function ($j) {
                    $j->on('s.house_Id', '=', 'h.house_Id')
                      ->on('s.survey_Year', '=', 'h.survey_Year');
                });

            $surveySubForLists = $surveySub; // ใช้ทำ list อำเภอ/ตำบล
        }

        // ======================
        // ✅ ใส่ filters (ยกเว้น health ไว้ค่อยกรองเฉพาะตาราง)
        // ======================
        $base
            ->when($district !== '', fn ($q) => $q->where('s.survey_District', $district))
            ->when($subdistrict !== '', fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict))
            ->when($house_id !== '', fn ($q) => $q->where('h.house_Id', 'like', "%{$house_id}%"))
            ->when($title !== '', fn ($q) => $q->where('h.human_Member_title', 'like', "%{$title}%"))
            ->when($fname !== '', fn ($q) => $q->where('h.human_Member_fname', 'like', "%{$fname}%"))
            ->when($lname !== '', fn ($q) => $q->where('h.human_Member_lname', 'like', "%{$lname}%"))
            ->when($cid !== '', fn ($q) => $q->where('h.human_Member_cid', 'like', "%{$cid}%"))
            ->when($agey !== '', fn ($q) => $q->where('h.human_Age_y', $agey))
            ->when($sex !== '', fn ($q) => $q->where('h.human_Sex', $sex));

        $base = $applyAgeRange($base);

        // ======================
        // ✅ COUNTS (การ์ดด้านบน) ไม่ผูก health
        // ======================
        $countKey = 'health_counts:' . md5(json_encode([
            'years' => $yearsToUse,
            'district' => $district,
            'subdistrict' => $subdistrict,
            'house_id' => $house_id,
            'title' => $title,
            'fname' => $fname,
            'lname' => $lname,
            'cid' => $cid,
            'agey' => $agey,
            'age_range' => $age_range,
            'sex' => $sex,
        ], JSON_UNESCAPED_UNICODE));

        $countsRaw = Cache::remember($countKey, 900, function () use ($base, $HEALTH_OPTIONS) {
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
        // ✅ ROWS (TABLE) ค่อยกรอง health ตรงนี้
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
            's.house_Number',
            's.village_No',
            's.village_Name',
            's.survey_Postcode',
        ]);

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
            ->orderByDesc('h.survey_Year')
            ->orderBy('h.house_Id')
            ->orderByRaw("CAST(NULLIF(h.human_Order,'') AS UNSIGNED)")
            ->paginate(20)
            ->appends($request->all());

        // ======================
        // ✅ DROPDOWN (CACHE) — ดึงจาก yearsToUse
        // ======================
        $districtListKey = 'health_district_list:' . md5(json_encode(['years'=>$yearsToUse], JSON_UNESCAPED_UNICODE));
        $districtList = Cache::remember($districtListKey, 3600, function () use ($surveySubForLists) {
            return DB::query()->fromSub($surveySubForLists, 's')
                ->whereNotNull('s.survey_District')
                ->where('s.survey_District', '<>', '')
                ->distinct()
                ->orderBy('s.survey_District')
                ->pluck('s.survey_District');
        });

        $subdistrictList = collect([]);
        if ($district !== '') {
            $subdistrictListKey = 'health_subdistrict_list:' . md5(json_encode(['years'=>$yearsToUse,'district'=>$district], JSON_UNESCAPED_UNICODE));
            $subdistrictList = Cache::remember($subdistrictListKey, 3600, function () use ($surveySubForLists, $district) {
                return DB::query()->fromSub($surveySubForLists, 's')
                    ->where('s.survey_District', $district)
                    ->whereNotNull('s.survey_Subdistrict')
                    ->where('s.survey_Subdistrict', '<>', '')
                    ->distinct()
                    ->orderBy('s.survey_Subdistrict')
                    ->pluck('s.survey_Subdistrict');
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

    // ✅ EXPORT EXCEL: ใช้ filter เดียวกับหน้า index แต่เปลี่ยน paginate -> get()
    public function export(Request $request)
    {
        $YEARS = [2564,2565,2566,2567,2568];

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
        $district    = trim((string) $request->get('district', ''));
        $subdistrict = trim((string) $request->get('subdistrict', ''));

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
            return $q->whereRaw("CAST(NULLIF(h.human_Age_y,'') AS UNSIGNED) BETWEEN ? AND ?", [$min, $max]);
        };

        // ======================
        // YEARS
        // ======================
        $yearsToUse = $YEARS;
        if ($survey_year !== '' && in_array((int)$survey_year, $YEARS, true)) {
            $yearsToUse = [(int)$survey_year];
        }

        $buildSurveySub = function(array $years) {
            $surveyUnionSql = collect($years)->map(function ($y) {
                return "
                    SELECT
                        house_Id,
                        {$y} AS survey_Year,
                        survey_District,
                        survey_Subdistrict,
                        latitude,
                        longitude,
                        survey_Informer_phone,
                        house_Number,
                        village_No,
                        village_Name,
                        survey_Postcode
                    FROM household_surveys_{$y}
                ";
            })->implode(" UNION ALL ");
            return DB::query()->fromRaw("({$surveyUnionSql}) as s_all");
        };

        $buildHumanSub = function(array $years) {
            $humanUnionSql = collect($years)->map(function ($y) {
                return "
                    SELECT
                        house_Id,
                        {$y} AS survey_Year,
                        human_Order,
                        human_Member_title,
                        human_Member_fname,
                        human_Member_lname,
                        human_Member_cid,
                        human_Age_y,
                        human_Sex,
                        human_Health
                    FROM human_capital_{$y}
                ";
            })->implode(" UNION ALL ");
            return DB::query()->fromRaw("({$humanUnionSql}) as h_all");
        };

        // ✅ base
        if (count($yearsToUse) === 1) {
            $y = (int)$yearsToUse[0];

            $base = DB::table("human_capital_{$y} as h")
                ->leftJoin("household_surveys_{$y} as s", function ($j) {
                    $j->on('s.house_Id', '=', 'h.house_Id');
                });
        } else {
            $surveySub = $buildSurveySub($yearsToUse);
            $humanSub  = $buildHumanSub($yearsToUse);

            $base = DB::query()
                ->fromSub($humanSub, 'h')
                ->leftJoinSub($surveySub, 's', function ($j) {
                    $j->on('s.house_Id', '=', 'h.house_Id')
                      ->on('s.survey_Year', '=', 'h.survey_Year');
                });
        }

        // ✅ filters
        $base
            ->when($district !== '', fn ($q) => $q->where('s.survey_District', $district))
            ->when($subdistrict !== '', fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict))
            ->when($house_id !== '', fn ($q) => $q->where('h.house_Id', 'like', "%{$house_id}%"))
            ->when($title !== '', fn ($q) => $q->where('h.human_Member_title', 'like', "%{$title}%"))
            ->when($fname !== '', fn ($q) => $q->where('h.human_Member_fname', 'like', "%{$fname}%"))
            ->when($lname !== '', fn ($q) => $q->where('h.human_Member_lname', 'like', "%{$lname}%"))
            ->when($cid !== '', fn ($q) => $q->where('h.human_Member_cid', 'like', "%{$cid}%"))
            ->when($agey !== '', fn ($q) => $q->where('h.human_Age_y', $agey))
            ->when($sex !== '', fn ($q) => $q->where('h.human_Sex', $sex));

        $base = $applyAgeRange($base);

        // ✅ rows
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
            's.house_Number',
            's.village_No',
            's.village_Name',
            's.survey_Postcode',
        ]);

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
            ->orderByDesc('h.survey_Year')
            ->orderBy('h.house_Id')
            ->orderByRaw("CAST(NULLIF(h.human_Order,'') AS UNSIGNED)")
            ->get();

        $fileName = 'health_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new HealthExport($rows), $fileName);
    }
}
