<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\HelpRecord; // ✅ เพิ่ม

class HousingPhysicalController extends Controller
{
    private array $YEARS = [2564,2565,2566,2567,2568];

    private function pickCol(string $table, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            $c = trim($c);
            if ($c !== '' && Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    /**
     * UNION ALL หลายปี แล้ว wrap เป็น subquery alias u
     * คืนค่า: [ $q, $meta ]
     */
    private function baseQuery(Request $request): array
    {
        $district    = (string) $request->get('district', '');
        $subdistrict = (string) $request->get('subdistrict', '');
        $surveyYear  = (string) $request->get('survey_year', '');

        $years = $this->YEARS;
        if ($surveyYear !== '' && in_array((int)$surveyYear, $this->YEARS, true)) {
            $years = [(int)$surveyYear];
        }

        $queries = [];

        foreach ($years as $y) {
            $hTable = "household_surveys_{$y}";
            $pTable = "physical_capital_{$y}";

            if (!Schema::hasTable($hTable) || !Schema::hasTable($pTable)) continue;

            // ===== household columns =====
            $districtCol    = $this->pickCol($hTable, ['survey_District','district','amphoe','amphoe_name','district_name','amp','amp_name']);
            $subdistrictCol = $this->pickCol($hTable, ['survey_Subdistrict','subdistrict','tambon','tambon_name','subdistrict_name','tam','tam_name']);

            $yearCol     = $this->pickCol($hTable, ['survey_Year','survey_year','year']);
            $phoneCol    = $this->pickCol($hTable, ['survey_Informer_phone','survey_informer_phone','phone']);
            $postcodeCol = $this->pickCol($hTable, ['survey_Postcode','survey_postcode','postcode']);

            $houseNoCol     = $this->pickCol($hTable, ['house_Number','house_number','house_no']);
            $villageNoCol   = $this->pickCol($hTable, ['village_No','village_no','moo','moo_no']);
            $villageNameCol = $this->pickCol($hTable, ['village_Name','village_name','village']);

            $provinceCol = $this->pickCol($hTable, ['survey_Province','province','province_name']);

            $latCol = $this->pickCol($hTable, ['latitude','lat','Latitude']);
            $lngCol = $this->pickCol($hTable, ['longitude','lng','lon','long','Longitude']);

            $bookCol   = $this->pickCol($hTable, ['survey_Has_agri_book','has_agri_book','agri_book']);
            $agriNoCol = $this->pickCol($hTable, ['survey_Agri_household_no','agri_household_no']);
            $titleCol  = $this->pickCol($hTable, ['survey_Householder_title','householder_title']);
            $fnameCol  = $this->pickCol($hTable, ['survey_Householder_fname','householder_fname']);
            $lnameCol  = $this->pickCol($hTable, ['survey_Householder_lname','householder_lname']);
            $cidCol    = $this->pickCol($hTable, ['survey_Householder_cid','householder_cid']);

            // ===== physical columns =====
            $houseConditionCol = $this->pickCol($pTable, [
                'phys_House_condition',
                'physical_House_condition',
                'physical_Housing_condition',
                'physical_Living_conditions',
                'physical_living_conditions',
                'physical_Living_condition',
                'physical_House_status',
                'physical_Living_status',
            ]);

            $sanitationCol = $this->pickCol($pTable, [
                'physical_Housing_sanitation','physical_housing_sanitation',
                'physical_Sanitation','physical_Toilet','physical_toilet',
            ]);

            $roadAccessCol = $this->pickCol($pTable, [
                'physical_Road_access_type','physical_road_access_type',
                'physical_Road_access','physical_road_access',
            ]);

            $drainageCol = $this->pickCol($pTable, [
                'physical_Drainage','physical_drainage','drainage'
            ]);

            $electricCol = $this->pickCol($pTable, ['phys_Electricity','physical_Electric','physical_electric','electric']);
            $mobileCol   = $this->pickCol($pTable, ['phys_Mobilephone','physical_Mobilephone','physical_mobilephone','mobilephone','mobile_phone','phone']);

            $homeRouteCol = $this->pickCol($pTable, ['phys_Home_route','physical_Home_route','home_route','homeRoute']);
            $other121Col  = $this->pickCol($pTable, ['phys_Other121','physical_Other121','physical_other121','other121','other_121']);

            $altEnergyCol = $this->pickCol($pTable, ['phys_Alternative_energy','physical_Alternative_energy','alternative_energy','alt_energy']);
            $waterCol     = $this->pickCol($pTable, ['physical_Water','physical_water','water']);

            $waterSupplyCol  = $this->pickCol($pTable, ['phys_Water_supply']);
            $waterSourcesCol = $this->pickCol($pTable, ['phys_Water_sources']);
            $buyWaterCol     = $this->pickCol($pTable, ['phys_Buy_water']);

            $wasteCol = $this->pickCol($pTable, ['physical_Waste','physical_waste','waste']);

            $qy = DB::table("$pTable as p")
                ->leftJoin("$hTable as h", 'h.house_Id', '=', 'p.house_Id')
                ->select([
                    DB::raw((int)$y . " as data_year"),

                    // ✅ ใช้ชื่อเดียวกันเสมอ (ห้ามมี house_id ซ้ำ)
                    'p.house_Id as house_Id',

                    // household (ชื่อหลักที่ใช้ใน view)
                    DB::raw(($districtCol ? "h.`$districtCol`" : "NULL") . " as survey_District"),
                    DB::raw(($subdistrictCol ? "h.`$subdistrictCol`" : "NULL") . " as survey_Subdistrict"),
                    DB::raw(($yearCol ? "h.`$yearCol`" : (int)$y) . " as survey_Year"),

                    DB::raw(($provinceCol ? "h.`$provinceCol`" : "NULL") . " as survey_Province"),
                    DB::raw(($postcodeCol ? "h.`$postcodeCol`" : "NULL") . " as survey_Postcode"),
                    DB::raw(($phoneCol ? "h.`$phoneCol`" : "NULL") . " as survey_Informer_phone"),

                    DB::raw(($houseNoCol ? "h.`$houseNoCol`" : "NULL") . " as house_Number"),
                    DB::raw(($villageNoCol ? "h.`$villageNoCol`" : "NULL") . " as village_No"),
                    DB::raw(($villageNameCol ? "h.`$villageNameCol`" : "NULL") . " as village_Name"),

                    DB::raw(($latCol ? "h.`$latCol`" : "NULL") . " as latitude"),
                    DB::raw(($lngCol ? "h.`$lngCol`" : "NULL") . " as longitude"),

                    DB::raw(($bookCol ? "h.`$bookCol`" : "NULL") . " as survey_Has_agri_book"),
                    DB::raw(($agriNoCol ? "h.`$agriNoCol`" : "NULL") . " as survey_Agri_household_no"),
                    DB::raw(($titleCol ? "h.`$titleCol`" : "NULL") . " as survey_Householder_title"),
                    DB::raw(($fnameCol ? "h.`$fnameCol`" : "NULL") . " as survey_Householder_fname"),
                    DB::raw(($lnameCol ? "h.`$lnameCol`" : "NULL") . " as survey_Householder_lname"),
                    DB::raw(($cidCol ? "h.`$cidCol`" : "NULL") . " as survey_Householder_cid"),

                    // physical
                    DB::raw(($houseConditionCol ? "p.`$houseConditionCol`" : "NULL") . " as house_condition"),
                    DB::raw(($sanitationCol ? "p.`$sanitationCol`" : "NULL") . " as sanitation"),
                    DB::raw(($roadAccessCol ? "p.`$roadAccessCol`" : "NULL") . " as road_access"),
                    DB::raw(($drainageCol ? "p.`$drainageCol`" : "NULL") . " as drainage"),
                    DB::raw(($electricCol ? "p.`$electricCol`" : "NULL") . " as electric"),
                    DB::raw(($mobileCol ? "p.`$mobileCol`" : "NULL") . " as mobile"),
                    DB::raw(($homeRouteCol ? "p.`$homeRouteCol`" : "NULL") . " as home_route"),
                    DB::raw(($other121Col ? "p.`$other121Col`" : "NULL") . " as other121"),
                    DB::raw(($altEnergyCol ? "p.`$altEnergyCol`" : "NULL") . " as alternative_energy"),
                    DB::raw(($waterCol ? "p.`$waterCol`" : "NULL") . " as water"),
                    DB::raw(($waterSupplyCol ? "p.`$waterSupplyCol`" : "NULL") . " as water_supply"),
                    DB::raw(($waterSourcesCol ? "p.`$waterSourcesCol`" : "NULL") . " as water_sources"),
                    DB::raw(($buyWaterCol ? "p.`$buyWaterCol`" : "NULL") . " as buy_water"),
                    DB::raw(($wasteCol ? "p.`$wasteCol`" : "NULL") . " as waste"),
                ]);

            if ($district !== '' && $districtCol)       $qy->where("h.$districtCol", $district);
            if ($subdistrict !== '' && $subdistrictCol) $qy->where("h.$subdistrictCol", $subdistrict);

            $queries[] = $qy;
        }

        if (empty($queries)) {
            $empty = DB::query()->fromRaw("(select null as house_Id) as u")->whereRaw("1=0");
            return [$empty, ['years'=>$years]];
        }

        $union = array_shift($queries);
        foreach ($queries as $qq) $union->unionAll($qq);

        $q = DB::query()->fromSub($union, 'u');

        return [$q, ['years'=>$years]];
    }

    private function score(array $r): int
    {
        $score = 0;

        if (($r['house_condition'] ?? '') === 'ทรุดโทรม') $score += 30;

        if (($r['sanitation'] ?? '') === 'ไม่มี') $score += 20;
        if (($r['sanitation'] ?? '') === 'ไม่มาตรฐาน') $score += 12;

        if (($r['drainage'] ?? '') === 'ไม่มี') $score += 15;

        if (($r['electric'] ?? '') === 'ต่อพ่วง') $score += 10;
        if (($r['electric'] ?? '') === 'ไม่มี') $score += 15;

        if (($r['water'] ?? '') === 'ไม่เพียงพอ') $score += 10;
        if (($r['road_access'] ?? '') === 'ยาก') $score += 10;
        if (($r['waste'] ?? '') === 'ไม่เหมาะสม') $score += 10;

        return min(100, $score);
    }

    private function level(int $score): array
    {
        if ($score >= 75) return ['label'=>'ด่วนมาก','badge'=>'bg-danger'];
        if ($score >= 50) return ['label'=>'ด่วน','badge'=>'bg-warning text-dark'];
        if ($score >= 25) return ['label'=>'เฝ้าระวัง','badge'=>'bg-info text-dark'];
        return ['label'=>'ปกติ','badge'=>'bg-success'];
    }

    /**
     * ✅ ทำให้ key ที่ view ใช้ "มีเสมอ" ทุกหน้า (กัน Undefined array key)
     */
    private function normalizeRow(array $r): array
    {
        $r['house_id']     = $r['house_id']     ?? ($r['house_Id'] ?? null);
        $r['district']     = $r['district']     ?? ($r['survey_District'] ?? null);
        $r['subdistrict']  = $r['subdistrict']  ?? ($r['survey_Subdistrict'] ?? null);
        $r['village_no']   = $r['village_no']   ?? ($r['village_No'] ?? null);
        $r['village_name'] = $r['village_name'] ?? ($r['village_Name'] ?? null);
        $r['lat']          = $r['lat']          ?? ($r['latitude'] ?? null);
        $r['lng']          = $r['lng']          ?? ($r['longitude'] ?? null);
        return $r;
    }

    private function enrichCollection($rows)
    {
        return collect($rows)->map(function($r){
            $r = $this->normalizeRow((array)$r);

            $s = $this->score($r);
            $lvl = $this->level($s);

            $r['score'] = $s;
            $r['level'] = $lvl['label'];
            $r['badge'] = $lvl['badge'];

            return $r;
        });
    }

    public function dashboard(Request $request)
    {
        [$q] = $this->baseQuery($request);

        $houseId = trim((string) $request->get('house_id', ''));
        if ($houseId !== '') {
            $q->where('u.house_Id', 'like', "%{$houseId}%");
        }

        $raw  = $q->limit(100000)->get();
        $coll = $this->enrichCollection($raw)->sortByDesc('score')->values();

        $kpi = [
            'total'      => $coll->count(),
            'urgent'     => $coll->where('score', '>=', 75)->count(),
            'poor_house' => $coll->where('house_condition', 'ทรุดโทรม')->count(),
            'no_toilet'  => $coll->where('sanitation', 'ไม่มี')->count(),
            'no_drain'   => $coll->where('drainage', 'ไม่มี')->count(),
            'bad_waste'  => $coll->where('waste', 'ไม่เหมาะสม')->count(),
            'elec_risk'  => $coll->filter(fn($x)=>in_array(($x['electric'] ?? ''), ['ต่อพ่วง','ไม่มี'], true))->count(),
            'water_short'=> $coll->where('water', 'ไม่เพียงพอ')->count(),
        ];

        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $items   = $coll->slice(($page - 1) * $perPage, $perPage)->values();

        $rows = new LengthAwarePaginator(
            $items,
            $coll->count(),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        // ✅ สร้าง map: house_Id => status (สถานะล่าสุด)
        $houseIds = collect($items)->pluck('house_Id')->filter()->unique()->values()->all();

        $helpStatusMap = HelpRecord::whereIn('house_Id', $houseIds)
            ->orderByDesc('action_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('house_Id')
            ->map(fn($g) => optional($g->first())->status)
            ->toArray();

        return view('housing.dashboard', [
            'kpi'     => $kpi,
            'rows'    => $rows,
            'houseId' => $houseId,
            'helpStatusMap' => $helpStatusMap, // ✅ ส่งไป blade
        ]);
    }

    public function map(Request $request)
    {
        [$q] = $this->baseQuery($request);

        $q->whereNotNull('u.latitude')->whereNotNull('u.longitude');

        $pins = $q->limit(50000)->get();
        $pins = $this->enrichCollection($pins)->values()->all();

        return view('housing.map', compact('pins'));
    }

    public function cases(Request $request)
    {
        $levelFilter = (string) $request->get('level', '');

        [$q, $meta] = $this->baseQuery($request);

        $rows = $q->limit(100000)->get();
        $rows = $this->enrichCollection($rows)->values()->all();

        if ($levelFilter !== '') {
            $rows = array_values(array_filter($rows, fn($x)=>($x['level'] ?? '') === $levelFilter));
        }

        usort($rows, fn($a,$b)=>($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        $districts = [];
        $subdistricts = [];

        foreach (($meta['years'] ?? $this->YEARS) as $y) {
            $hTable = "household_surveys_{$y}";
            if (!Schema::hasTable($hTable)) continue;

            $dCol = $this->pickCol($hTable, ['survey_District','district','amphoe','amphoe_name','district_name','amp','amp_name']);
            $sCol = $this->pickCol($hTable, ['survey_Subdistrict','subdistrict','tambon','tambon_name','subdistrict_name','tam','tam_name']);

            if ($dCol) $districts = array_merge($districts, DB::table($hTable)->whereNotNull($dCol)->distinct()->pluck($dCol)->toArray());
            if ($sCol) $subdistricts = array_merge($subdistricts, DB::table($hTable)->whereNotNull($sCol)->distinct()->pluck($sCol)->toArray());
        }

        $districts = array_values(array_unique(array_filter($districts, fn($x)=>$x!==null && $x!=='')));
        sort($districts);

        $subdistricts = array_values(array_unique(array_filter($subdistricts, fn($x)=>$x!==null && $x!=='')));
        sort($subdistricts);

        return view('housing.cases', [
            'filtered' => $rows,
            'districts' => $districts,
            'subdistricts' => $subdistricts,
            'district' => (string)$request->get('district',''),
            'subdistrict' => (string)$request->get('subdistrict',''),
            'level' => $levelFilter,
        ]);
    }

    public function show(string $houseId)
    {
        [$q] = $this->baseQuery(request());

        $row = $q->where('u.house_Id', $houseId)->first();
        abort_if(!$row, 404);

        // ✅ normalize ให้ view ใช้ key ได้ชัวร์
        $house = $this->normalizeRow((array)$row);
        $house['house_id'] = $house['house_id'] ?? $houseId; // กันสุดท้าย

        $score = $this->score($house);
        $lvl = $this->level($score);

        // ✅ ดึงสถานะล่าสุดจาก help_records
        $latestHelp = HelpRecord::where('house_Id', $houseId)
            ->orderByDesc('action_date')
            ->orderByDesc('id')
            ->first();

        $helpStatus = $latestHelp->status ?? null;

        return view('housing.show', [
            'house'      => $house,
            'score'      => $score,
            'level'      => $lvl['label'],
            'badge'      => $lvl['badge'],

            // ✅ ส่ง status ไปใช้ใน blade
            'helpStatus' => $helpStatus,
            'latestHelp' => $latestHelp,
        ]);
    }
}
