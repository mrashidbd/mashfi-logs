<?php

namespace App\Imports;

use App\Models\Log;
use App\Models\Vessel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log as Logger;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VesselImport implements ToCollection, WithCalculatedFormulas, WithHeadingRow
{
    public function __construct(
        public string $vesselName = ''
    ) {}

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $vesselName = $this->vesselName ?: 'Vessel-'.now()->format('Y-m-d');
        $vesselCode = 'IMP-'.now()->format('YmdHis');

        $vessel = Vessel::create([
            'vessel_code' => $vesselCode,
            'vessel_name' => $vesselName,
            'arrival_date' => now(),
        ]);

        foreach ($rows as $row) {
            try {
                // Support both new format (df10_tagno) and old format (df10_tag / tag_no)
                $tagNo = $row['df10_tagno'] ?? $row['df10_tag'] ?? $row['tag_no'] ?? null;
                if (! $tagNo) {
                    continue;
                }

                $tagNo = trim((string) $tagNo);
                if ($tagNo === '') {
                    continue;
                }

                $length = $this->toNumeric($row['length'] ?? null);
                $gb = $this->toNumeric($row['gb'] ?? null);
                $pb = $this->toNumeric($row['pb'] ?? null);
                $lRef = $this->toNumeric($row['lref'] ?? $row['l_ref'] ?? null);
                $dRef = $this->toNumeric($row['dref'] ?? $row['dia_ref'] ?? $row['d_ref'] ?? null);

                // DIA: if formula didn't resolve, calculate from GB + PB
                $dia = $this->toNumeric($row['dia'] ?? $row['diameter'] ?? null);
                if ($dia === null && $gb !== null && $pb !== null) {
                    $dia = floor(($gb + $pb) / 2);
                }

                // CALC.LENGTH: length - l_ref
                $calcLength = $this->toNumeric($row['calclength'] ?? $row['calc_length'] ?? $row['length_after_ref'] ?? null);
                if ($calcLength === null && $length !== null) {
                    $calcLength = $length - ($lRef ?? 0);
                }

                // Volume CBM: dia² × calc_length × 0.7854 / 10000
                $volCbm = $this->toNumeric($row['volumecbm'] ?? $row['volume_cbm'] ?? $row['vol'] ?? null);
                if ($volCbm === null && $dia !== null && $calcLength !== null && $calcLength > 0) {
                    $volCbm = round($dia * $dia * $calcLength * 0.7854 / 10000, 3);
                }

                Log::create([
                    'vessel_id' => $vessel->id,
                    'serial_no' => $row['sn'] ?? $row['sl_no'] ?? null,
                    'log_no' => $row['logno'] ?? $row['log_no'] ?? null,
                    'tag_no' => $tagNo,
                    'species' => trim((string) ($row['species'] ?? '')),
                    'origin' => trim((string) ($row['origiin'] ?? $row['origin'] ?? '')),
                    'length' => $length,
                    'girth_butt' => $gb,
                    'girth_top' => $pb,
                    'diameter' => $dia,
                    'l_ref' => $lRef,
                    'd_ref' => $dRef,
                    'calc_length' => $calcLength,
                    'vol_cbm' => $volCbm,
                    'buyer_name' => trim((string) ($row['buyer'] ?? $row['buyers_name'] ?? '')),
                    'remarks' => $row['remarks'] ?? null,
                ]);
            } catch (\Throwable $e) {
                Logger::warning('VesselImport row skipped: '.$e->getMessage(), [
                    'row' => $row->toArray(),
                ]);

                continue;
            }
        }
    }

    private function toNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        // If it's a string that looks like a formula, skip
        if (is_string($value) && str_starts_with($value, '=')) {
            return null;
        }

        $numeric = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $numeric !== false ? (float) $numeric : null;
    }
}
