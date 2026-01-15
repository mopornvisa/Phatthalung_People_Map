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

        $survey_year = trim((string) $request->get('survey_year', ''));
        $agey        = trim((string) $request->get('agey', ''));          // (เดิม) fallback
        $age_range   = (string) $request->get('age_range', '');           // ✅ NEW: ช่วงอายุ
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
        // DROPDOWN (CACHE)
        // ======================
        $districtList = Cache::remember(
            'welfare_district_'.$survey_year,
            3600,
            function () use ($survey_year) {
                return DB::table('household_surveys_2564')
                    ->when($survey_year !== '', fn($q) => $q->where('survey_Year', $survey_year))
                    ->whereNotNull('survey_District')->where('survey_District','!=','')
                    ->distinct()->orderBy('survey_District')
                    ->pluck('survey_District');
            }
        );

        $subdistrictList = Cache::remember(
            'welfare_subdistrict_'.$survey_year.'_'.$district,
            3600,
            function () use ($survey_year, $district) {
                return DB::table('household_surveys_2564')
                    ->when($survey_year !== '', fn($q) => $q->where('survey_Year', $survey_year))
                    ->when($district !== '', fn($q) => $q->where('survey_District', $district))
                    ->whereNotNull('survey_Subdistrict')->where('survey_Subdistrict','!=','')
                    ->distinct()->orderBy('survey_Subdistrict')
                    ->pluck('survey_Subdistrict');
            }
        );

        // ======================
        // SUBQUERY HOUSE ✅ รวม: พื้นที่/ปี + โทร + พิกัด + ที่อยู่
        // ======================
        $houseSub = DB::table('household_surveys_2564')
            ->select(
                'house_Id',
                'survey_District',
                'survey_Subdistrict',
                'survey_Year',

                // ✅ เบอร์โทรผู้ให้ข้อมูล
                'survey_Informer_phone',

                // ✅ พิกัด
                'latitude',
                'longitude',

                // ✅ ที่อยู่
                'house_Number',
                'village_No',
                'village_Name',
                'survey_Postcode'
            )
            ->whereNotNull('house_Id')
            ->where('house_Id','!=','')
            ->distinct();

        // ======================
        // BASE TABLES
        // ======================
        $fallbackTables = ['human_capital_2564'];

        // ✅ helper: COALESCE ค่าแรกที่ "ไม่ว่าง"
        $coalesce = function(string $col) use ($fallbackTables) {
            $parts = ["NULLIF(TRIM(h.$col),'')"];
            for ($i=1; $i<count($fallbackTables); $i++) {
                $parts[] = "NULLIF(TRIM(h{$i}.$col),'')";
            }
            return "COALESCE(".implode(',', $parts).")";
        };

        $allowedCols = ['a7_1','a7_2','a7_3','a7_4','a7_5','a7_6'];
        $picked      = array_values(array_intersect($welfare_type, $allowedCols));
        $YES_VALUES  = ['ได้รับ']; // (คงไว้ เผื่อใช้ต่อ)

        // ✅ helper: อย่างน้อย 1 ช่อง = ได้รับ
        $anyReceivedRaw = function(array $cols) use ($coalesce) {
            return '(' . implode(' OR ', array_map(
                fn($c) => "TRIM({$coalesce($c)}) = 'ได้รับ'",
                $cols
            )) . ')';
        };

        // ✅ helper: ไม่มีช่องไหนได้รับเลย
        $noneReceivedRaw = function(array $cols) use ($coalesce) {
            return '(' . implode(' AND ', array_map(
                fn($c) => "(NULLIF(TRIM({$coalesce($c)}),'') IS NULL OR TRIM({$coalesce($c)}) <> 'ได้รับ')",
                $cols
            )) . ')';
        };

        // ✅ helper: OR/AND เฉพาะ "ช่องที่เลือก" (กรองจริง)
        $receivedCondition = function(array $cols, string $mode) use ($coalesce) {
            if (empty($cols)) return null;

            $conds = array_map(
                fn($c) => "TRIM({$coalesce($c)}) = 'ได้รับ'",
                $cols
            );

            return '(' . implode($mode === 'all' ? ' AND ' : ' OR ', $conds) . ')';
        };

        // ======================
        // MAIN QUERY (ROWS)
        // ======================
        $q = DB::table($fallbackTables[0].' as h')
            ->joinSub($houseSub,'s',fn($j)=>$j->on('s.house_Id','=','h.house_Id'));

        // ✅ raw ที่ใช้บ่อย
        $ageRaw = "TRIM({$coalesce('human_Age_y')})";
        $sexRaw = "TRIM({$coalesce('human_Sex')})";
        $a70    = "TRIM({$coalesce('a7_0')})";
        $a70Empty = "NULLIF($a70,'') IS NULL";

        $q->select([
            'h.house_Id',
            'h.human_Member_title',
            'h.human_Member_fname',
            'h.human_Member_lname',
            'h.human_Member_cid',

            // ✅ ลำดับ
            'h.human_Order',

            's.survey_District',
            's.survey_Subdistrict',
            's.survey_Year',

            // ✅ เบอร์โทร + พิกัด
            's.survey_Informer_phone',
            's.latitude',
            's.longitude',

            // ✅ ที่อยู่ (เพิ่มใหม่)
            's.house_Number',
            's.village_No',
            's.village_Name',
            's.survey_Postcode',

            DB::raw($coalesce('human_Age_y')." as human_Age_y"),
            DB::raw($coalesce('human_Sex')." as human_Sex"),

            DB::raw($coalesce('a7_0')." as a7_0"),
            DB::raw($coalesce('a7_1')." as a7_1"),
            DB::raw($coalesce('a7_2')." as a7_2"),
            DB::raw($coalesce('a7_3')." as a7_3"),
            DB::raw($coalesce('a7_4')." as a7_4"),
            DB::raw($coalesce('a7_5')." as a7_5"),
            DB::raw($coalesce('a7_6')." as a7_6"),
        ]);

        // พื้นที่/ปี
        if ($district !== '')    $q->where('s.survey_District',$district);
        if ($subdistrict !== '') $q->where('s.survey_Subdistrict',$subdistrict);
        if ($survey_year !== '') $q->where('s.survey_Year',$survey_year);

        // อายุ/เพศ  ✅ age_range เป็นหลัก, agey เป็น fallback
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

        // ✅ กรอง welfare
        if ($welfare === 'received') {

            // กลุ่ม "ได้รับ": ถ้า a7_0 ว่าง -> เดาจาก a7_1..a7_6
            $q->where(function($qq) use ($a70, $a70Empty, $allowedCols, $anyReceivedRaw) {
                $qq->whereRaw("$a70 NOT IN ('ใช่','ไม่ได้รับ') AND NULLIF($a70,'') IS NOT NULL")
                   ->orWhereRaw("$a70Empty AND ".$anyReceivedRaw($allowedCols));
            });

            // ✅ กรองจริงตาม OR/AND ของ "ช่องที่เลือก"
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

        // ค้นหาชื่อ/บ้าน/เลขบัตร
        if ($house_id !== '') $q->where('h.house_Id','like',"%{$house_id}%");
        if ($title    !== '') $q->where('h.human_Member_title','like',"%{$title}%");
        if ($fname    !== '') $q->where('h.human_Member_fname','like',"%{$fname}%");
        if ($lname    !== '') $q->where('h.human_Member_lname','like',"%{$lname}%");
        if ($cid      !== '') $q->where('h.human_Member_cid','like',"%{$cid}%");

        $rows = $q->orderBy('s.survey_District')
                  ->orderBy('s.survey_Subdistrict')
                  ->orderBy('h.house_Id')
                  ->paginate(15)
                  ->appends($request->query());

        // ======================
        // COUNTS (CACHE) - ต้องใช้ logic เดียวกับ rows
        // ======================
        $countKey = 'welfare_counts_'.md5(json_encode([
            $district,$subdistrict,$survey_year,
            $house_id,$title,$fname,$lname,$cid,
            $agey,$age_range,$sex,
            $picked,$welfare_match
        ]));

        $counts = Cache::remember($countKey, 300, function () use (
            $houseSub,$district,$subdistrict,$survey_year,
            $house_id,$title,$fname,$lname,$cid,$agey,$age_range,$sex,
            $a70,$a70Empty,$allowedCols,$anyReceivedRaw,$noneReceivedRaw,
            $picked,$welfare_match,$receivedCondition,
            $ageRaw,$sexRaw,$parseAgeRange
        ) {
            $base = DB::table('human_capital_2564 as h')
                ->joinSub($houseSub,'s',fn($j)=>$j->on('s.house_Id','=','h.house_Id'))
                ->when($district !== '', fn($q)=>$q->where('s.survey_District',$district))
                ->when($subdistrict !== '', fn($q)=>$q->where('s.survey_Subdistrict',$subdistrict))
                ->when($survey_year !== '', fn($q)=>$q->where('s.survey_Year',$survey_year));

            if ($house_id !== '') $base->where('h.house_Id','like',"%{$house_id}%");
            if ($title    !== '') $base->where('h.human_Member_title','like',"%{$title}%");
            if ($fname    !== '') $base->where('h.human_Member_fname','like',"%{$fname}%");
            if ($lname    !== '') $base->where('h.human_Member_lname','like',"%{$lname}%");
            if ($cid      !== '') $base->where('h.human_Member_cid','like',"%{$cid}%");

            // อายุ/เพศ ✅ age_range เป็นหลัก, agey fallback
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

            // ✅ กรองจริงตาม OR/AND ของ "ช่องที่เลือก"
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
