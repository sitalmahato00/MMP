<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'program', 'guardian.user'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('admission_number', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn($q) => $q->where('current_semester', $request->semester))
            ->latest()
            ->paginate(20);

        $programs = Program::orderBy('name')->get();
        return view('admin.students.index', compact('students', 'programs'));
    }

    public function create()
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.students.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other',
            'dob'              => 'nullable|date',
            'address'          => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',
            'password'         => 'required|string|min:8',
            'admission_number' => 'required|string|max:50|unique:students,admission_number',
            'dob_bs'           => 'nullable|string|max:15',
            'program_id'       => 'required|exists:programs,id',
            'current_semester' => 'required|integer|min:1|max:10',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => $data['dob'] ?? null,
            'address'   => $data['address'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole('student');

        $student = Student::create([
            'user_id'          => $user->id,
            'admission_number' => $data['admission_number'],
            'dob_bs'           => $data['dob_bs'],
            'program_id'       => $data['program_id'],
            'current_semester' => $data['current_semester'],
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'program', 'guardian.user']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $programs = Program::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'programs'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required', 'email', Rule::unique('users')->ignore($student->user_id)],
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other',
            'dob'              => 'nullable|date',
            'address'          => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',
            'admission_number' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'dob_bs'           => 'nullable|string|max:15',
            'program_id'       => 'required|exists:programs,id',
            'current_semester' => 'required|integer|min:1|max:10',
        ]);

        if ($request->hasFile('avatar')) {
            if ($student->user->avatar && Storage::disk('public')->exists($student->user->avatar)) {
                Storage::disk('public')->delete($student->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $student->user->update([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'address' => $data['address'] ?? null,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        $student->update([
            'admission_number' => $data['admission_number'],
            'dob_bs'           => $data['dob_bs'],
            'program_id'       => $data['program_id'],
            'current_semester' => $data['current_semester'],
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        if ($student->user->avatar && Storage::disk('public')->exists($student->user->avatar)) {
            Storage::disk('public')->delete($student->user->avatar);
        }
        $student->user->delete();
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }
}
