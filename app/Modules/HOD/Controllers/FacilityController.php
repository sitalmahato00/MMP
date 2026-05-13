<?php

namespace App\Modules\HOD\Controllers;


/**
 * HOD facility management for department pages.
 * 
 * HODs can manage facility pages related to their department.
 */
use App\Modules\CMS\Models\Facility;
use App\Modules\CMS\Models\Page;
use App\Modules\Department\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FacilityController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);

        // Get department-related pages (pages with department slug prefix)
        $departmentSlug = Str::slug($department->name);
        
        $query = Page::where('slug', 'like', "{$departmentSlug}%")
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'published') {
                    $q->where('is_published', true);
                } elseif ($request->status === 'draft') {
                    $q->where('is_published', false);
                }
            });

        $pages = (clone $query)
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalPages = (clone $query)->count();
        $publishedPages = (clone $query)->where('is_published', true)->count();
        $draftPages = (clone $query)->where('is_published', false)->count();

        return view('hod.facilities.index', compact(
            'pages', 'department',
            'totalPages', 'publishedPages', 'draftPages'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        return view('hod.facilities.create', compact('department'));
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $departmentSlug = Str::slug($department->name);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'category' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'availability' => 'nullable|string|max:255',
            'features' => 'nullable|string',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        }

        $page = Page::create([
            'title' => $data['title'],
            'slug' => $departmentSlug . '-' . Str::slug($data['title']) . '-' . time(),
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?? null,
            'meta_title' => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? Str::limit(strip_tags($data['content']), 160),
            'is_published' => $data['is_published'] ?? false,
            'category' => $data['category'] ?? null,
            'location' => $data['location'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'availability' => $data['availability'] ?? null,
            'features' => $data['features'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Invalidate public cache
        \App\Services\PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.facilities.index')
            ->with('success', 'Facility page created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Request $request, Page $content)
    {
        $department = $this->currentDepartment($request);
        $departmentSlug = Str::slug($department->name);

        // Verify page belongs to department
        if (!Str::startsWith($content->slug, $departmentSlug)) {
            abort(403, 'Unauthorized access to content page.');
        }

        return view('hod.facilities.show', compact('content', 'department'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Page $content)
    {
        $department = $this->currentDepartment($request);
        $departmentSlug = Str::slug($department->name);

        // Verify page belongs to department
        if (!Str::startsWith($content->slug, $departmentSlug)) {
            abort(403, 'Unauthorized access to content page.');
        }

        return view('hod.facilities.edit', compact('content', 'department'));
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, Page $content)
    {
        $department = $this->currentDepartment($request);
        $departmentSlug = Str::slug($department->name);

        // Verify page belongs to department
        if (!Str::startsWith($content->slug, $departmentSlug)) {
            abort(403, 'Unauthorized access to content page.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'category' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'availability' => 'nullable|string|max:255',
            'features' => 'nullable|string',
        ]);

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($content->featured_image && Storage::disk('public')->exists($content->featured_image)) {
                Storage::disk('public')->delete($content->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('pages', 'public');
        } else {
            unset($data['featured_image']);
        }

        $content->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'meta_title' => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? Str::limit(strip_tags($data['content']), 160),
            'is_published' => $data['is_published'] ?? false,
            'category' => $data['category'] ?? null,
            'location' => $data['location'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'availability' => $data['availability'] ?? null,
            'features' => $data['features'] ?? null,
        ] + (isset($data['featured_image']) ? ['featured_image' => $data['featured_image']] : []));

        // Invalidate public cache
        \App\Services\PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.facilities.index')
            ->with('success', 'Facility page updated successfully.');
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, Page $content)
    {
        $department = $this->currentDepartment($request);
        $departmentSlug = Str::slug($department->name);

        // Verify page belongs to department
        if (!Str::startsWith($content->slug, $departmentSlug)) {
            abort(403, 'Unauthorized access to content page.');
        }

        if ($content->featured_image && Storage::disk('public')->exists($content->featured_image)) {
            Storage::disk('public')->delete($content->featured_image);
        }

        $content->delete();

        // Invalidate public cache
        \App\Services\PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.facilities.index')
            ->with('success', 'Facility page deleted successfully.');
    }
}
