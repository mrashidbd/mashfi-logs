<?php

namespace App\Http\Controllers;

use App\Imports\VesselImport;
use App\Models\Log;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class VesselController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Vessels/Index', [
            'vessels' => Vessel::withCount('logs')->latest()->get(),
        ]);
    }

    public function show(Vessel $vessel)
    {
        $query = $vessel->logs()->with('inspection.surveyor');

        // Search filter (LOG# and DF10-TAG only)
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('log_no', 'like', "%{$search}%")
                    ->orWhere('tag_no', 'like', "%{$search}%");
            });
        }

        // Species filter
        if (request('species')) {
            $query->where('species', request('species'));
        }

        // Origin filter
        if (request('origin')) {
            $query->where('origin', request('origin'));
        }

        // Buyer filter
        if (request('buyer')) {
            $query->where('buyer_name', request('buyer'));
        }

        // Survey status filter: surveyed / not_surveyed
        if (request('survey_status')) {
            if (request('survey_status') === 'surveyed') {
                $query->whereHas('inspection');
            } elseif (request('survey_status') === 'not_surveyed') {
                $query->doesntHave('inspection');
            }
        }

        // Match status filter: matched / mismatched
        if (request('match_status')) {
            if (request('match_status') === 'matched') {
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', true);
                });
            } elseif (request('match_status') === 'mismatched') {
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', false);
                });
            }
        }

        return Inertia::render('Admin/Vessels/Show', [
            'vessel' => $vessel,
            'logs' => $query->paginate(50)->withQueryString(),
            'filters' => request()->all(['search', 'species', 'origin', 'buyer', 'survey_status', 'match_status']),
            'species_list' => $vessel->logs()->distinct()->pluck('species'),
            'origins_list' => $vessel->logs()->whereNotNull('origin')->distinct()->pluck('origin'),
            'buyers_list' => $vessel->logs()->whereNotNull('buyer_name')->distinct()->pluck('buyer_name'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'vessel_name' => 'required|string|max:255',
        ]);

        Excel::import(new VesselImport($request->input('vessel_name')), $request->file('file'));

        return redirect()->back()->with('success', 'Vessel log imported successfully.');
    }

    public function update(Request $request, Vessel $vessel)
    {
        $validated = $request->validate([
            'vessel_name' => 'sometimes|required|string|max:255',
            'arrival_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:open,closed',
            'surveyor_access' => 'sometimes|boolean',
            'buyer_access' => 'sometimes|boolean',
        ]);

        // Enforce "Admin can enable access for any ONE/Single vessel at a given time" for Surveyor?
        // User said: "But there shuold not be enabled access for two vessel at any given time."
        if ($request->has('surveyor_access') && $request->surveyor_access) {
            // Disable access for all other vessels
            Vessel::where('id', '!=', $vessel->id)->update(['surveyor_access' => false]);
        }

        $vessel->update($validated);

        return redirect()->back();
    }

    public function destroy(Vessel $vessel)
    {
        $vessel->delete(); // Cascades logs

        return redirect()->back();
    }

    // Log Management Methods
    public function storeLog(Request $request, Vessel $vessel)
    {
        $validated = $request->validate([
            'serial_no' => 'required|string',
            'log_no' => 'nullable|string',
            'tag_no' => 'required|string',
            'species' => 'required|string',
            'origin' => 'nullable|string',
            'length' => 'required|numeric',
            'girth_butt' => 'nullable|numeric',
            'girth_top' => 'nullable|numeric',
            'diameter' => 'nullable|numeric',
            'l_ref' => 'nullable|numeric',
            'd_ref' => 'nullable|numeric',
            'calc_length' => 'nullable|numeric',
            'vol_cbm' => 'required|numeric',
            'buyer_name' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $vessel->logs()->create($validated);

        return back()->with('success', 'Log added successfully.');
    }

    public function updateLog(Request $request, Log $log)
    {
        $validated = $request->validate([
            'serial_no' => 'required|string',
            'log_no' => 'nullable|string',
            'tag_no' => 'required|string',
            'species' => 'required|string',
            'origin' => 'nullable|string',
            'length' => 'required|numeric',
            'girth_butt' => 'nullable|numeric',
            'girth_top' => 'nullable|numeric',
            'diameter' => 'nullable|numeric',
            'l_ref' => 'nullable|numeric',
            'd_ref' => 'nullable|numeric',
            'calc_length' => 'nullable|numeric',
            'vol_cbm' => 'required|numeric',
            'buyer_name' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $log->update($validated);

        return back()->with('success', 'Log updated successfully.');
    }

    public function destroyLog(Log $log)
    {
        $log->delete();

        return back()->with('success', 'Log deleted successfully.');
    }
}
