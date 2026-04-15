<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with('academicSession')->latest()->paginate(20);
        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        $currentSession = AcademicSession::where('is_current', true)->first();
        return view('admin.exams.create', compact('sessions', 'currentSession'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'marks_open'          => 'boolean',
        ]);

        $data['is_published'] = false;
        $data['marks_open'] = $request->has('marks_open');

        Exam::create($data);

        return redirect()->route('admin.exams.index')->with('success', 'Exam created.');
    }

    public function show(Exam $exam)
    {
        $exam->load('academicSession');
        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $sessions = AcademicSession::orderByDesc('start_date')->get();
        return view('admin.exams.edit', compact('exam', 'sessions'));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'marks_open'          => 'boolean',
        ]);

        $data['marks_open'] = $request->has('marks_open');
        $exam->update($data);

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted.');
    }

    public function publish(Exam $exam)
    {
        $exam->update(['is_published' => true]);
        return redirect()->route('admin.exams.index')->with('success', "Exam '{$exam->name}' is now published.");
    }
}
