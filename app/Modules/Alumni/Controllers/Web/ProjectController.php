<?php

namespace App\Modules\Alumni\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlumniProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        $alumnus = auth()->user()->alumnus;
        $alumnus?->load('projects');
        return view('alumni.projects', compact('alumnus'));
    }

    public function edit(string $type)
    {
        abort_unless(in_array($type, ['minor', 'major']), 404);

        $alumnus = auth()->user()->alumnus;
        $project = $alumnus?->projects()->where('type', $type)->first();

        return view('alumni.project-edit', compact('alumnus', 'project', 'type'));
    }

    public function update(Request $request, string $type)
    {
        abort_unless(in_array($type, ['minor', 'major']), 404);

        $alumnus = auth()->user()->alumnus;
        abort_unless($alumnus, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'supervisor' => 'nullable|string|max:255',
            'technologies' => 'nullable|string|max:500',
            'team_members' => 'nullable|string|max:500',
            'report' => 'nullable|file|mimes:pdf|max:10240',
            'cover_image' => 'nullable|image|max:2048',
            'screenshots.*' => 'nullable|image|max:2048',
            'github_url' => 'nullable|url|max:255',
            'demo_url' => 'nullable|url|max:255',
            'is_visible' => 'boolean',
            'year' => 'nullable|string|max:4',
        ]);

        $project = $alumnus->projects()->where('type', $type)->first();

        $data = [
            'alumni_id' => $alumnus->id,
            'type' => $type,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'supervisor' => $validated['supervisor'] ?? null,
            'technologies' => !empty($validated['technologies'])
                ? array_map('trim', explode(',', $validated['technologies']))
                : [],
            'team_members' => !empty($validated['team_members'])
                ? array_map('trim', explode(',', $validated['team_members']))
                : [],
            'github_url' => $validated['github_url'] ?? null,
            'demo_url' => $validated['demo_url'] ?? null,
            'is_visible' => $validated['is_visible'] ?? true,
            'year' => $validated['year'] ?? null,
            'status' => 'completed',
        ];

        // Handle report upload
        if ($request->hasFile('report')) {
            if ($project?->report_path) Storage::disk('public')->delete($project->report_path);
            $data['report_path'] = $request->file('report')->store('alumni/projects/reports', 'public');
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            if ($project?->cover_image) Storage::disk('public')->delete($project->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('alumni/projects/covers', 'public');
        }

        // Handle screenshots
        if ($request->hasFile('screenshots')) {
            // Delete old screenshots
            if ($project?->screenshots) {
                foreach ($project->screenshots as $ss) {
                    Storage::disk('public')->delete($ss);
                }
            }
            $screenshots = [];
            foreach ($request->file('screenshots') as $file) {
                $screenshots[] = $file->store('alumni/projects/screenshots', 'public');
            }
            $data['screenshots'] = $screenshots;
        }

        if ($project) {
            $project->update($data);
        } else {
            AlumniProject::create($data);
        }

        return redirect()->route('alumni.projects.index')->with('success', ucfirst($type) . ' project saved.');
    }
}
