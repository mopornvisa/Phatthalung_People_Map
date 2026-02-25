<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class CapitalsController extends Controller
{
    private array $YEARS = [2564,2565,2566,2567,2568];

    public function index(Request $request)
    {
        $mode = (string) $request->get('mode', 'year'); // year|trend
        if (!in_array($mode, ['year','trend'], true)) $mode = 'year';

        $year = (int) $request->get('year', 2568);
        if (!in_array($year, $this->YEARS, true)) $year = 2568;

        $district    = trim((string) $request->get('district', ''));
        $subdistrict = trim((string) $request->get('subdistrict', ''));
        $q           = trim((string) $request->get('q', ''));

        // ===== helpers: หา column ที่มีจริง =====
        $colPick = function (string $table, array $candidates): ?string {
            foreach ($candidates as $c) {
                if (SchemaHasColumn($table, $c)) return $c;
            }
            return null;
        };

        // ===== ฟังก์ชัน base query ต่อปี =====
        $buildBaseQuery = function (int $y) use ($district, $subdistrict, $q, $colPick) {
            $table = "total_capital_data_{$y}";

            $districtCol = $colPick($table, ['district', 'survey_District', 'District']);
            $subdistCol  = $colPick($table, ['subdistrict', 'survey_Subdistrict', 'Subdistrict']);
            $houseCol    = $colPick($table, ['house_id', 'house_Id', 'houseID', 'HouseId']);
            $cidCol      = $colPick($table, ['cid', 'CID', 'citizen_id']);

            $qb = DB::table($table);

            // filters: district/subdistrict
            if ($district !== '' && $districtCol) {
                $qb->where($districtCol, $district);
            }
            if ($subdistrict !== '' && $subdistCol) {
                $qb->where($subdistCol, $subdistrict);
            }

            // q: ค้นหาใน house_id / cid ถ้ามี
            if ($q !== '') {
                $qb->where(function ($w) use ($q, $houseCol, $cidCol, $districtCol, $subdistCol) {
                    $like = "%{$q}%";
                    $did = false;

                    if ($houseCol) { $w->orWhere($houseCol, 'like', $like); $did = true; }
                    if ($cidCol)   { $w->orWhere($cidCol, 'like', $like);   $did = true; }

                    // เผื่ออยากให้ q ค้นในอำเภอ/ตำบลด้วย
                    if ($districtCol) { $w->orWhere($districtCol, 'like', $like); $did = true; }
                    if ($subdistCol)  { $w->orWhere($subdistCol, 'like', $like);  $did = true; }

                    // กันกรณีไม่มีคอลัมน์อะไรเลย
                    if (!$did) { $w->orWhereRaw('1=1'); }
                });
            }

            return $qb;
        };

        // =========================
        // MODE: YEAR
        // =========================
        if ($mode === 'year') {

            // ดึงรายการ: แสดงปี + 5 ทุน + คะแนนรวม
            $rowsQ = $buildBaseQuery($year)->selectRaw("
                {$year} as year,
                (COALESCE(human_Total,0) + COALESCE(physical_Total,0) + COALESCE(financial_Total,0) + COALESCE(natural_Total,0) + COALESCE(social_Total,0)) as total_score,
                COALESCE(human_Total,0)     as human_score,
                COALESCE(physical_Total,0)  as physical_score,
                COALESCE(financial_Total,0) as financial_score,
                COALESCE(natural_Total,0)   as natural_score,
                COALESCE(social_Total,0)    as social_score
            ");

            $rows = $rowsQ->orderByDesc('total_score')->paginate(20);

            // summary (ค่าเฉลี่ยของปีนี้)
            // ✅ แก้ alias natural -> natural_capital กันชน keyword NATURAL
            $avg = $buildBaseQuery($year)->selectRaw("
                AVG(COALESCE(human_Total,0))     as human,
                AVG(COALESCE(physical_Total,0))  as physical,
                AVG(COALESCE(financial_Total,0)) as financial,
                AVG(COALESCE(natural_Total,0))   as natural_capital,
                AVG(COALESCE(social_Total,0))    as social,
                AVG(
                    COALESCE(human_Total,0)
                  + COALESCE(physical_Total,0)
                  + COALESCE(financial_Total,0)
                  + COALESCE(natural_Total,0)
                  + COALESCE(social_Total,0)
                ) as total
            ")->first();

            $summary = [
                'total'     => (float)($avg->total ?? 0),
                'human'     => (float)($avg->human ?? 0),
                'physical'  => (float)($avg->physical ?? 0),
                'financial' => (float)($avg->financial ?? 0),
                'natural'   => (float)($avg->natural_capital ?? 0), // ✅ map กลับเป็น key natural เหมือนเดิม
                'social'    => (float)($avg->social ?? 0),
            ];

            // ===== SD (ส่วนเบี่ยงเบนมาตรฐาน) ของปีนี้ =====
            // ใช้ STDDEV_POP (ประชากร) | ถ้าต้องการ sample เปลี่ยนเป็น STDDEV_SAMP
            $sd = $buildBaseQuery($year)->selectRaw("
                STDDEV_POP(COALESCE(human_Total,0))     as human,
                STDDEV_POP(COALESCE(physical_Total,0))  as physical,
                STDDEV_POP(COALESCE(financial_Total,0)) as financial,
                STDDEV_POP(COALESCE(natural_Total,0))   as natural_capital,
                STDDEV_POP(COALESCE(social_Total,0))    as social,
                STDDEV_POP(
                    COALESCE(human_Total,0)
                  + COALESCE(physical_Total,0)
                  + COALESCE(financial_Total,0)
                  + COALESCE(natural_Total,0)
                  + COALESCE(social_Total,0)
                ) as total
            ")->first();

            $std = [
                'total'     => (float)($sd->total ?? 0),
                'human'     => (float)($sd->human ?? 0),
                'physical'  => (float)($sd->physical ?? 0),
                'financial' => (float)($sd->financial ?? 0),
                'natural'   => (float)($sd->natural_capital ?? 0),
                'social'    => (float)($sd->social ?? 0),
            ];

            // radar (mean)
            $radar = [
                $summary['human'],
                $summary['physical'],
                $summary['financial'],
                $summary['natural'],
                $summary['social'],
            ];

            // radar (sd) เอาไปทำ tooltip/แสดง mean±sd
            $radarStd = [
                $std['human'],
                $std['physical'],
                $std['financial'],
                $std['natural'],
                $std['social'],
            ];

            // dist ต่ำ/กลาง/สูง
            $distRow = $buildBaseQuery($year)->selectRaw("
                SUM(CASE WHEN (
                    COALESCE(human_Total,0)+COALESCE(physical_Total,0)+COALESCE(financial_Total,0)+COALESCE(natural_Total,0)+COALESCE(social_Total,0)
                ) < 34 THEN 1 ELSE 0 END) as low_n,

                SUM(CASE WHEN (
                    COALESCE(human_Total,0)+COALESCE(physical_Total,0)+COALESCE(financial_Total,0)+COALESCE(natural_Total,0)+COALESCE(social_Total,0)
                ) BETWEEN 34 AND 66 THEN 1 ELSE 0 END) as mid_n,

                SUM(CASE WHEN (
                    COALESCE(human_Total,0)+COALESCE(physical_Total,0)+COALESCE(financial_Total,0)+COALESCE(natural_Total,0)+COALESCE(social_Total,0)
                ) > 66 THEN 1 ELSE 0 END) as high_n
            ")->first();

            $distTotal = [
                'ต่ำ'  => (int)($distRow->low_n ?? 0),
                'กลาง' => (int)($distRow->mid_n ?? 0),
                'สูง'  => (int)($distRow->high_n ?? 0),
            ];

            $trend = []; // ไม่ใช้ใน year

            return view('capitals.index', [
                'mode'       => $mode,
                'year'       => $year,
                'district'   => $district,
                'subdistrict'=> $subdistrict,
                'q'          => $q,
                'YEARS'      => $this->YEARS,
                'rows'       => $rows,
                'summary'    => $summary,
                'std'        => $std,        // ✅ เพิ่ม
                'radar'      => $radar,
                'radarStd'   => $radarStd,   // ✅ เพิ่ม
                'distTotal'  => $distTotal,
                'trend'      => $trend,
            ]);
        }

        // =========================
        // MODE: TREND (5 ปี)
        // =========================
        $trend = [];
        foreach ($this->YEARS as $y) {

            // AVG
            $avg = $buildBaseQuery($y)->selectRaw("
                COUNT(*) as n,
                AVG(COALESCE(human_Total,0))     as human,
                AVG(COALESCE(physical_Total,0))  as physical,
                AVG(COALESCE(financial_Total,0)) as financial,
                AVG(COALESCE(natural_Total,0))   as natural_capital,
                AVG(COALESCE(social_Total,0))    as social,
                AVG(
                    COALESCE(human_Total,0)
                  + COALESCE(physical_Total,0)
                  + COALESCE(financial_Total,0)
                  + COALESCE(natural_Total,0)
                  + COALESCE(social_Total,0)
                ) as total
            ")->first();

            // SD
            $sd = $buildBaseQuery($y)->selectRaw("
                STDDEV_POP(COALESCE(human_Total,0))     as human,
                STDDEV_POP(COALESCE(physical_Total,0))  as physical,
                STDDEV_POP(COALESCE(financial_Total,0)) as financial,
                STDDEV_POP(COALESCE(natural_Total,0))   as natural_capital,
                STDDEV_POP(COALESCE(social_Total,0))    as social,
                STDDEV_POP(
                    COALESCE(human_Total,0)
                  + COALESCE(physical_Total,0)
                  + COALESCE(financial_Total,0)
                  + COALESCE(natural_Total,0)
                  + COALESCE(social_Total,0)
                ) as total
            ")->first();

            $trend[$y] = [
                'n'         => (int)($avg->n ?? 0),
                'total'     => (float)($avg->total ?? 0),
                'human'     => (float)($avg->human ?? 0),
                'physical'  => (float)($avg->physical ?? 0),
                'financial' => (float)($avg->financial ?? 0),
                'natural'   => (float)($avg->natural_capital ?? 0),
                'social'    => (float)($avg->social ?? 0),

                // ✅ SD ต่อปี
                'sd_total'     => (float)($sd->total ?? 0),
                'sd_human'     => (float)($sd->human ?? 0),
                'sd_physical'  => (float)($sd->physical ?? 0),
                'sd_financial' => (float)($sd->financial ?? 0),
                'sd_natural'   => (float)($sd->natural_capital ?? 0),
                'sd_social'    => (float)($sd->social ?? 0),
            ];
        }

        // summary ในโหมด trend: ใช้ “ปีล่าสุด”
        $latestYear = 2568;
        $latest = $trend[$latestYear] ?? [
            'total'=>0,'human'=>0,'physical'=>0,'financial'=>0,'natural'=>0,'social'=>0,
            'sd_total'=>0,'sd_human'=>0,'sd_physical'=>0,'sd_financial'=>0,'sd_natural'=>0,'sd_social'=>0
        ];

        $summary = [
            'total'     => (float)($latest['total'] ?? 0),
            'human'     => (float)($latest['human'] ?? 0),
            'physical'  => (float)($latest['physical'] ?? 0),
            'financial' => (float)($latest['financial'] ?? 0),
            'natural'   => (float)($latest['natural'] ?? 0),
            'social'    => (float)($latest['social'] ?? 0),
        ];

        // ✅ SD ของปีล่าสุด (ให้ Blade ใช้เหมือนโหมด year)
        $std = [
            'total'     => (float)($latest['sd_total'] ?? 0),
            'human'     => (float)($latest['sd_human'] ?? 0),
            'physical'  => (float)($latest['sd_physical'] ?? 0),
            'financial' => (float)($latest['sd_financial'] ?? 0),
            'natural'   => (float)($latest['sd_natural'] ?? 0),
            'social'    => (float)($latest['sd_social'] ?? 0),
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

        // distTotal ใน trend: ยังไม่คำนวณ (ถ้าต้องการให้คิดจากปีล่าสุด บอกได้)
        $distTotal = ['ต่ำ'=>0,'กลาง'=>0,'สูง'=>0];

        // ✅ rows ว่าง ๆ แบบไม่พัง paginate
        $rows = new LengthAwarePaginator([], 0, 20, 1, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        return view('capitals.index', [
            'mode'       => $mode,
            'year'       => $year,
            'district'   => $district,
            'subdistrict'=> $subdistrict,
            'q'          => $q,
            'YEARS'      => $this->YEARS,
            'rows'       => $rows,
            'summary'    => $summary,
            'std'        => $std,       // ✅ เพิ่ม
            'radar'      => $radar,
            'radarStd'   => $radarStd,  // ✅ เพิ่ม
            'distTotal'  => $distTotal,
            'trend'      => $trend,
        ]);
    }
}

/**
 * helper: เช็คคอลัมน์มีจริง
 */
function SchemaHasColumn(string $table, string $column): bool
{
    try {
        return Schema::hasColumn($table, $column);
    } catch (\Throwable $e) {
        return false;
    }
}
