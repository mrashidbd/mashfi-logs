<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Carbon\Carbon;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dailyReport()
    {
        // "Admin dashboard needs a daily report view showing logs surveyed per shift."
        $inspections = Inspection::with(['log', 'surveyor'])
            ->orderBy('verified_at', 'desc')
            ->get()
            ->groupBy(function ($inspection) {
                return Carbon::parse($inspection->verified_at)->format('Y-m-d');
            });

        $dailyReport = $inspections->map(function ($dayInspections, $date) {
            $shifts = $dayInspections->groupBy('shift')->map(function ($shiftInspections, $shift) {
                return [
                    'shift' => $shift,
                    'total_logs' => $shiftInspections->count(),
                    'total_volume' => $shiftInspections->sum(function ($ins) {
                        return $ins->actual_vol_cbm ?? $ins->log->vol_cbm;
                    }),
                    'match_count' => $shiftInspections->where('is_match', true)->count(),
                    'mismatch_count' => $shiftInspections->where('is_match', false)->count(),
                    // Optionally, include surveyors active in this shift
                    'surveyors' => $shiftInspections->pluck('surveyor.name')->unique()->values(),
                ];
            });

            return [
                'date' => $date,
                'total_logs' => $dayInspections->count(),
                'total_volume' => $dayInspections->sum(function ($ins) {
                    return $ins->actual_vol_cbm ?? $ins->log->vol_cbm;
                }),
                'shifts' => $shifts->values(),
            ];
        })->values();

        return Inertia::render('Admin/DailyReport', [
            'reports' => $dailyReport,
        ]);
    }
}
