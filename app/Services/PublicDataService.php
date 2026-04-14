<?php

namespace App\Services;

use App\Models\{AcademicSession, Department, Notice, Banner, Alumni, Download, Page};
use Illuminate\Support\Facades\Cache;

/**
 * PublicDataService — The ONLY authorized pathway for public pages to access institutional data.
 * Public pages MUST use this service. They must NEVER query the database directly.
 */
class PublicDataService
{
    /** Cache TTL in seconds */
    private const CACHE_TTL = 600; // 10 minutes

    public function getHomepageData(): array
    {
        return Cache::remember('public:homepage', self::CACHE_TTL, function () {
            return [
                'banners' => Banner::active()->get(['id', 'title', 'subtitle', 'image', 'link', 'order']),
                'departments' => Department::active()
                    ->withCount('programs')
                    ->get(['id', 'name', 'code', 'slug', 'description', 'photo']),
                'featured_alumni' => Alumni::featured()->verified()
                    ->with('user:id,name,avatar')
                    ->with('department:id,name,code')
                    ->take(4)
                    ->get(['id', 'user_id', 'department_id', 'graduation_year', 'current_job', 'company_name']),
                'notices' => Notice::published()->general()
                    ->latest()
                    ->take(6)
                    ->get(['id', 'title', 'slug', 'type', 'published_at']),
            ];
        });
    }

    public function getNotices(int $perPage = 15)
    {
        return Notice::published()
            ->where('type', 'general')
            ->latest()
            ->paginate($perPage, ['id', 'title', 'slug', 'type', 'attachment', 'published_at']);
    }

    public function getDepartments(): \Illuminate\Support\Collection
    {
        return Cache::remember('public:departments', self::CACHE_TTL, function () {
            return Department::active()
                ->withCount(['programs', 'students', 'teachers'])
                ->get(['id', 'name', 'code', 'slug', 'description', 'photo', 'seat_capacity']);
        });
    }

    public function getDepartmentBySlug(string $slug): Department
    {
        return Cache::remember("public:department:{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Department::where('slug', $slug)
                ->with(['programs:id,department_id,name,code,total_semesters'])
                ->with(['hod:id,name'])
                ->firstOrFail(['id', 'name', 'code', 'slug', 'description', 'photo', 'seat_capacity', 'hod_id']);
        });
    }

    public function getFeaturedAlumni(int $limit = 8): \Illuminate\Support\Collection
    {
        return Cache::remember("public:alumni:featured:{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Alumni::featured()->verified()
                ->with('user:id,name,avatar')
                ->with('department:id,name,code')
                ->with('program:id,name,code')
                ->take($limit)
                ->get();
        });
    }

    public function getDownloads(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:downloads', self::CACHE_TTL, function () {
            return Download::with('department:id,name,code')
                ->latest()
                ->get(['id', 'title', 'file_path', 'category', 'department_id', 'created_at']);
        });
    }

    public function getPage(string $slug): Page
    {
        return Cache::remember("public:page:{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Page::published()
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    /**
     * Invalidate all public-facing caches when data is updated.
     */
    public static function invalidate(string $key = '*'): void
    {
        if ($key === '*') {
            Cache::forget('public:homepage');
            Cache::forget('public:departments');
            Cache::forget('public:downloads');
        } else {
            Cache::forget("public:{$key}");
        }
    }
}
