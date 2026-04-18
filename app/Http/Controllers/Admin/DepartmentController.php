<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());

        $departments = Department::query()
            ->select(['id', 'name', 'code', 'description', 'photo', 'hod_id', 'is_active', 'created_at'])
            ->with(['hod:id,name'])
            ->withCount('programs')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('hod', fn ($hod) => $hod->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.departments.index', [
            'departments' => $departments,
            'search' => $search,
        ]);
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
            'photo'       => 'nullable|image|max:2048',
            'syllabus'    => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('departments', 'public');
        }

        if ($request->hasFile('syllabus')) {
            $data['syllabus'] = $request->file('syllabus')
                ->store('departments/syllabi', 'public');
        }

        Department::create($data);
        PublicDataService::invalidate('*');

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
            'photo'       => 'nullable|image|max:2048',
            'syllabus'    => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            if ($department->photo) {
                Storage::disk('public')->delete($department->photo);
            }
            $data['photo'] = $request->file('photo')
                ->store('departments', 'public');
        }

        if ($request->hasFile('syllabus')) {
            if ($department->syllabus) {
                Storage::disk('public')->delete($department->syllabus);
            }
            $data['syllabus'] = $request->file('syllabus')
                ->store('departments/syllabi', 'public');
        }

        $department->update($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$department->name}' updated.");
    }

    public function destroy(Department $department)
    {
        if ($department->photo) {
            Storage::disk('public')->delete($department->photo);
        }
        if ($department->syllabus) {
            Storage::disk('public')->delete($department->syllabus);
        }
        $department->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}
