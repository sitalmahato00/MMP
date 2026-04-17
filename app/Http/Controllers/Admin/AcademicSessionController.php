<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        return view('admin.academic-sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('admin.academic-sessions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100|unique:academic_sessions',
            'start_date' => 'required|string|max:10',
            'end_date'   => 'required|string|max:10',
            'is_current' => 'boolean',
        ]);

        $data['start_date'] = NepaliDateHelper::toAD($data['start_date']);
        $data['end_date']   = NepaliDateHelper::toAD($data['end_date']);

        if (!empty($data['is_current'])) {
            AcademicSession::where('is_current', true)->update(['is_current' => false]);
        }

        AcademicSession::create($data);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', "Session '{$data['name']}' created.");
    }

    public function edit(AcademicSession $academicSession)
    {
        return view('admin.academic-sessions.edit', ['session' => $academicSession]);
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        $data = $request->validate([
            'name'       => "required|string|max:100|unique:academic_sessions,name,{$academicSession->id}",
            'start_date' => 'required|string|max:10',
            'end_date'   => 'required|string|max:10',
        ]);

        $data['start_date'] = NepaliDateHelper::toAD($data['start_date']);
        $data['end_date']   = NepaliDateHelper::toAD($data['end_date']);

        $academicSession->update($data);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session updated.');
    }

    public function destroy(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_current, 403, 'Cannot delete the active session.');
        $academicSession->delete();
        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session deleted.');
    }

    public function setCurrent(AcademicSession $academicSession)
    {
        AcademicSession::where('is_current', true)->update(['is_current' => false]);
        $academicSession->update(['is_current' => true]);
        return back()->with('success', "'{$academicSession->name}' is now the active session.");
    }
}
