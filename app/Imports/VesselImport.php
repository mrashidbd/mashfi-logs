<?php

namespace App\Imports;

use App\Models\Log;
use App\Models\Vessel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class VesselImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $vessels = [];

        foreach ($rows as $row) {
            if (!isset($row['vessel_code'])) continue;

            $vesselCode = trim($row['vessel_code']);
            if (!$vesselCode) continue;

            // Cache creation to avoid query per row
            if (!isset($vessels[$vesselCode])) {
                $vessels[$vesselCode] = Vessel::firstOrCreate(
                    ['vessel_code' => $vesselCode],
                    [
                        'vessel_name' => 'Mashfi-' . now()->format('Y-m'),
                        'arrival_date' => now()
                    ]
                );
            }

            $vessel = $vessels[$vesselCode];

            Log::create([
                'vessel_id' => $vessel->id,
                'serial_no' => $row['sl_no'] ?? null,
                'tag_no' => $row['tag_no'],
                'log_no' => $row['log_no'] ?? null,
                'species' => $row['species'],
                'origin' => $row['origin'] ?? null,
                'length' => $row['length'],
                'girth_butt' => $row['gb'] ?? null,
                'girth_top' => $row['pb'] ?? null,
                'diameter' => $row['diameter'] ?? null,
                'vol_cbm' => $row['volume_cbm'] ?? 0,
                'l_ref' => $row['l_ref'] ?? null,
                'd_ref' => $row['d_ref'] ?? null,
                'buyer_name' => $row['buyers_name'] ?? null,
                'remarks' => $row['remarks'] ?? null,
            ]);
        }
    }
}
