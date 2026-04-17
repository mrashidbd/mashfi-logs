<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Log;
use App\Models\User;
use App\Models\Vessel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function welcome()
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();
        $totalLogs = Log::count();
        $totalSurveyed = Inspection::count();
        $totalBuyers = Log::whereNotNull('buyer_name')->distinct()->pluck('buyer_name')->count();
        $totalVessels = Vessel::count();

        return Inertia::render('Admin/Welcome', [
            'activeVessel' => $activeVessel,
            'stats' => [
                'total_logs' => $totalLogs,
                'total_surveyed' => $totalSurveyed,
                'total_buyers' => $totalBuyers,
                'total_vessels' => $totalVessels,
            ],
        ]);
    }

    public function surveyData(Request $request)
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        if (! $activeVessel) {
            return Inertia::render('Admin/SurveyData/Index', [
                'vessel' => null,
                'logs' => null,
                'filters' => [],
                'species_list' => [],
                'origins_list' => [],
                'buyers_list' => [],
                'surveyors_list' => [],
            ]);
        }

        $query = $activeVessel->logs()->with('inspection.surveyor');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('log_no', 'like', "%{$search}%")
                    ->orWhere('tag_no', 'like', "%{$search}%");
            });
        }

        // Species filter
        if ($request->filled('species')) {
            $query->where('species', $request->input('species'));
        }

        // Origin filter
        if ($request->filled('origin')) {
            $query->where('origin', $request->input('origin'));
        }

        // Buyer filter
        if ($request->filled('buyer')) {
            $query->where('buyer_name', $request->input('buyer'));
        }

        // Survey status filter
        if ($request->filled('survey_status')) {
            if ($request->input('survey_status') === 'surveyed') {
                $query->whereHas('inspection');
            } elseif ($request->input('survey_status') === 'not_surveyed') {
                $query->doesntHave('inspection');
            }
        }

        // Match status filter
        if ($request->filled('match_status')) {
            if ($request->input('match_status') === 'matched') {
                $query->whereHas('inspection', fn ($q) => $q->where('is_match', true));
            } elseif ($request->input('match_status') === 'mismatched') {
                $query->whereHas('inspection', fn ($q) => $q->where('is_match', false));
            }
        }

        // Surveyor filter
        if ($request->filled('surveyor')) {
            $query->whereHas('inspection', function ($q) use ($request) {
                $q->where('surveyor_id', $request->input('surveyor'));
            });
        }

        return Inertia::render('Admin/SurveyData/Index', [
            'vessel' => $activeVessel,
            'logs' => $query->paginate(50)->withQueryString(),
            'filters' => $request->all(['search', 'species', 'origin', 'buyer', 'survey_status', 'match_status', 'surveyor']),
            'species_list' => $activeVessel->logs()->distinct()->pluck('species'),
            'origins_list' => $activeVessel->logs()->whereNotNull('origin')->distinct()->pluck('origin'),
            'buyers_list' => $activeVessel->logs()->whereNotNull('buyer_name')->distinct()->pluck('buyer_name'),
            'surveyors_list' => User::where('role', 'surveyor')->select('id', 'name')->get(),
        ]);
    }

    public function dailyReport(Request $request)
    {
        $query = Inspection::with(['log.vessel', 'surveyor']);

        // Filter by surveyor
        if ($request->filled('surveyor')) {
            $query->where('surveyor_id', $request->input('surveyor'));
        }

        // Filter by buyer (via log relationship)
        if ($request->filled('buyer')) {
            $query->whereHas('log', fn ($q) => $q->where('buyer_name', $request->input('buyer')));
        }

        // Filter by species (via log relationship)
        if ($request->filled('species')) {
            $query->whereHas('log', fn ($q) => $q->where('species', $request->input('species')));
        }

        // Filter by origin (via log relationship)
        if ($request->filled('origin')) {
            $query->whereHas('log', fn ($q) => $q->where('origin', $request->input('origin')));
        }

        $inspections = $query
            ->orderBy('verified_at', 'desc')
            ->get()
            ->groupBy(fn ($inspection) => Carbon::parse($inspection->verified_at)->format('Y-m-d'));

        $dailyReport = $inspections->map(function ($dayInspections, $date) {
            $shifts = $dayInspections->groupBy('shift')->map(function ($shiftInspections, $shift) {
                return [
                    'shift' => $shift,
                    'total_logs' => $shiftInspections->count(),
                    'total_volume' => round($shiftInspections->sum(fn ($ins) => $ins->actual_vol_cbm ?? $ins->log->vol_cbm), 3),
                    'match_count' => $shiftInspections->where('is_match', true)->count(),
                    'mismatch_count' => $shiftInspections->where('is_match', false)->count(),
                    'surveyors' => $shiftInspections->pluck('surveyor.name')->unique()->values(),
                ];
            });

            return [
                'date' => $date,
                'total_logs' => $dayInspections->count(),
                'total_volume' => round($dayInspections->sum(fn ($ins) => $ins->actual_vol_cbm ?? $ins->log->vol_cbm), 3),
                'shifts' => $shifts->values(),
            ];
        })->values();

        // Overview stats (totals across ALL filtered data)
        $allFilteredInspections = $query->get();
        $overview = [
            'total_unloaded' => $allFilteredInspections->count(),
            'total_days' => $inspections->count(),
            'total_shifts' => $inspections->flatMap(fn ($day) => $day->pluck('shift'))->unique()->count(),
            'total_buyers' => $allFilteredInspections->pluck('log.buyer_name')->filter()->unique()->count(),
            'total_volume' => round($allFilteredInspections->sum(fn ($ins) => $ins->actual_vol_cbm ?? $ins->log->vol_cbm), 3),
        ];

        // Filter option lists
        $surveyors = User::where('role', 'surveyor')->select('id', 'name')->get();
        $buyers = Log::whereNotNull('buyer_name')->distinct()->pluck('buyer_name');
        $species = Log::distinct()->pluck('species');
        $origins = Log::whereNotNull('origin')->distinct()->pluck('origin');

        return Inertia::render('Admin/DailyReport', [
            'reports' => $dailyReport,
            'overview' => $overview,
            'filters' => $request->all(['surveyor', 'buyer', 'species', 'origin']),
            'surveyors_list' => $surveyors,
            'buyers_list' => $buyers,
            'species_list' => $species,
            'origins_list' => $origins,
        ]);
    }

    public function downloadPdf(Vessel $vessel)
    {
        $logs = $vessel->logs()->with('inspection.surveyor')->get();

        $pdf = Pdf::loadView('reports.admin-vessel-pdf', [
            'vessel' => $vessel,
            'logs' => $logs,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('vessel-report-'.$vessel->id.'.pdf');
    }

    public function surveyDataPdf()
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        if (! $activeVessel) {
            return back()->with('error', 'No active vessel found.');
        }

        $logs = $activeVessel->logs()->with('inspection.surveyor')->get();

        $pdf = Pdf::loadView('reports.admin-vessel-pdf', [
            'vessel' => $activeVessel,
            'logs' => $logs,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('survey-data-'.$activeVessel->id.'.pdf');
    }
}
