<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BuyerController extends Controller
{
    public function dashboard()
    {
        // Buyer sees list of vessels they have access to
        $vessels = Vessel::where('buyer_access', true)
            ->withCount(['logs', 'logs as surveyed_logs_count' => function ($query) {
                $query->whereHas('inspection');
            }])
            ->get()
            ->map(function ($vessel) {
                return [
                    'id' => $vessel->id,
                    'vessel_name' => $vessel->vessel_name ?? 'Shipment #' . $vessel->id, // Hide Code
                    'arrival_date' => $vessel->arrival_date,
                    'logs_count' => $vessel->logs_count,
                    'surveyed_logs_count' => $vessel->surveyed_logs_count,
                    'is_complete' => $vessel->logs_count > 0 && $vessel->logs_count == $vessel->surveyed_logs_count,
                ];
            });

        return Inertia::render('Buyer/Dashboard', [
            'vessels' => $vessels,
        ]);
    }

    public function show(Vessel $vessel)
    {
        if (!$vessel->buyer_access) {
            abort(403);
        }

        // "if only 3,4,5 items is surveyed, the buyer will only see the reprot up until that point."
        // So we only load logs that have inspections?
        // "buyer, should only be allowed to see the survey/inspected report"
        $logs = $vessel->logs()
            ->whereHas('inspection') // Only show inspected
            ->with('inspection')
            ->paginate(100);

        // Hide vessel_code in the view logic (handled by front-end passing vessel object masked or generic)

        return Inertia::render('Buyer/Show', [
            'vessel' => [
                'id' => $vessel->id,
                'vessel_name' => $vessel->vessel_name ?? 'Shipment #' . $vessel->id,
                'arrival_date' => $vessel->arrival_date,
                'is_complete' => $vessel->logs()->count() == $vessel->logs()->whereHas('inspection')->count(),
            ],
            'logs' => $logs,
        ]);
    }

    public function downloadPdf(Vessel $vessel)
    {
        if (!$vessel->buyer_access) {
            abort(403);
        }

        // "if all the items in a particular vessel is surveyed, then buyer can see them all and only then he should be able to download the entire report as PDF."
        $total = $vessel->logs()->count();
        $surveyed = $vessel->logs()->whereHas('inspection')->count();

        if ($surveyed < $total) {
            return back()->with('error', 'Report generation available only after full vessel survey completion.');
        }

        $logs = $vessel->logs()->with('inspection')->get();

        $pdf = Pdf::loadView('reports.vessel-pdf', [
            'vessel' => $vessel,
            'logs' => $logs,
            'hide_code' => true, // Flag to hide sensitive info
        ]);

        return $pdf->download('shipment-report-' . $vessel->id . '.pdf');
    }
}
