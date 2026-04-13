<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\Log;
use App\Imports\VesselImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

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
        // "admin should be able to filter by species, origin, buyers, surveyors, survey status etc."
        // "admin should be able to search by SL, TAG, LOG_Number"
        $query = $vessel->logs()->with('inspection.surveyor');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('serial_no', 'like', "%{$search}%")
                    ->orWhere('tag_no', 'like', "%{$search}%")
                    ->orWhere('log_no', 'like', "%{$search}%");
            });
        }

        if (request('species')) {
            $query->where('species', request('species'));
        }

        if (request('buyer')) {
            $query->where('buyer_name', 'like', "%" . request('buyer') . "%");
        }

        // Status filter: pending (no inspection), matched, mismatched
        if (request('status')) {
            if (request('status') === 'pending') {
                $query->doesntHave('inspection');
            } elseif (request('status') === 'verified') {
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', true);
                });
            } elseif (request('status') === 'mismatch') {
                $query->whereHas('inspection', function ($q) {
                    $q->where('is_match', false);
                });
            }
        }

        return Inertia::render('Admin/Vessels/Show', [
            'vessel' => $vessel,
            'logs' => $query->paginate(50)->withQueryString(),
            'filters' => request()->all(['search', 'species', 'buyer', 'status']),
            // Unique values for dropdowns
            'species_list' => $vessel->logs()->distinct()->pluck('species'),
            'origins_list' => $vessel->logs()->distinct()->pluck('origin'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new VesselImport, $request->file('file'));

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
            // Maybe buyer access too? User said "one for surveyor access one for buyer access".
            // "Admin can enable/open access for any ONE/Single vessel at a given time."
            // This might imply ONLY ONE ACTIVE VESSEL globally.
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
            'tag_no' => 'required|string',
            'log_no' => 'nullable|string',
            'species' => 'required|string',
            'origin' => 'nullable|string',
            'length' => 'required|numeric',
            'girth_butt' => 'required|numeric',
            'girth_top' => 'required|numeric',
            'diameter' => 'required|numeric',
            'vol_cbm' => 'required|numeric',
            'l_ref' => 'nullable|numeric',
            'd_ref' => 'nullable|numeric',
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
            'tag_no' => 'required|string',
            'log_no' => 'nullable|string',
            'species' => 'required|string',
            'origin' => 'nullable|string',
            'length' => 'required|numeric',
            'girth_butt' => 'required|numeric',
            'girth_top' => 'required|numeric',
            'diameter' => 'required|numeric',
            'vol_cbm' => 'required|numeric',
            'l_ref' => 'nullable|numeric',
            'd_ref' => 'nullable|numeric',
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
