<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ======================
        // ✅ ปีที่ต้องการแสดง (กรอง)
        // ======================
        $year = (string) $request->get('year', 'all'); // all | 2564..2568
        $YEAR_OPTIONS = ['all', '2564', '2565', '2566', '2567', '2568'];
        if (!in_array($year, $YEAR_OPTIONS, true)) $year = 'all';

        $years = ($year === 'all')
            ? [2564, 2565, 2566, 2567, 2568]
            : [(int) $year];

        $yearLabel = ($year === 'all') ? '2564–2568' : $year;

        // ======================
        // รับค่าจาก query
        // ======================
        $district    = (string) $request->get('district', '');
        $subdistrict = (string) $request->get('subdistrict', '');
        $view        = (string) $request->get('view', 'district');

        // ✅ เพศ
        $human_Sex = (string) $request->get('human_Sex', '');
        if (!in_array($human_Sex, ['', 'ชาย', 'หญิง'], true)) $human_Sex = '';

        // ✅ ช่วงอายุ
        $age_range = (string) $request->get('age_range', '');
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
        if (!array_key_exists($age_range, $AGE_RANGES)) $age_range = '';

        // ✅ helper ใส่ช่วงอายุเข้า query (alias h)
        $applyAgeRange = function ($q) use ($age_range) {
            if ($age_range === '') return $q;

            $q->whereNotNull('h.human_Age_y');
            switch ($age_range) {
                case '0-15':  $q->whereBetween('h.human_Age_y', [0, 15]); break;
                case '16-28': $q->whereBetween('h.human_Age_y', [16, 28]); break;
                case '29-44': $q->whereBetween('h.human_Age_y', [29, 44]); break;
                case '45-59': $q->whereBetween('h.human_Age_y', [45, 59]); break;
                case '60-78': $q->whereBetween('h.human_Age_y', [60, 78]); break;
                case '79-97': $q->whereBetween('h.human_Age_y', [79, 97]); break;
                case '98+':   $q->where('h.human_Age_y', '>=', 98); break;
            }
            return $q;
        };

        $HEALTH_OPTIONS = [
            'ปกติ',
            'ป่วยเรื้อรังที่ไม่ติดเตียง (เช่น หัวใจ เบาหวาน)',
            'พิการพึ่งตนเองได้',
            'ผู้ป่วยติดเตียง/พิการพึ่งตัวเองไม่ได้',
        ];

        // =========================================================
        // ✅ สร้าง query แบบ “กรองก่อน UNION” (เร็วขึ้นมาก)
        // =========================================================
        if ($year === 'all') {

            // household union
            $surveyUnion = null;
            foreach ($years as $y) {
                $q = DB::table("household_surveys_{$y} as s")
                    ->select(['s.house_Id', 's.survey_District', 's.survey_Subdistrict'])
                    ->when($district !== '', fn($qq) => $qq->where('s.survey_District', $district))
                    ->when($subdistrict !== '', fn($qq) => $qq->where('s.survey_Subdistrict', $subdistrict));

                $surveyUnion = $surveyUnion ? $surveyUnion->unionAll($q) : $q;
            }

            // human union
            $humanUnion = null;
            foreach ($years as $y) {
                $q = DB::table("human_capital_{$y} as h")
                    ->select(['h.house_Id', 'h.human_Sex', 'h.human_Age_y', 'h.human_Health', 'h.a7_0'])
                    ->when($human_Sex !== '', fn($qq) => $qq->where('h.human_Sex', $human_Sex));

                if ($age_range !== '') {
                    $q->whereNotNull('h.human_Age_y');
                    switch ($age_range) {
                        case '0-15':  $q->whereBetween('h.human_Age_y', [0, 15]); break;
                        case '16-28': $q->whereBetween('h.human_Age_y', [16, 28]); break;
                        case '29-44': $q->whereBetween('h.human_Age_y', [29, 44]); break;
                        case '45-59': $q->whereBetween('h.human_Age_y', [45, 59]); break;
                        case '60-78': $q->whereBetween('h.human_Age_y', [60, 78]); break;
                        case '79-97': $q->whereBetween('h.human_Age_y', [79, 97]); break;
                        case '98+':   $q->where('h.human_Age_y', '>=', 98); break;
                    }
                }

                $humanUnion = $humanUnion ? $humanUnion->unionAll($q) : $q;
            }

            $surveySub = DB::query()->fromSub($surveyUnion, 's');
            $humanSub  = DB::query()->fromSub($humanUnion, 'h');

            $houseQ = DB::query()->fromSub($surveySub, 's');

            $joinHumansBase = DB::query()
                ->fromSub($humanSub, 'h')
                ->joinSub($surveySub, 's', 's.house_Id', '=', 'h.house_Id');

        } else {

            $y = (int) $year;

            $houseQ = DB::table("household_surveys_{$y} as s")
                ->when($district !== '', fn ($q) => $q->where('s.survey_District', $district))
                ->when($subdistrict !== '', fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict));

            $joinHumansBase = DB::table("human_capital_{$y} as h")
                ->join("household_surveys_{$y} as s", 's.house_Id', '=', 'h.house_Id')
                ->when($district !== '', fn ($q) => $q->where('s.survey_District', $district))
                ->when($subdistrict !== '', fn ($q) => $q->where('s.survey_Subdistrict', $subdistrict))
                ->when($human_Sex !== '', fn ($q) => $q->where('h.human_Sex', $human_Sex));

            $applyAgeRange($joinHumansBase);
        }

        // =========================================================
        // 🔥 CACHE
        // =========================================================
        $cacheTtl = 1800;   // 30 นาที
        $listTtl  = 21600;  // 6 ชม.

        $baseKey = 'dash:' . md5(json_encode([
            'year'        => $year,
            'district'    => $district,
            'subdistrict' => $subdistrict,
            'sex'         => $human_Sex,
            'age'         => $age_range,
        ], JSON_UNESCAPED_UNICODE));

        // ======================
        // ✅ stats
        // ======================
        $stats = Cache::remember($baseKey . ':stats', $cacheTtl, function () use ($joinHumansBase, $HEALTH_OPTIONS) {
            $r = (clone $joinHumansBase)->selectRaw("
                COUNT(*) as total_members,
                SUM(CASE WHEN COALESCE(h.a7_0,'') = 'ใช่' THEN 1 ELSE 0 END) as welfare_not,
                SUM(CASE WHEN h.human_Sex = 'ชาย' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN h.human_Sex = 'หญิง' THEN 1 ELSE 0 END) as female,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h0,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h1,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h2,
                SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h3
            ", $HEALTH_OPTIONS)->first();

            $total = (int) ($r->total_members ?? 0);
            $not   = (int) ($r->welfare_not ?? 0);

            return [
                'totalMembers' => $total,
                'welfareNot'   => $not,
                'welfareYes'   => max(0, $total - $not),
                'sex' => [
                    'ชาย'  => (int) ($r->male ?? 0),
                    'หญิง' => (int) ($r->female ?? 0),
                ],
            ];
        });

        // ✅ household count
        $totalHouseholds = Cache::remember($baseKey . ':totalHouseholds', $cacheTtl, function () use ($houseQ) {
            return (clone $houseQ)->distinct()->count('s.house_Id');
        });

        // ✅ ครัวเรือนต่ออำเภอ
        $householdsByDistrict = Cache::remember($baseKey . ':householdsByDistrict', $cacheTtl, function () use ($houseQ) {
            return (clone $houseQ)
                ->selectRaw('s.survey_District as label, COUNT(DISTINCT s.house_Id) as total')
                ->whereNotNull('s.survey_District')
                ->where('s.survey_District', '!=', '')
                ->groupBy('s.survey_District')
                ->orderByDesc('total')
                ->get();
        });

        // ✅ รายการตำบล
        $subdistrictList = Cache::remember($baseKey . ':subdistrictList', $listTtl, function () use ($houseQ) {
            return (clone $houseQ)
                ->select('s.survey_Subdistrict')
                ->whereNotNull('s.survey_Subdistrict')
                ->where('s.survey_Subdistrict', '!=', '')
                ->distinct()
                ->orderBy('s.survey_Subdistrict')
                ->pluck('s.survey_Subdistrict');
        });

        // ค่าที่ใช้ใน Blade
        $totalMembers       = $stats['totalMembers'];
        $welfareNotReceived = $stats['welfareNot'];
        $welfareReceived    = $stats['welfareYes'];
        $welfareTotal       = $totalMembers;
        $sexCounts          = $stats['sex'];

        // ======================
        // กราฟสุขภาพ
        // ======================
        if ($view === 'subdistrict' && $district === '') $view = 'district';

        $groupField = ($view === 'subdistrict')
            ? 's.survey_Subdistrict'
            : 's.survey_District';

        $graphKey = $baseKey . ':graph:' . $view;
        $MAX_GROUPS = 30;

        $raw = Cache::remember($graphKey, $cacheTtl, function () use ($joinHumansBase, $groupField, $HEALTH_OPTIONS, $MAX_GROUPS) {
            $qq = (clone $joinHumansBase)->selectRaw("$groupField as label");

            if ($groupField === 's.survey_Subdistrict') {
                $qq->addSelect(DB::raw("s.survey_District as district_label"));
            }

            $qq->selectRaw("COUNT(*) as total_members")
               ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h0", [$HEALTH_OPTIONS[0]])
               ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h1", [$HEALTH_OPTIONS[1]])
               ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h2", [$HEALTH_OPTIONS[2]])
               ->selectRaw("SUM(CASE WHEN h.human_Health = ? THEN 1 ELSE 0 END) as h3", [$HEALTH_OPTIONS[3]])
               ->whereNotNull($groupField)
               ->where($groupField, '!=', '');

            if ($groupField === 's.survey_Subdistrict') {
                $qq->groupBy('label', 'district_label');
            } else {
                $qq->groupBy('label');
            }

            return $qq->orderByDesc('total_members')->limit($MAX_GROUPS)->get();
        });

        $labels = $raw->pluck('label')->values();

        $labelDistrictMap = $raw->mapWithKeys(function ($r) {
            return [$r->label => ($r->district_label ?? '')];
        });

        $h0 = $raw->pluck('h0')->map(fn ($v) => (int) $v)->values();
        $h1 = $raw->pluck('h1')->map(fn ($v) => (int) $v)->values();
        $h2 = $raw->pluck('h2')->map(fn ($v) => (int) $v)->values();
        $h3 = $raw->pluck('h3')->map(fn ($v) => (int) $v)->values();

        $notSpecifiedArr = $raw->map(function ($r) {
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

        if ($notSpecifiedArr->sum() > 0) {
            $datasets[] = ['label' => 'ไม่ระบุ', 'data' => $notSpecifiedArr];
        }

        // =========================================================
        // ✅ Capitals ปีที่เลือก (Mean/SD/Radar)
        // =========================================================
        $capYear = ($year === 'all') ? 2568 : (int)$year;
        $capYears = [2564,2565,2566,2567,2568];
        if (!in_array($capYear, $capYears, true)) $capYear = 2568;

        $capKey = $baseKey . ':capitals:' . $capYear;

        [$capSummary, $capStd, $capRadar, $capRadarStd] = Cache::remember(
            $capKey,
            $cacheTtl,
            function () use ($capYear, $district, $subdistrict) {

                $table = "total_capital_data_{$capYear}";

                $pick = function(string $table, array $cands): ?string {
                    foreach ($cands as $c) {
                        try {
                            if (Schema::hasColumn($table, $c)) return $c;
                        } catch (\Throwable $e) {}
                    }
                    return null;
                };

                $districtCol = $pick($table, ['district', 'survey_District', 'District']);
                $subdistCol  = $pick($table, ['subdistrict', 'survey_Subdistrict', 'Subdistrict']);

                $qb = DB::table($table);

                if ($district !== '' && $districtCol) {
                    $qb->where($districtCol, $district);
                }
                if ($subdistrict !== '' && $subdistCol) {
                    $qb->where($subdistCol, $subdistrict);
                }

                $avg = (clone $qb)->selectRaw("
                    AVG(COALESCE(human_Total,0))     as human,
                    AVG(COALESCE(physical_Total,0))  as physical,
                    AVG(COALESCE(financial_Total,0)) as financial,
                    AVG(COALESCE(natural_Total,0))   as natural_capital,
                    AVG(COALESCE(social_Total,0))    as social
                ")->first();

                $sd = (clone $qb)->selectRaw("
                    STDDEV_POP(COALESCE(human_Total,0))     as human,
                    STDDEV_POP(COALESCE(physical_Total,0))  as physical,
                    STDDEV_POP(COALESCE(financial_Total,0)) as financial,
                    STDDEV_POP(COALESCE(natural_Total,0))   as natural_capital,
                    STDDEV_POP(COALESCE(social_Total,0))    as social
                ")->first();

                $summary = [
                    'human'     => (float)($avg->human ?? 0),
                    'physical'  => (float)($avg->physical ?? 0),
                    'financial' => (float)($avg->financial ?? 0),
                    'natural'   => (float)($avg->natural_capital ?? 0),
                    'social'    => (float)($avg->social ?? 0),
                ];

                $std = [
                    'human'     => (float)($sd->human ?? 0),
                    'physical'  => (float)($sd->physical ?? 0),
                    'financial' => (float)($sd->financial ?? 0),
                    'natural'   => (float)($sd->natural_capital ?? 0),
                    'social'    => (float)($sd->social ?? 0),
                ];

                $radar = [
                    $summary['human'],
                    $summary['physical'],
                    $summary['financial'],
                    $summary['natural'],
                    $summary['social'],
                ];

                $radarStd = [
                    $std['human'],
                    $std['physical'],
                    $std['financial'],
                    $std['natural'],
                    $std['social'],
                ];

                return [$summary, $std, $radar, $radarStd];
            }
        );

        // =========================================================
        // ✅ NEW: capByYear (ค่าเฉลี่ยทุน 5 ด้าน "รายปี" 2564–2568)
        // =========================================================
        $capYearsAll = [2564,2565,2566,2567,2568];
        $capByYearKey = $baseKey . ':capByYear';

        $capByYear = Cache::remember($capByYearKey, $cacheTtl, function () use ($capYearsAll, $district, $subdistrict) {

            $pick = function(string $table, array $cands): ?string {
                foreach ($cands as $c) {
                    try {
                        if (Schema::hasColumn($table, $c)) return $c;
                    } catch (\Throwable $e) {}
                }
                return null;
            };

            $out = [];

            foreach ($capYearsAll as $y) {
                $table = "total_capital_data_{$y}";

                if (!Schema::hasTable($table)) {
                    $out[$y] = ['human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0];
                    continue;
                }

                $districtCol = $pick($table, ['district', 'survey_District', 'District']);
                $subdistCol  = $pick($table, ['subdistrict', 'survey_Subdistrict', 'Subdistrict']);

                $qb = DB::table($table);

                if ($district !== '' && $districtCol) {
                    $qb->where($districtCol, $district);
                }
                if ($subdistrict !== '' && $subdistCol) {
                    $qb->where($subdistCol, $subdistrict);
                }

                $avg = (clone $qb)->selectRaw("
                    AVG(COALESCE(human_Total,0))     as human,
                    AVG(COALESCE(physical_Total,0))  as physical,
                    AVG(COALESCE(financial_Total,0)) as financial,
                    AVG(COALESCE(natural_Total,0))   as natural_capital,
                    AVG(COALESCE(social_Total,0))    as social
                ")->first();

                $out[$y] = [
                    'human'     => (float)($avg->human ?? 0),
                    'physical'  => (float)($avg->physical ?? 0),
                    'financial' => (float)($avg->financial ?? 0),
                    'natural'   => (float)($avg->natural_capital ?? 0),
                    'social'    => (float)($avg->social ?? 0),
                ];
            }

            return $out;
        });

        // ======================
        // ✅ ส่งไป Blade
        // ======================
        return view('welcome', compact(
            'year','yearLabel','YEAR_OPTIONS',
            'district','subdistrict','view',
            'human_Sex','age_range','AGE_RANGES',
            'totalHouseholds','totalMembers',
            'householdsByDistrict','subdistrictList',
            'labels','labelDistrictMap','datasets',
            'welfareTotal','welfareReceived','welfareNotReceived',
            'sexCounts',

            // capitals
            'capYear','capSummary','capStd','capRadar','capRadarStd',

            // NEW
            'capByYear'
        ));
    }
}
