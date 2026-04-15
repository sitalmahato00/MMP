<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('hod')->withCount('programs')->latest()->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $teachers = User::role('teacher')->orderBy('name')->get();
        return view('admin.departments.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:departments',
            'code'        => 'required|string|max:20|unique:departments',
            'hod_id'      => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')
                ->store('departments', 'public');
        }

        Department::create($data);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$data['name']}' created.");
    }

    public function show(Department $department)
    {
        $department->load('hod', 'programs');
        return view('admin.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $teachers = User::role('teacher')->orderBy('name')->get();
        return view('admin.departments.edit', compact('department', 'teachers'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255', "unique:departments,name,{$department->id}"],
            'code'        => ['required','string','max:20',  "unique:departments,code,{$department->id}"],
            'hod_id'      => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($department->cover_image_path) {
                Storage::disk('public')->delete($department->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')
                ->store('departments', 'public');
        }

        $department->update($data);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$department->name}' updated.");
    }

    public function destroy(Department $department)
    {
        if ($department->cover_image_path) {
            Storage::disk('public')->delete($department->cover_image_path);
        }
        $department->delete();
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}
