<?php

namespace App\Imports;

use App\Models\Log;
use App\Models\Vessel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VesselImport implements ToCollection, WithCalculatedFormulas, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $vesselName = 'Mashfi-'.now()->format('Y-m');
        $vesselCode = 'IMP-'.now()->format('YmdHis');

        $vessel = Vessel::firstOrCreate(
            ['vessel_name' => $vesselName],
            [
                'vessel_code' => $vesselCode,
                'arrival_date' => now(),
            ]
        );

        foreach ($rows as $row) {
            // Support both new format (df10_tagno) and old format (df10_tag / tag_no)
            $tagNo = $row['df10_tagno'] ?? $row['df10_tag'] ?? $row['tag_no'] ?? null;
            if (! $tagNo) {
                continue;
            }

            $buyerNameStr = $row['buyer'] ?? $row['buyers_name'] ?? null;

            Log::create([
                'vessel_id' => $vessel->id,
                'serial_no' => $row['sn'] ?? $row['sl_no'] ?? null,
                'mark' => $row['mark'] ?? null,
                'tag_no' => $tagNo,
                'log_no' => $row['logno'] ?? $row['log_no'] ?? null,
                'species' => $row['species'] ?? '',
                'origin' => $row['origiin'] ?? $row['origin'] ?? null,
                'length' => $row['length'] ?? 0,
                'girth_butt' => $row['gb'] ?? null,
                'girth_top' => $row['pb'] ?? null,
                'diameter' => $row['dia'] ?? $row['diameter'] ?? null,
                'vol_cbm' => $row['volumecbm'] ?? $row['volume_cbm'] ?? $row['vol'] ?? 0,
                'l_ref' => $row['lref'] ?? $row['l_ref'] ?? null,
                'd_ref' => $row['dref'] ?? $row['dia_ref'] ?? $row['d_ref'] ?? null,
                'length_after_ref' => $row['calclength'] ?? $row['length_after_ref'] ?? null,
                'buyer_name' => $buyerNameStr,
                'remarks' => $row['remarks'] ?? null,
            ]);
        }
    }
}
