<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SurveyorController extends Controller
{
    public function welcome()
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        return Inertia::render('Surveyor/Welcome', [
            'vessel' => $activeVessel,
        ]);
    }

    public function search()
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        if (! $activeVessel) {
            return redirect()->route('surveyor.welcome');
        }

        return Inertia::render('Surveyor/Search', [
            'vessel' => $activeVessel,
        ]);
    }

    public function searchQuery(Request $request)
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        if (! $activeVessel) {
            return response()->json([]);
        }

        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $activeVessel->logs()
            ->with('inspection')
            ->where(function ($q) use ($query) {
                $q->where('log_no', 'like', "%{$query}%")
                    ->orWhere('tag_no', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    public function dashboard(Request $request)
    {
        $activeVessel = Vessel::where('surveyor_access', true)->first();

        if (! $activeVessel) {
            return Inertia::render('Surveyor/Dashboard', [
                'vessel' => null,
                'logs' => null,
                'filters' => [],
                'buyers_list' => [],
                'species_list' => [],
                'origins_list' => [],
            ]);
        }

        $query = $activeVessel->logs()
            ->with(['inspection' => function ($q) {
                $q->where('surveyor_id', Auth::id());
            }]);

        // Search filter (LOG# and DF10-TAG only)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('log_no', 'like', "%{$search}%")
                    ->orWhere('tag_no', 'like', "%{$search}%");
            });
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
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', true);
                });
            } elseif ($request->input('match_status') === 'mismatched') {
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', false);
                });
            }
        }

        // Buyer filter
        if ($request->filled('buyer')) {
            $query->where('buyer_name', $request->input('buyer'));
        }

        // Species filter
        if ($request->filled('species')) {
            $query->where('species', $request->input('species'));
        }

        // Origin filter
        if ($request->filled('origin')) {
            $query->where('origin', $request->input('origin'));
        }

        return Inertia::render('Surveyor/Dashboard', [
            'vessel' => $activeVessel,
            'logs' => $query->paginate(100)->withQueryString(),
            'filters' => $request->all(['search', 'survey_status', 'match_status', 'buyer', 'species', 'origin']),
            'buyers_list' => $activeVessel->logs()->whereNotNull('buyer_name')->distinct()->pluck('buyer_name'),
            'species_list' => $activeVessel->logs()->distinct()->pluck('species'),
            'origins_list' => $activeVessel->logs()->whereNotNull('origin')->distinct()->pluck('origin'),
        ]);
    }

    public function show(Log $log)
    {
        $log->load('vessel', 'inspection');

        // Ensure log belongs to active vessel
        if (! $log->vessel->surveyor_access) {
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
            'buyer_name' => 'nullable|string',
            'actual_length_ft' => 'required_if:is_match,false|nullable|integer|min:0',
            'actual_length_in' => 'required_if:is_match,false|nullable|numeric|min:0|max:11.9',
            'actual_mid_girth' => 'required_if:is_match,false|nullable|numeric|min:0',
            'butt_image' => 'nullable|image|max:10240',
            'top_image' => 'nullable|image|max:10240',
        ]);

        $actualVolCft = null;
        $actualVolCbm = null;

        if (! $validated['is_match'] && isset($validated['actual_mid_girth']) && isset($validated['actual_length_ft'])) {
            // Convert foot + inch to total feet
            $lengthFt = (int) $validated['actual_length_ft'];
            $lengthIn = (float) ($validated['actual_length_in'] ?? 0);
            $totalLengthFt = $lengthFt + ($lengthIn / 12);

            $midGirth = (float) $validated['actual_mid_girth'];

            // Shonatoni formula: (mid_girth² × length_ft) / 2304 = CFT
            $actualVolCft = ($midGirth * $midGirth * $totalLengthFt) / 2304;
            $actualVolCft = round($actualVolCft, 6);

            // CFT to CBM
            $actualVolCbm = $actualVolCft / 27.74;
            $actualVolCbm = round($actualVolCbm, 6);
        }

        // Handle Images
        $inspection = $log->inspection()->firstOrNew(['log_id' => $log->id]);
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
                'buyer_name' => $validated['buyer_name'] ?? null,
                'actual_length_ft' => $validated['actual_length_ft'] ?? null,
                'actual_length_in' => $validated['actual_length_in'] ?? null,
                'actual_mid_girth' => $validated['actual_mid_girth'] ?? null,
                'actual_vol_cft' => $actualVolCft,
                'actual_vol_cbm' => $actualVolCbm,
                'surveyor_remarks' => $validated['surveyor_remarks'] ?? null,
                'images' => $images,
                'verified_at' => now(),
            ]
        );

        return back()->with('success', 'Log verification saved.');
    }
}
