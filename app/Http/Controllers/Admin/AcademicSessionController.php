<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\AcademicSession;
use App\Services\SessionService;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function __construct(private SessionService $sessionService)
    {
    }

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
            'is_active'  => 'boolean',
        ]);

        $data['start_date'] = NepaliDateHelper::toAD($data['start_date']);
        $data['end_date']   = NepaliDateHelper::toAD($data['end_date']);
        $activateImmediately = $request->boolean('is_active');

        unset($data['is_active']);

        $academicSession = AcademicSession::create($data);

        if ($activateImmediately) {
            $result = $this->sessionService->switchTo($academicSession);

            if (($result['failed'] ?? 0) > 0) {
                return redirect()->route('admin.academic-sessions.index')
                    ->with('error', 'Session created, but the current session could not be ended automatically. The new session remains saved as upcoming.');
            }

            $message = "Session '{$data['name']}' created and activated.";

            if (($result['converted'] ?? 0) > 0) {
                $message .= " {$result['converted']} final-semester student(s) were moved to alumni.";
            }

            return redirect()->route('admin.academic-sessions.index')
                ->with('success', $message);
        }

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', "Session '{$data['name']}' created.");
    }

    public function edit(AcademicSession $academicSession)
    {
        return view('admin.academic-sessions.edit', ['session' => $academicSession]);
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot edit an ended session.');

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
        abort_if($academicSession->is_active || $academicSession->is_locked, 403, 'Cannot delete an active or ended session.');
        $academicSession->delete();
        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Session deleted.');
    }

    public function setCurrent(AcademicSession $academicSession)
    {
        abort_if($academicSession->is_locked, 403, 'Cannot activate an ended session.');

        $result = $this->sessionService->switchTo($academicSession);

        if (($result['failed'] ?? 0) > 0) {
            return back()->with('error', 'Unable to switch sessions automatically. The current session was not closed cleanly.');
        }

        $message = "'{$academicSession->name}' is now the active session.";

        if (($result['converted'] ?? 0) > 0) {
            $message .= " {$result['converted']} final-semester student(s) were moved to alumni.";
        }

        return back()->with('success', $message);
    }
}
