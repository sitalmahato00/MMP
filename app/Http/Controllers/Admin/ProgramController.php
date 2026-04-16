<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Department;
use App\Services\PublicDataService;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::with('department')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->latest()
            ->paginate(20);

        $departments = Department::orderBy('name')->get();
        return view('admin.programs.index', compact('programs', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'department_id'    => 'required|exists:departments,id',
            'duration_years'   => 'required|integer|min:1|max:6',
            'total_semesters'  => 'required|integer|min:1|max:12',
        ]);

        Program::create($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$data['name']}' created.");
    }

    public function edit(Program $program)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.programs.edit', compact('program', 'departments'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'department_id'   => 'required|exists:departments,id',
            'duration_years'  => 'required|integer|min:1|max:6',
            'total_semesters' => 'required|integer|min:1|max:12',
        ]);

        $program->update($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.programs.index')
            ->with('success', 'Program updated.');
    }

    public function destroy(Program $program)
    {
        $program->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.programs.index')
            ->with('success', 'Program deleted.');
    }
}
