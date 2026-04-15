<?php

namespace App\Services;

use App\Models\{AcademicSession, Department, Notice, Banner, Alumni, Download, Page, Staff, SiteSetting, Facility, Executive};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
            if ($page = $this->buildManagedPage($slug)) {
                return $page;
            }

            return Page::published()
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    public function getFacilities(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:facilities', self::CACHE_TTL, function () {
            return Facility::where('is_published', true)
                ->with('department:id,name,code')
                ->with('program:id,name,code')
                ->latest()
                ->get();
        });
    }

    public function getStaff(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:staff', self::CACHE_TTL, function () {
            return Staff::where('is_active', true)
                ->orderBy('order')
                ->get();
        });
    }

    public function getSiteSettings(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:site_settings', self::CACHE_TTL, function () {
            SiteSetting::ensureDefaults();

            return SiteSetting::all();
        });
    }

    public function getLeadership(): array
    {
        return Cache::remember('public:leadership', self::CACHE_TTL, function () {
            return [
                'presidents' => Executive::presidents()->get(),
                'principals' => Executive::principals()->get(),
            ];
        });
    }

    /**
     * Invalidate all public-facing caches when data is updated.
     */
    public static function invalidate(string $key = '*'): void
    {
        if ($key === '*') {
            $cacheKeys = [
                'public:homepage',
                'public:departments',
                'public:downloads',
                'public:facilities',
                'public:staff',
                'public:leadership',
                'public:site_settings',
            ];

            foreach (array_keys(SiteSetting::managedPageDefinitions()) as $slug) {
                $cacheKeys[] = "public:page:{$slug}";
            }

            foreach ($cacheKeys as $cacheKey) {
                Cache::forget($cacheKey);
            }
        } else {
            Cache::forget("public:{$key}");
        }
    }

    private function buildManagedPage(string $slug): ?Page
    {
        $definition = SiteSetting::managedPageDefinition($slug);

        if (!$definition) {
            return null;
        }

        $settings = $this->getSiteSettings()->keyBy('key');
        $content = trim((string) optional($settings->get($definition['content_key']))->value);

        if ($slug === 'contact-us' && $content === '') {
            $content = $this->buildContactPageSummary($settings);
        }

        return new Page([
            'title' => $definition['title'],
            'slug' => $slug,
            'content' => $content,
            'meta_title' => $definition['title'],
            'meta_description' => $definition['meta_description'] ?? Str::limit(strip_tags($content), 160, ''),
            'is_published' => true,
        ]);
    }

    private function buildContactPageSummary(Collection $settings): string
    {
        $details = array_filter([
            optional($settings->get('contact_address'))->value ? '<p><strong>Address:</strong> '.e(optional($settings->get('contact_address'))->value).'</p>' : null,
            optional($settings->get('contact_email'))->value ? '<p><strong>Email:</strong> '.e(optional($settings->get('contact_email'))->value).'</p>' : null,
            optional($settings->get('contact_phone'))->value ? '<p><strong>Phone:</strong> '.e(optional($settings->get('contact_phone'))->value).'</p>' : null,
        ]);

        if ($details === []) {
            return '';
        }

        return '<p>Reach out to our team using the official contact details below.</p>'.implode('', $details);
    }
}
