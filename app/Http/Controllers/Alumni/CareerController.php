<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniEmployment;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        $alumnus = auth()->user()->alumnus;
        $alumnus?->load('employmentHistory');
        return view('alumni.career', compact('alumnus'));
    }

    public function storeEmployment(Request $request)
    {
        $alumnus = auth()->user()->alumnus;
        abort_unless($alumnus, 403);

        $validated = $request->validate([
            'job_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $alumnus->employmentHistory()->create($validated);

        // Update current job if marked as current
        if (!empty($validated['is_current'])) {
            $alumnus->update([
                'current_job' => $validated['job_title'],
                'company_name' => $validated['company_name'],
                'work_location' => $validated['location'] ?? null,
                'employment_status' => 'employed',
            ]);
        }

        return back()->with('success', 'Employment record added.');
    }

    public function destroyEmployment(AlumniEmployment $employment)
    {
        $alumnus = auth()->user()->alumnus;
        abort_unless($alumnus && $employment->alumni_id === $alumnus->id, 403);

        $employment->delete();
        return back()->with('success', 'Employment record removed.');
    }
}
