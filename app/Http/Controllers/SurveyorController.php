<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SurveyorController extends Controller
{
    public function dashboard()
    {
        // "surveyor ... only able to see individual wood/logs/lumber items that was enabled for him by the admin"
        // "Surveyor ... should not be able to see list of vessels of course. He only able to see ... items ... enabled for him"
        // Actually, user said: "If there is none, he should see a message... surveyor then see details of each line"
        // This implies he enters a vessel context first? Or just a flat list of TODOs?
        // "admin should be able to see individual Vessel codes ... and open access for any ONE/Single vessel"

        // So we find the ONE vessel with surveyor_access = true.
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        return Inertia::render('Surveyor/Dashboard', [
            'vessel' => $activeVessel,
            // If active vessel exists, load its logs? Or link to logs? 
            // "He only able to see individual wood/logs/lumber items"
            // Let's load the logs if a vessel is active.
            'logs' => $activeVessel
                ? $activeVessel->logs()
                ->with(['inspection' => function ($q) {
                    $q->where('surveyor_id', Auth::id());
                }])
                ->paginate(100)
                : null,
        ]);
    }
    public function show(Log $log)
    {
        $log->load('vessel', 'inspection');

        // Ensure log belongs to active vessel
        if (!$log->vessel->surveyor_access) {
            abort(403, 'This vessel is not open for survey.');
        }

        return Inertia::render('Surveyor/Inspection', [
            'log' => $log,
            'shifts' => ['SHIFT A (12:00 AM - 08:00 AM)', 'SHIFT B (08:00 AM - 04:00 PM)', 'SHIFT C (04:00 PM - 12:00 AM)'],
        ]);
    }

    public function store(Request $request, Log $log)
    {
        $validated = $request->validate([
            'is_match' => 'required|boolean',
            'shift' => 'required|in:A,B,C',
            'surveyor_remarks' => 'nullable|string',
            'actual_length' => 'required_if:is_match,false|nullable|numeric',
            'actual_gb' => 'required_if:is_match,false|nullable|numeric',
            'actual_pb' => 'required_if:is_match,false|nullable|numeric',
            'actual_diameter' => 'required_if:is_match,false|nullable|numeric',
            'actual_l_ref' => 'nullable|numeric',
            'actual_d_ref' => 'nullable|numeric',
            'buyer_name' => 'nullable|string',
            'butt_image' => 'nullable|image|max:10240', // 10MB max
            'top_image' => 'nullable|image|max:10240',
        ]);

        $actualVol = null;
        if (!$validated['is_match']) {
            $d_m = $validated['actual_diameter'] / 100; // cm to m
            $r_m = $d_m / 2;
            $actualVol = $validated['actual_length'] * pi() * pow($r_m, 2);
            $actualVol = round($actualVol, 6);
        }

        // Handle Images
        $inspection = $log->inspection()->firstOrNew(['log_id' => $log->id]);

        // Model casts 'images' => 'array', so it's already an array or null.
        // No need to json_decode.
        $existingImages = $inspection->images ?? [];

        $images = $existingImages;

        if ($request->hasFile('butt_image')) {
            $path = $request->file('butt_image')->store('inspections', 'public');
            $images['butt'] = $path;
        }

        if ($request->hasFile('top_image')) {
            $path = $request->file('top_image')->store('inspections', 'public');
            $images['top'] = $path;
        }

        // Create or Update Inspection
        $log->inspection()->updateOrCreate(
            ['log_id' => $log->id],
            [
                'surveyor_id' => Auth::id(),
                'shift' => $validated['shift'],
                'is_match' => $validated['is_match'],
                'actual_length' => $validated['actual_length'] ?? null,
                'actual_gb' => $validated['actual_gb'] ?? null,
                'actual_pb' => $validated['actual_pb'] ?? null,
                'actual_diameter' => $validated['actual_diameter'] ?? null,
                'actual_vol_cbm' => $actualVol,
                'actual_l_ref' => $validated['actual_l_ref'] ?? null,
                'actual_d_ref' => $validated['actual_d_ref'] ?? null,
                'buyer_name' => $validated['buyer_name'] ?? null,
                'surveyor_remarks' => $validated['surveyor_remarks'],
                'images' => $images, // Pass array directly, Model casts will handle JSON encoding
                'verified_at' => now(),
            ]
        );

        return to_route('surveyor.dashboard')->with('success', 'Log verification saved.');
    }
}
