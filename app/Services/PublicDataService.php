<?php

namespace App\Services;

use App\Models\{AcademicSession, Department, Notice, Banner, Alumni, Download, Page, Program, Staff, Student, SiteSetting, Facility, Executive, Media, Teacher};
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
                    ->get(['id', 'title', 'slug', 'type', 'published_at', 'created_at']),
                'examNotices' => Notice::published()
                    ->where('type', 'exam')
                    ->latest()
                    ->take(6)
                    ->get(['id', 'title', 'slug', 'type', 'published_at', 'created_at']),
            ];
        });
    }

    public function getNotices(int $perPage = 15, ?string $type = 'general')
    {
        return Notice::published()
            ->when(in_array($type, ['general', 'exam', 'news', 'event'], true), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate($perPage, ['id', 'title', 'slug', 'type', 'attachment', 'content', 'published_at', 'created_at']);
    }

    public function getDepartments(): \Illuminate\Support\Collection
    {
        return Cache::remember('public:departments', self::CACHE_TTL, function () {
            return Department::active()
                ->withCount(['programs', 'students', 'teachers'])
                ->get(['id', 'name', 'code', 'slug', 'description', 'photo', 'seat_capacity']);
        });
    }

    public function getNavigationCourses(): \Illuminate\Support\Collection
    {
        return Cache::remember('public:navigation_courses', self::CACHE_TTL, function () {
            return Department::active()
                ->with(['programs' => function ($query) {
                    $query->active()->orderBy('name');
                }])
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'slug']);
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

    public function getDownloads(?string $category = null): Collection
    {
        $downloads = Cache::remember('public:downloads', self::CACHE_TTL, function () {
            return Download::with('department:id,name,code')
                ->latest()
                ->get(['id', 'title', 'file_path', 'category', 'department_id', 'created_at']);
        });

        $normalizedCategory = $this->normalizeCategory($category);

        if ($normalizedCategory === '') {
            return $downloads;
        }

        return $downloads->filter(function ($download) use ($normalizedCategory) {
            return $this->normalizeCategory((string) $download->category) === $normalizedCategory;
        })->values();
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
                ->with('user:id,avatar')
                ->orderBy('order')
                ->get();
        });
    }

    public function getGalleryMedia(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:gallery', self::CACHE_TTL, function () {
            return Media::where(function ($query) {
                $query->where('file_type', 'gallery')
                    ->orWhere('file_type', 'image')
                    ->orWhere('mime_type', 'like', 'image/%');
            })
                ->with('department:id,name,code')
                ->latest()
                ->get(['id', 'title', 'file_path', 'file_type', 'mime_type', 'size', 'department_id', 'created_at']);
        });
    }

    public function getQuestionBankDownloads(): Collection
    {
        return $this->getDownloads('question-bank');
    }

    public function getNewsEvents(int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notice::published()
            ->whereIn('type', ['news', 'event'])
            ->latest()
            ->paginate($perPage, ['id', 'title', 'slug', 'type', 'content', 'attachment', 'published_at', 'created_at']);
    }

    public function getLatestNewsEvents(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("public:news_events:{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Notice::published()
                ->whereIn('type', ['news', 'event'])
                ->latest()
                ->take($limit)
                ->get(['id', 'title', 'slug', 'type', 'content', 'attachment', 'published_at', 'created_at']);
        });
    }

    public function getRecentDownloads(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("public:recent_downloads:{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Download::with('department:id,name,code')
                ->latest()
                ->take($limit)
                ->get(['id', 'title', 'file_path', 'category', 'department_id', 'created_at']);
        });
    }

    public function getHomepageStats(): array
    {
        return Cache::remember('public:homepage_stats', self::CACHE_TTL, function () {
            return [
                'graduates'     => Alumni::verified()->count(),
                'students'      => Student::active()->count(),
                'faculty_staff' => Teacher::active()->count() + Staff::where('is_active', true)->count(),
                'programs'      => Program::active()->count(),
                'years'         => now()->year - 2010,
            ];
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
                'public:gallery',
                'public:question_bank',
                'public:news_events:5',
                'public:homepage_stats',
                'public:navigation_courses',
            ];

            foreach (array_keys(SiteSetting::managedPageDefinitions()) as $slug) {
                $cacheKeys[] = "public:page:{$slug}";
            }

            foreach ($cacheKeys as $cacheKey) {
                Cache::forget($cacheKey);
            }

            Cache::forget('brand:site_logo');
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

    private function normalizeCategory(?string $category): string
    {
        return Str::of((string) $category)
            ->lower()
            ->replace(['-', '_'], ' ')
            ->squish()
            ->toString();
    }
}
