<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Vessel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BuyerController extends Controller
{
    public function dashboard()
    {
        $buyerName = Auth::user()->name;

        $dailyData = Log::whereHas('inspection')
            ->where(function ($q) use ($buyerName) {
                $q->where('buyer_name', $buyerName)
                    ->orWhereHas('inspection', function ($q2) use ($buyerName) {
                        $q2->where('buyer_name', $buyerName);
                    });
            })
            ->with(['vessel', 'inspection'])
            ->get()
            ->groupBy(function ($log) {
                return \Carbon\Carbon::parse($log->inspection->verified_at)->format('Y-m-d');
            })
            ->map(function ($logs, $date) {
                return [
                    'date' => $date,
                    'total_logs' => $logs->count(),
                    // Always use original vol_cbm for buyer
                    'total_volume' => $logs->sum('vol_cbm'),
                    'logs' => $logs->values(),
                ];
            })
            ->values()
            ->sortByDesc('date')
            ->values();

        return Inertia::render('Buyer/Dashboard', [
            'dailyData' => $dailyData,
        ]);
    }

    public function index(Request $request)
    {
        $buyerName = Auth::user()->name;

        // Find active vessel with buyer access
        $activeVessel = Vessel::where('buyer_access', true)->first();

        if (! $activeVessel) {
            return Inertia::render('Buyer/Index', [
                'vessel' => null,
                'logs' => null,
                'filters' => [],
                'species_list' => [],
                'origins_list' => [],
                'stats' => null,
            ]);
        }

        $query = $activeVessel->logs()
            ->where(function ($q) use ($buyerName) {
                $q->where('buyer_name', $buyerName);
            })
            ->with(['inspection' => function ($q) {
                // Only select non-sensitive fields for buyer
                $q->select('id', 'log_id', 'images', 'verified_at');
            }]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('log_no', 'like', "%{$search}%")
                    ->orWhere('tag_no', 'like', "%{$search}%");
            });
        }

        // Unloaded status filter
        if ($request->filled('unload_status')) {
            if ($request->input('unload_status') === 'unloaded') {
                $query->whereHas('inspection');
            } elseif ($request->input('unload_status') === 'not_unloaded') {
                $query->doesntHave('inspection');
            }
        }

        // Species filter
        if ($request->filled('species')) {
            $query->where('species', $request->input('species'));
        }

        // Origin filter
        if ($request->filled('origin')) {
            $query->where('origin', $request->input('origin'));
        }

        // Get stats for the buyer
        $totalLogs = $activeVessel->logs()->where('buyer_name', $buyerName)->count();
        $unloadedLogs = $activeVessel->logs()->where('buyer_name', $buyerName)->whereHas('inspection')->count();
        $totalVolume = $activeVessel->logs()->where('buyer_name', $buyerName)->sum('vol_cbm');

        return Inertia::render('Buyer/Index', [
            'vessel' => [
                'id' => $activeVessel->id,
                'vessel_name' => $activeVessel->vessel_name ?? 'Shipment #'.$activeVessel->id,
                'arrival_date' => $activeVessel->arrival_date,
            ],
            'logs' => $query->paginate(50)->withQueryString(),
            'filters' => $request->all(['search', 'unload_status', 'species', 'origin']),
            'species_list' => $activeVessel->logs()->where('buyer_name', $buyerName)->distinct()->pluck('species'),
            'origins_list' => $activeVessel->logs()->where('buyer_name', $buyerName)->whereNotNull('origin')->distinct()->pluck('origin'),
            'stats' => [
                'total' => $totalLogs,
                'unloaded' => $unloadedLogs,
                'total_volume' => round($totalVolume, 3),
            ],
        ]);
    }

    public function show(Vessel $vessel)
    {
        if (! $vessel->buyer_access) {
            abort(403);
        }

        $buyerName = Auth::user()->name;

        $logs = $vessel->logs()
            ->where(function ($q) use ($buyerName) {
                $q->where('buyer_name', $buyerName);
            })
            ->whereHas('inspection')
            ->with(['inspection' => function ($q) {
                // Only select non-sensitive fields for buyer
                $q->select('id', 'log_id', 'images', 'verified_at');
            }])
            ->paginate(100);

        return Inertia::render('Buyer/Show', [
            'vessel' => [
                'id' => $vessel->id,
                'vessel_name' => $vessel->vessel_name ?? 'Shipment #'.$vessel->id,
                'arrival_date' => $vessel->arrival_date,
                'is_complete' => $vessel->logs()->where('buyer_name', $buyerName)->count() == $vessel->logs()->where('buyer_name', $buyerName)->whereHas('inspection')->count(),
            ],
            'logs' => $logs,
        ]);
    }

    public function downloadPdf(Vessel $vessel)
    {
        if (! $vessel->buyer_access) {
            abort(403);
        }

        $buyerName = Auth::user()->name;

        $logs = $vessel->logs()
            ->where('buyer_name', $buyerName)
            ->get();

        $pdf = Pdf::loadView('reports.vessel-pdf', [
            'vessel' => $vessel,
            'logs' => $logs,
            'buyerName' => $buyerName,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('shipment-report-'.$vessel->id.'.pdf');
    }
}
