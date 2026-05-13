<?php

namespace App\Modules\Alumni\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\Alumni\Models\AlumniEmployment;
use App\Modules\User\Models\User;
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
            'start_date' => 'nullable|string|max:10', // BS date format
            'end_date' => 'nullable|string|max:10', // BS date format
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Convert BS dates to AD dates
        if (!empty($validated['start_date'])) {
            $adStartDate = \App\Helpers\NepaliDateHelper::toAD($validated['start_date']);
            if (!$adStartDate) {
                return redirect()->back()
                    ->withErrors(['start_date' => 'Invalid BS date format. Please use YYYY-MM-DD format.'])
                    ->withInput();
            }
            $validated['start_date'] = $adStartDate->format('Y-m-d');
        }

        if (!empty($validated['end_date'])) {
            $adEndDate = \App\Helpers\NepaliDateHelper::toAD($validated['end_date']);
            if (!$adEndDate) {
                return redirect()->back()
                    ->withErrors(['end_date' => 'Invalid BS date format. Please use YYYY-MM-DD format.'])
                    ->withInput();
            }
            $validated['end_date'] = $adEndDate->format('Y-m-d');
            
            // Validate end date is after start date
            if (!empty($validated['start_date']) && $validated['end_date'] < $validated['start_date']) {
                return redirect()->back()
                    ->withErrors(['end_date' => 'End date must be after start date.'])
                    ->withInput();
            }
        }

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
