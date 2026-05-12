<?php

namespace App\Modules\Alumni\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni as Alumnus;
use App\Models\{Department, Program, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use Illuminate\Validation\Rule;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumnus::with(['user', 'department', 'program', 'projects']);

        // Filters
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
                  ->orWhere('id', $s);
            });
        }
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        if ($request->filled('graduation_year')) $query->where('graduation_year', $request->graduation_year);
        if ($request->filled('employment_status')) $query->where('employment_status', $request->employment_status);
        if ($request->filled('is_featured')) $query->where('is_featured', $request->is_featured === '1');
        if ($request->filled('has_project')) {
            $query->whereHas('projects', fn($q) => $q->where('type', $request->has_project));
        }

        // KPIs
        $totalAlumni = Alumnus::count();
        $featuredCount = Alumnus::featured()->count();
        $employedCount = Alumnus::where('employment_status', 'employed')->count();
        $employmentRate = $totalAlumni > 0 ? round(($employedCount / $totalAlumni) * 100) : 0;
        $thisYearCount = Alumnus::where('graduation_year', date('Y'))->count();

        $alumni = $query->latest()->paginate(20)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $graduationYears = Alumnus::select('graduation_year')->distinct()->orderByDesc('graduation_year')->pluck('graduation_year');

        return view('admin.alumni.index', compact(
            'alumni', 'departments', 'programs', 'graduationYears',
            'totalAlumni', 'featuredCount', 'employmentRate', 'thisYearCount'
        ));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        return view('admin.alumni.create', compact('departments', 'programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'required|string|min:8',
            'department_id' => 'required|exists:departments,id',
            'program_id' => 'required|exists:programs,id',
            'graduation_year' => 'required|string|max:4',
            'admission_year' => 'nullable|string|max:4',
            'roll_number' => 'nullable|string|max:50',
            'current_job' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'work_location' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string',
            'bio' => 'nullable|string|max:2000',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'achievements' => 'nullable|string|max:2000',
            'is_featured' => 'boolean',
        ]);

        $createdUser = null;

        DB::transaction(function () use ($validated, $request, &$createdUser) {
            $avatarPath = $request->hasFile('avatar')
                ? $request->file('avatar')->store('avatars', 'public')
                : null;

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'avatar' => $avatarPath,
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);
            $user->assignRole('alumni');
            $createdUser = $user;

            Alumnus::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
                'program_id' => $validated['program_id'],
                'graduation_year' => $validated['graduation_year'],
                'admission_year' => $validated['admission_year'] ?? null,
                'roll_number' => $validated['roll_number'] ?? null,
                'current_job' => $validated['current_job'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'work_location' => $validated['work_location'] ?? null,
                'employment_status' => $validated['employment_status'] ?? 'unknown',
                'bio' => $validated['bio'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'github_url' => $validated['github_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'achievements' => $validated['achievements'] ?? null,
                'is_featured' => $validated['is_featured'] ?? false,
                'is_verified' => true,
                'visibility' => 'public',
            ]);
        });

        if ($createdUser) {
            app(\App\Services\PortalNotificationService::class)
                ->sendNewAccountCredentials($createdUser, $validated['password'], auth()->user());
        }

        return redirect()->route('admin.alumni.index')->with('success', 'Alumni record created successfully.');
    }

    public function show(Alumnus $alumnus)
    {
        $alumnus->load([
            'user', 'department', 'program', 'student',
            'projects', 'achievementRecords', 'employmentHistory',
        ]);

        $lastLogin = $alumnus->user?->last_login_at;

        return view('admin.alumni.show', compact('alumnus', 'lastLogin'));
    }

    public function edit(Alumnus $alumnus)
    {
        $alumnus->load(['user', 'projects', 'achievementRecords']);
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        return view('admin.alumni.edit', compact('alumnus', 'departments', 'programs'));
    }

    public function update(Request $request, Alumnus $alumnus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($alumnus->user_id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
            'department_id' => 'required|exists:departments,id',
            'program_id' => 'required|exists:programs,id',
            'graduation_year' => 'required|string|max:4',
            'admission_year' => 'nullable|string|max:4',
            'roll_number' => 'nullable|string|max:50',
            'current_job' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'work_location' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string',
            'bio' => 'nullable|string|max:2000',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'achievements' => 'nullable|string|max:2000',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'visibility' => 'nullable|string|in:public,private',
        ]);

        DB::transaction(function () use ($validated, $request, $alumnus) {
            $user = $alumnus->user;

            if ($request->hasFile('avatar')) {
                if ($user->avatar) Storage::disk('public')->delete($user->avatar);
                $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'avatar' => $validated['avatar_path'] ?? $user->avatar,
                'is_active' => $validated['is_active'] ?? $user->is_active,
            ]);

            $alumnus->update([
                'department_id' => $validated['department_id'],
                'program_id' => $validated['program_id'],
                'graduation_year' => $validated['graduation_year'],
                'admission_year' => $validated['admission_year'] ?? null,
                'roll_number' => $validated['roll_number'] ?? null,
                'current_job' => $validated['current_job'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'work_location' => $validated['work_location'] ?? null,
                'employment_status' => $validated['employment_status'] ?? 'unknown',
                'bio' => $validated['bio'] ?? null,
                'linkedin_url' => $validated['linkedin_url'] ?? null,
                'github_url' => $validated['github_url'] ?? null,
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'achievements' => $validated['achievements'] ?? null,
                'is_featured' => $validated['is_featured'] ?? false,
                'visibility' => $validated['visibility'] ?? $alumnus->visibility,
            ]);
        });

        return redirect()->route('admin.alumni.show', $alumnus)->with('success', 'Alumni updated successfully.');
    }

    public function destroy(Alumnus $alumnus)
    {
        DB::transaction(function () use ($alumnus) {
            $user = $alumnus->user;
            // Delete project files
            foreach ($alumnus->projects as $project) {
                if ($project->report_path) Storage::disk('public')->delete($project->report_path);
                if ($project->cover_image) Storage::disk('public')->delete($project->cover_image);
                foreach ($project->screenshots ?? [] as $ss) {
                    Storage::disk('public')->delete($ss);
                }
            }
            // Delete achievement certificates
            foreach ($alumnus->achievementRecords as $a) {
                if ($a->certificate_path) Storage::disk('public')->delete($a->certificate_path);
            }
            if ($user?->avatar) Storage::disk('public')->delete($user->avatar);
            if ($alumnus->cv_path) Storage::disk('public')->delete($alumnus->cv_path);

            $alumnus->projects()->delete();
            $alumnus->achievementRecords()->delete();
            $alumnus->employmentHistory()->delete();
            $alumnus->delete();
            $user?->delete();
        });

        return redirect()->route('admin.alumni.index')->with('success', 'Alumni record deleted.');
    }

    public function toggleFeatured(Alumnus $alumnus)
    {
        $alumnus->update(['is_featured' => !$alumnus->is_featured]);
        $label = $alumnus->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Alumni {$label} successfully.");
    }
}
