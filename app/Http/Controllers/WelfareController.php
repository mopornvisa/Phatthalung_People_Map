<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WelfareController extends Controller
{
    public function index(Request $request)
    {
        $actionUrl = route('welfare.index');

        // ======================
        // FILTERS
        // ======================
        $district     = (string) $request->get('district', '');
        $subdistrict  = (string) $request->get('subdistrict', '');

        $welfare      = (string) $request->get('welfare', ''); // '' | received | not_received
        $welfare_type = (array)  $request->input('welfare_type', []);

        if (!in_array($welfare, ['', 'received', 'not_received'], true)) {
            $welfare = '';
        }

        // ✅ OR / AND
        $welfare_match = (string) $request->get('welfare_match', 'any'); // any(OR) | all(AND)
        if (!in_array($welfare_match, ['any','all'], true)) {
            $welfare_match = 'any';
        }

        $house_id = trim((string) $request->get('house_id', ''));
        $title    = trim((string) $request->get('title', ''));
        $fname    = trim((string) $request->get('fname', ''));
        $lname    = trim((string) $request->get('lname', ''));
        $cid      = trim((string) $request->get('cid', ''));

        $survey_year = trim((string) $request->get('survey_year', ''));   // 2564..2568 หรือ ''
        $agey        = trim((string) $request->get('agey', ''));          // fallback
        $age_range   = (string) $request->get('age_range', '');
        $sex         = trim((string) $request->get('sex', ''));

        // ✅ ถ้าไม่ได้เลือก "received" ให้เคลียร์ประเภท
        if ($welfare !== 'received') {
            $welfare_type = [];
        }

        // ✅ helper: แปลง age_range เป็น [min,max] หรือ [min,null]
        $parseAgeRange = function(string $ageRange): array {
            $ageRange = trim($ageRange);
            if ($ageRange === '') return [null, null];

            if (preg_match('/^(\d+)\-(\d+)$/', $ageRange, $m)) {
                return [(int)$m[1], (int)$m[2]];
            }
            if (preg_match('/^(\d+)\+$/', $ageRange, $m)) {
                return [(int)$m[1], null];
            }
            return [null, null];
        };

        // ======================
        // ✅ YEARS + UNION SUBQUERY (2564-2568)
        // ======================
        $ALL_YEARS = [2564,2565,2566,2567,2568];
        $years = $ALL_YEARS;

        if ($survey_year !== '' && ctype_digit($survey_year) && in_array((int)$survey_year, $ALL_YEARS, true)) {
            $years = [(int)$survey_year];
        }

        // ======================
        // ✅ UNION: household + human (ทำให้ทุกปีเป็นชุดเดียว)
        // ======================
        $union = null;

        foreach ($years as $y) {
            $hh = "household_surveys_{$y}";
            $hm = "human_capital_{$y}";

            $qY = DB::table("$hm as h")
                ->join("$hh as s", 's.house_Id', '=', 'h.house_Id')
                ->selectRaw("
                    ? as survey_Year,
                    h.house_Id,

                    -- household
                    s.survey_District,
                    s.survey_Subdistrict,
                    s.survey_Informer_phone,
                    s.latitude,
                    s.longitude,
                    s.house_Number,
                    s.village_No,
                    s.village_Name,
                    s.survey_Postcode,

                    -- human
                    h.human_Member_title,
                    h.human_Member_fname,
                    h.human_Member_lname,
                    h.human_Member_cid,
                    h.human_Order,
                    h.human_Age_y,
                    h.human_Sex,
                    h.human_Health,

                    -- welfare columns
                    h.a7_0, h.a7_1, h.a7_2, h.a7_3, h.a7_4, h.a7_5, h.a7_6
                ", [$y])
                ->whereNotNull('h.house_Id')
                ->where('h.house_Id','!=','');

            $union = $union ? $union->unionAll($qY) : $qY;
        }

        // subquery u (รวมทุกปีแล้ว)
        $u = DB::query()->fromSub($union, 'u');

        // ======================
        // DROPDOWN (CACHE) ✅ ดึงจาก u (ทุกปี)
        // ======================
        $districtList = Cache::remember(
            'welfare_district_union_'.md5(json_encode([$years])),
            3600,
            function () use ($union) {
                return DB::query()
                    ->fromSub($union, 'u')
                    ->whereNotNull('u.survey_District')->where('u.survey_District','!=','')
                    ->distinct()->orderBy('u.survey_District')
                    ->pluck('u.survey_District');
            }
        );

        $subdistrictList = Cache::remember(
            'welfare_subdistrict_union_'.md5(json_encode([$years,$district])),
            3600,
            function () use ($union, $district) {
                return DB::query()
                    ->fromSub($union, 'u')
                    ->when($district !== '', fn($q) => $q->where('u.survey_District', $district))
                    ->whereNotNull('u.survey_Subdistrict')->where('u.survey_Subdistrict','!=','')
                    ->distinct()->orderBy('u.survey_Subdistrict')
                    ->pluck('u.survey_Subdistrict');
            }
        );

        // ======================
        // HELPERS (ใช้กับ u.*)
        // ======================
        $allowedCols = ['a7_1','a7_2','a7_3','a7_4','a7_5','a7_6'];
        $picked      = array_values(array_intersect($welfare_type, $allowedCols));

        $anyReceivedRaw = function(array $cols) {
            return '(' . implode(' OR ', array_map(
                fn($c) => "TRIM(u.$c) = 'ได้รับ'",
                $cols
            )) . ')';
        };

        $noneReceivedRaw = function(array $cols) {
            return '(' . implode(' AND ', array_map(
                fn($c) => "(NULLIF(TRIM(u.$c),'') IS NULL OR TRIM(u.$c) <> 'ได้รับ')",
                $cols
            )) . ')';
        };

        $receivedCondition = function(array $cols, string $mode) {
            if (empty($cols)) return null;

            $conds = array_map(
                fn($c) => "TRIM(u.$c) = 'ได้รับ'",
                $cols
            );

            return '(' . implode($mode === 'all' ? ' AND ' : ' OR ', $conds) . ')';
        };

        // ======================
        // MAIN QUERY (ROWS) จาก u
        // ======================
        $q = DB::query()->fromSub($union, 'u');

        // พื้นที่/ปี (ปีใน u คือ survey_Year ที่เรายัดมาแล้ว)
        if ($district !== '')    $q->where('u.survey_District', $district);
        if ($subdistrict !== '') $q->where('u.survey_Subdistrict', $subdistrict);

        // อายุ/เพศ
        $ageRaw = "TRIM(u.human_Age_y)";
        $sexRaw = "TRIM(u.human_Sex)";
        $a70    = "TRIM(u.a7_0)";
        $a70Empty = "NULLIF($a70,'') IS NULL";

        [$ageMin, $ageMax] = $parseAgeRange($age_range);

        if ($ageMin !== null && $ageMax !== null) {
            $q->whereRaw("CAST($ageRaw AS UNSIGNED) BETWEEN ? AND ?", [$ageMin, $ageMax]);
        } elseif ($ageMin !== null && $ageMax === null) {
            $q->whereRaw("CAST($ageRaw AS UNSIGNED) >= ?", [$ageMin]);
        } elseif ($agey !== '') {
            ctype_digit($agey)
                ? $q->whereRaw("$ageRaw = ?", [$agey])
                : $q->whereRaw("$ageRaw LIKE ?", ["%{$agey}%"]);
        }

        if ($sex !== '') $q->whereRaw("$sexRaw = ?", [$sex]);

        // ค้นหาชื่อ/บ้าน/เลขบัตร
        if ($house_id !== '') $q->where('u.house_Id','like',"%{$house_id}%");
        if ($title    !== '') $q->where('u.human_Member_title','like',"%{$title}%");
        if ($fname    !== '') $q->where('u.human_Member_fname','like',"%{$fname}%");
        if ($lname    !== '') $q->where('u.human_Member_lname','like',"%{$lname}%");
        if ($cid      !== '') $q->where('u.human_Member_cid','like',"%{$cid}%");

        // ✅ กรอง welfare (คง logic เดิม)
        if ($welfare === 'received') {

            $q->where(function($qq) use ($a70, $a70Empty, $allowedCols, $anyReceivedRaw) {
                $qq->whereRaw("$a70 NOT IN ('ใช่','ไม่ได้รับ') AND NULLIF($a70,'') IS NOT NULL")
                   ->orWhereRaw("$a70Empty AND ".$anyReceivedRaw($allowedCols));
            });

            if (!empty($picked)) {
                $cond = $receivedCondition($picked, $welfare_match);
                if ($cond) $q->whereRaw($cond);
            }

        } elseif ($welfare === 'not_received') {

            $q->where(function($qq) use ($a70, $a70Empty, $allowedCols, $noneReceivedRaw) {
                $qq->whereRaw("$a70 IN ('ใช่','ไม่ได้รับ')")
                   ->orWhereRaw("$a70Empty AND ".$noneReceivedRaw($allowedCols));
            });
        }

        // ✅ select ให้เหมือนเดิม (ให้ Blade ใช้ชื่อเดิมได้)
        $q->select([
            'u.house_Id',
            'u.human_Member_title',
            'u.human_Member_fname',
            'u.human_Member_lname',
            'u.human_Member_cid',
            'u.human_Order',

            'u.survey_District',
            'u.survey_Subdistrict',
            'u.survey_Year',

            'u.survey_Informer_phone',
            'u.latitude',
            'u.longitude',

            'u.house_Number',
            'u.village_No',
            'u.village_Name',
            'u.survey_Postcode',

            'u.human_Age_y',
            'u.human_Sex',

            'u.a7_0',
            'u.a7_1','u.a7_2','u.a7_3','u.a7_4','u.a7_5','u.a7_6',
        ]);

        $rows = $q->orderBy('u.survey_Year')          // ✅ เพิ่มเรียงปี
                  ->orderBy('u.survey_District')
                  ->orderBy('u.survey_Subdistrict')
                  ->orderBy('u.house_Id')
                  ->paginate(15)
                  ->appends($request->query());

        // ======================
        // COUNTS (CACHE) ✅ ใช้ union เดียวกัน
        // ======================
        $countKey = 'welfare_counts_union_'.md5(json_encode([
            $years,
            $district,$subdistrict,
            $house_id,$title,$fname,$lname,$cid,
            $agey,$age_range,$sex,
            $picked,$welfare_match
        ]));

        $counts = Cache::remember($countKey, 300, function () use (
            $union,$district,$subdistrict,
            $house_id,$title,$fname,$lname,$cid,$agey,$age_range,$sex,
            $a70,$a70Empty,$allowedCols,$anyReceivedRaw,$noneReceivedRaw,
            $picked,$welfare_match,$receivedCondition,
            $ageRaw,$sexRaw,$parseAgeRange
        ) {
            $base = DB::query()->fromSub($union, 'u')
                ->when($district !== '', fn($q)=>$q->where('u.survey_District',$district))
                ->when($subdistrict !== '', fn($q)=>$q->where('u.survey_Subdistrict',$subdistrict));

            if ($house_id !== '') $base->where('u.house_Id','like',"%{$house_id}%");
            if ($title    !== '') $base->where('u.human_Member_title','like',"%{$title}%");
            if ($fname    !== '') $base->where('u.human_Member_fname','like',"%{$fname}%");
            if ($lname    !== '') $base->where('u.human_Member_lname','like',"%{$lname}%");
            if ($cid      !== '') $base->where('u.human_Member_cid','like',"%{$cid}%");

            // อายุ/เพศ
            [$ageMin, $ageMax] = $parseAgeRange($age_range);

            if ($ageMin !== null && $ageMax !== null) {
                $base->whereRaw("CAST($ageRaw AS UNSIGNED) BETWEEN ? AND ?", [$ageMin, $ageMax]);
            } elseif ($ageMin !== null && $ageMax === null) {
                $base->whereRaw("CAST($ageRaw AS UNSIGNED) >= ?", [$ageMin]);
            } elseif ($agey !== '') {
                ctype_digit($agey)
                    ? $base->whereRaw("$ageRaw = ?", [$agey])
                    : $base->whereRaw("$ageRaw LIKE ?", ["%{$agey}%"]);
            }

            if ($sex !== '') $base->whereRaw("$sexRaw = ?", [$sex]);

            $receivedBase = (clone $base)
                ->where(function($qq) use ($a70,$a70Empty,$allowedCols,$anyReceivedRaw){
                    $qq->whereRaw("$a70 NOT IN ('ใช่','ไม่ได้รับ') AND NULLIF($a70,'') IS NOT NULL")
                       ->orWhereRaw("$a70Empty AND ".$anyReceivedRaw($allowedCols));
                });

            if (!empty($picked)) {
                $cond = $receivedCondition($picked, $welfare_match);
                if ($cond) $receivedBase->whereRaw($cond);
            }

            $notReceivedBase = (clone $base)
                ->where(function($qq) use ($a70,$a70Empty,$allowedCols,$noneReceivedRaw){
                    $qq->whereRaw("$a70 IN ('ใช่','ไม่ได้รับ')")
                       ->orWhereRaw("$a70Empty AND ".$noneReceivedRaw($allowedCols));
                });

            return [
                'received'     => $receivedBase->count(),
                'not_received' => $notReceivedBase->count(),
            ];
        });

        return view('welfare', compact(
            'actionUrl',
            'district','subdistrict','districtList','subdistrictList',
            'welfare','welfare_type','welfare_match',
            'house_id','title','fname','lname','cid',
            'survey_year','agey','age_range','sex',
            'counts','rows'
        ));
    }
}
