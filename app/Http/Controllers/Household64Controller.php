<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Household64Controller extends Controller
{
    public function index(Request $request)
    {
        // ======================
        // INPUTS
        // ======================
        $q           = trim((string) $request->get('q', ''));
        $survey_year = trim((string) $request->get('survey_year', ''));

        $district    = trim((string) $request->get('district', ''));
        $subdistrict = trim((string) $request->get('subdistrict', ''));
        $village     = trim((string) $request->get('village', ''));

        $house_id    = trim((string) $request->get('house_id', ''));
        $cid         = trim((string) $request->get('cid', ''));

        $has_book    = trim((string) $request->get('has_book', ''));

        $agri_no     = trim((string) $request->get('agri_no', ''));
        $house_no    = trim((string) $request->get('house_no', ''));
        $village_no  = trim((string) $request->get('village_no', ''));

        $province    = trim((string) $request->get('province', ''));
        $postcode    = trim((string) $request->get('postcode', ''));

        $title       = trim((string) $request->get('title', ''));
        $fname       = trim((string) $request->get('fname', ''));
        $lname       = trim((string) $request->get('lname', ''));

        // ======================
        // YEARS
        // ======================
        $ALL_YEARS = [2564, 2565, 2566, 2567, 2568];
        $yearList  = $ALL_YEARS;

        // ✅ normalize survey_year: ว่าง/ไม่ถูกต้อง = ทุกปี
        if (!ctype_digit($survey_year) || !in_array((int)$survey_year, $ALL_YEARS, true)) {
            $survey_year = '';
        }

        $yearsToUse = $survey_year !== '' ? [(int)$survey_year] : $ALL_YEARS;

        // ======================
        // ✅ UNION SQL (กันกรณีตารางบางปีไม่มี)
        // ======================
        $unionSqlParts = [];
        foreach ($yearsToUse as $y) {
            $hh = "household_surveys_{$y}";
            $hm = "human_capital_{$y}";

            if (!Schema::hasTable($hh)) {
                continue; // ข้ามปีที่ไม่มีตาราง household
            }

            // human อาจไม่มี ก็ยัง join ได้โดยใช้ subquery ว่าง ๆ (แต่ในที่นี้ง่ายสุดคือ เช็คมีตารางก่อน)
            $humanJoin = Schema::hasTable($hm)
                ? "LEFT JOIN {$hm} h
                      ON h.house_Id = s.house_Id
                     AND h.human_Member_cid = s.survey_Householder_cid"
                : "LEFT JOIN (SELECT NULL AS house_Id, NULL AS human_Member_cid, NULL AS human_Age_y, NULL AS human_Sex) h
                      ON 1=0";

            $unionSqlParts[] = "
                SELECT
                    {$y} AS survey_Year,
                    s.house_Id,
                    s.survey_Has_agri_book,
                    s.survey_Agri_household_no,
                    s.house_Number,
                    s.village_No,
                    s.village_Name,
                    s.survey_Subdistrict,
                    s.survey_District,
                    s.survey_Province,
                    s.survey_Postcode,
                    s.survey_Householder_title,
                    s.survey_Householder_fname,
                    s.survey_Householder_lname,
                    s.survey_Householder_cid,
                    h.human_Age_y,
                    h.human_Sex
                FROM {$hh} s
                {$humanJoin}
                WHERE s.house_Id IS NOT NULL AND s.house_Id <> ''
            ";
        }

        // ถ้าไม่มีตารางเลย (กันหน้าว่างพัง)
        if (empty($unionSqlParts)) {
            $surveys = collect([]); // หรือทำ paginator ว่างก็ได้
            return view('household_64', compact(
                'surveys',
                'q','survey_year','district','subdistrict','village','house_id','cid','has_book',
                'agri_no','house_no','village_no','province','postcode','title','fname','lname',
                'yearList'
            ));
        }

        $unionSql = implode(" UNION ALL ", $unionSqlParts);
        $base = DB::query()->fromRaw("({$unionSql}) as u");

        // ======================
        // DROPDOWN LISTS (ไม่ใช้ Cache กันค้าง)
        // ======================
        $districtList = (clone $base)
            ->whereNotNull('u.survey_District')->where('u.survey_District', '<>', '')
            ->distinct()->orderBy('u.survey_District')
            ->pluck('u.survey_District');

        $subdistrictList = (clone $base)
            ->when($district !== '', fn($qq) => $qq->where('u.survey_District', $district))
            ->whereNotNull('u.survey_Subdistrict')->where('u.survey_Subdistrict', '<>', '')
            ->distinct()->orderBy('u.survey_Subdistrict')
            ->pluck('u.survey_Subdistrict');

        $villageList = (clone $base)
            ->when($district !== '', fn($qq) => $qq->where('u.survey_District', $district))
            ->when($subdistrict !== '', fn($qq) => $qq->where('u.survey_Subdistrict', $subdistrict))
            ->whereNotNull('u.village_Name')->where('u.village_Name', '<>', '')
            ->distinct()->orderBy('u.village_Name')
            ->pluck('u.village_Name');

        // ======================
        // FILTERS
        // ======================
        if ($district !== '')    $base->where('u.survey_District', $district);
        if ($subdistrict !== '') $base->where('u.survey_Subdistrict', $subdistrict);
        if ($village !== '')     $base->where('u.village_Name', $village);

        if ($house_id !== '')    $base->where('u.house_Id', 'like', "%{$house_id}%");
        if ($cid !== '')         $base->where('u.survey_Householder_cid', 'like', "%{$cid}%");

        if ($agri_no !== '')     $base->where('u.survey_Agri_household_no', 'like', "%{$agri_no}%");
        if ($house_no !== '')    $base->where('u.house_Number', 'like', "%{$house_no}%");
        if ($village_no !== '')  $base->where('u.village_No', 'like', "%{$village_no}%");

        if ($province !== '')    $base->where('u.survey_Province', 'like', "%{$province}%");
        if ($postcode !== '')    $base->where('u.survey_Postcode', 'like', "%{$postcode}%");

        if ($title !== '')       $base->where('u.survey_Householder_title', 'like', "%{$title}%");
        if ($fname !== '')       $base->where('u.survey_Householder_fname', 'like', "%{$fname}%");
        if ($lname !== '')       $base->where('u.survey_Householder_lname', 'like', "%{$lname}%");

        if ($has_book === '1') {
            $base->whereRaw("LOWER(TRIM(COALESCE(u.survey_Has_agri_book,''))) IN ('1','y','yes','มี')");
        } elseif ($has_book === '0') {
            $base->whereRaw("LOWER(TRIM(COALESCE(u.survey_Has_agri_book,''))) NOT IN ('1','y','yes','มี')");
        }

        if ($q !== '') {
            $base->where(function ($qq) use ($q) {
                $qq->where('u.house_Id', 'like', "%{$q}%")
                   ->orWhere('u.survey_Householder_fname', 'like', "%{$q}%")
                   ->orWhere('u.survey_Householder_lname', 'like', "%{$q}%")
                   ->orWhere('u.survey_Householder_cid', 'like', "%{$q}%")
                   ->orWhere('u.village_Name', 'like', "%{$q}%")
                   ->orWhere('u.survey_Subdistrict', 'like', "%{$q}%")
                   ->orWhere('u.survey_District', 'like', "%{$q}%")
                   ->orWhere('u.house_Number', 'like', "%{$q}%")
                   ->orWhere('u.village_No', 'like', "%{$q}%")
                   ->orWhere('u.survey_Postcode', 'like', "%{$q}%");
            });
        }

        // ======================
        // PAGINATION
        // ======================
        $surveys = $base
            ->orderByDesc('u.survey_Year')
            ->orderBy('u.house_Id')
            ->paginate(25)
            ->appends($request->query());

        return view('household_64', compact(
            'surveys',
            'q','survey_year','district','subdistrict','village','house_id','cid','has_book',
            'agri_no','house_no','village_no','province','postcode','title','fname','lname',
            'districtList','subdistrictList','villageList',
            'yearList'
        ));
    }
}
