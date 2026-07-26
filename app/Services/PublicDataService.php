<?php

namespace App\Services;

use App\Helpers\NepaliDateHelper;
use App\Models\{Department, Notice, Banner, Alumni, Download, Page, Program, Staff, Student, SiteSetting, Facility, Executive, Media, Teacher};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * PublicDataService — The ONLY authorized pathway for public pages to access institutional data.
 * Public pages MUST use this service. They must NEVER query the database directly.
 */
class PublicDataService
{
    /** Cache TTL in seconds */
    private const CACHE_TTL = 120; // 2 minutes

    public function getHomepageData(): array
    {
        return Cache::remember('public:homepage', self::CACHE_TTL, function () {
            return [
                'banners' => Banner::active()->get(['id', 'title', 'subtitle', 'image', 'link', 'order']),
                'departments' => Department::active()
                    ->withCount('programs')
                    ->get(['id', 'name', 'code', 'slug', 'description', 'photo', 'syllabus']),
                'featured_alumni' => Alumni::featured()->verified()
                    ->with('user:id,name,avatar')
                    ->with('department:id,name,code')
                    ->take(4)
                    ->get(['id', 'user_id', 'department_id', 'graduation_year', 'current_job', 'company_name']),
                // Homepage notices: ONLY college-wide (no department_id set)
                'notices' => Notice::published()
                    ->whereIn('type', ['general', 'academic'])
                    ->whereNull('department_id')
                    ->whereNull('program_id')
                    ->with(['department:id,name,code', 'program:id,name,code'])
                    ->latest()
                    ->take(6)
                    ->get(['id', 'title', 'slug', 'type', 'department_id', 'program_id', 'semester', 'attachment', 'published_at', 'created_at']),
                'examNotices' => Notice::published()
                    ->where('type', 'exam')
                    ->whereNull('department_id')
                    ->whereNull('program_id')
                    ->with(['department:id,name,code', 'program:id,name,code'])
                    ->latest()
                    ->take(6)
                    ->get(['id', 'title', 'slug', 'type', 'department_id', 'program_id', 'semester', 'attachment', 'published_at', 'created_at']),
            ];
        });
    }

    /**
     * Main /notices page — shows ALL published notices (both college-wide and department-specific).
     * No department_id filter applied here.
     */
    public function getNotices(int $perPage = 15, ?string $type = 'all')
    {
        return Notice::published()
            ->when(
                $type !== 'all' && in_array($type, ['general', 'exam', 'department', 'program', 'academic'], true),
                fn ($q) => $q->where('type', $type),
                fn ($q) => $q->whereIn('type', ['general', 'exam', 'department', 'program', 'academic'])
            )
            ->with(['department:id,name,code', 'program:id,name,code'])
            ->latest()
            ->paginate($perPage, ['id', 'title', 'slug', 'type', 'department_id', 'program_id', 'semester', 'attachment', 'content', 'published_at', 'created_at']);
    }

    public function getPublishedItemBySlug(string $slug): Notice
    {
        return Cache::remember("public:notice:{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Notice::published()
                ->with(['department:id,name,code', 'program:id,name,code', 'author:id,name', 'attachments'])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    }

    public function getRelatedItemsByType(string $type, int $excludeId, int $limit = 5): Collection
    {
        return Notice::published()
            ->where('type', $type)
            ->whereKeyNot($excludeId)
            ->latest()
            ->take($limit)
            ->get(['id', 'title', 'slug', 'type', 'published_at', 'created_at']);
    }

    public function getDepartments(): \Illuminate\Support\Collection
    {
        return Cache::remember('public:departments', self::CACHE_TTL, function () {
            return Department::active()
                ->withCount(['programs', 'students', 'teachers'])
                ->get(['id', 'name', 'code', 'slug', 'description', 'photo', 'syllabus', 'seat_capacity']);
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
                ->get(['id', 'name', 'code', 'slug', 'photo', 'syllabus']);
        });
    }

    public function getDepartmentBySlug(string $slug): Department
    {
        return Cache::remember("public:department:{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Department::where('slug', $slug)
                ->with([
                    'programs:id,department_id,name,code,slug,ctevt_code,affiliation_type,total_semesters,duration_years,description,is_active',
                    'hod:id,name,avatar',
                ])
                ->withCount(['teachers', 'students'])
                ->firstOrFail(['id', 'name', 'code', 'slug', 'description', 'photo', 'syllabus', 'seat_capacity', 'hod_id', 'created_at']);
        });
    }

    /**
     * Get enriched department data for the portal homepage.
     * Returns department + stats + latest notices + HOD + faculty preview + gallery.
     */
    public function getDepartmentPortalData(string $slug): array
    {
        $department = Cache::remember("public:dept_portal:{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Department::where('slug', $slug)
                ->with([
                    'programs:id,department_id,name,code,slug,ctevt_code,affiliation_type,total_semesters,duration_years,description,is_active',
                    'hod:id,name,avatar',
                ])
                ->withCount(['teachers', 'students'])
                ->firstOrFail(['id', 'name', 'code', 'slug', 'description', 'photo', 'syllabus', 'seat_capacity', 'hod_id', 'created_at']);
        });

        // Latest notices for this department — ONLY department-specific (department_id matches)
        $notices = Cache::remember("public:dept_portal:{$slug}:notices", self::CACHE_TTL, function () use ($department) {
            return Notice::published()
                ->where('department_id', $department->id)
                ->whereIn('type', ['general', 'department', 'exam', 'academic'])
                ->with(['attachments'])
                ->latest('published_at')
                ->take(8)
                ->get(['id', 'title', 'slug', 'type', 'content', 'attachment', 'department_id', 'published_at', 'created_at']);
        });

        // Teachers in this department
        $teachers = Cache::remember("public:dept_portal:{$slug}:teachers", self::CACHE_TTL, function () use ($department) {
            return Teacher::active()
                ->where('department_id', $department->id)
                ->with('user:id,name,avatar')
                ->orderBy('designation')
                ->get(['id', 'user_id', 'department_id', 'designation', 'qualification', 'specialization']);
        });

        // HOD Teacher record (for extra details)
        $hodTeacher = null;
        if ($department->hod_id) {
            $hodTeacher = Cache::remember("public:dept_portal:{$slug}:hod", self::CACHE_TTL, function () use ($department) {
                return Teacher::where('user_id', $department->hod_id)
                    ->where('department_id', $department->id)
                    ->with('user:id,name,avatar,email,phone')
                    ->first(['id', 'user_id', 'designation', 'qualification', 'specialization']);
            });
        }

        // Gallery media for this department
        $gallery = Cache::remember("public:dept_portal:{$slug}:gallery", self::CACHE_TTL, function () use ($department) {
            return Media::where('department_id', $department->id)
                ->where(function ($q) {
                    $q->where('file_type', 'gallery')
                      ->orWhere('file_type', 'image')
                      ->orWhere('mime_type', 'like', 'image/%');
                })
                ->latest()
                ->take(8)
                ->get(['id', 'title', 'file_path', 'file_type', 'mime_type', 'created_at']);
        });

        // Downloads for this department
        $downloads = Cache::remember("public:dept_portal:{$slug}:downloads", self::CACHE_TTL, function () use ($department) {
            return Download::where('department_id', $department->id)
                ->where('is_public', true)
                ->latest()
                ->take(6)
                ->get(['id', 'title', 'file_path', 'category', 'created_at']);
        });

        // Upcoming events — ONLY events scoped to this department
        $events = Cache::remember("public:dept_portal:{$slug}:events", self::CACHE_TTL, function () use ($department) {
            return Notice::published()
                ->where('type', 'event')
                ->where('department_id', $department->id)
                ->where('published_at', '>=', now())
                ->orderBy('published_at')
                ->take(3)
                ->get(['id', 'title', 'slug', 'type', 'published_at']);
        });

        // Compute stats
        $labsCount = Facility::where('department_id', $department->id)
            ->where('is_published', true)
            ->where(function ($q) {
                $q->where('category', 'lab')
                  ->orWhere('name', 'like', '%lab%');
            })
            ->count();

        $establishedYear = $department->created_at?->format('Y') ?? '2069 B.S.';

        $stats = [
            'programs'       => $department->programs->count(),
            'faculty'        => $department->teachers_count,
            'labs'           => max($labsCount, 0),
            'students'       => $department->students_count,
            'established'    => $establishedYear,
            'affiliation'    => 'CTEVT',
        ];

        return compact('department', 'notices', 'teachers', 'hodTeacher', 'gallery', 'downloads', 'events', 'stats');
    }

    /**
     * Get paginated notices for the department notices page.
     */
    public function getDepartmentNotices(string $slug, ?string $category = null, ?string $search = null, int $perPage = 12): array
    {
        $department = $this->getDepartmentBySlug($slug);

        // Department notices page: ONLY notices scoped to this department
        $query = Notice::published()
            ->where('department_id', $department->id)
            ->whereIn('type', ['general', 'department', 'exam', 'academic'])
            ->with(['attachments'])
            ->latest('published_at');

        if ($category && $category !== 'all') {
            $query->where('type', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $notices = $query->paginate($perPage, ['id', 'title', 'slug', 'type', 'content', 'attachment', 'department_id', 'published_at', 'created_at'])
            ->withQueryString();

        return compact('department', 'notices', 'category', 'search');
    }

    /**
     * Get all people for a department: HOD, teachers, staff.
     */
    public function getDepartmentPeople(string $slug): array
    {
        $department = $this->getDepartmentBySlug($slug);

        $teachers = Teacher::active()
            ->where('department_id', $department->id)
            ->with('user:id,name,avatar,email,phone')
            ->orderByRaw("FIELD(designation, 'Head of Department', 'HOD', 'Associate Professor', 'Assistant Professor', 'Lecturer', 'Instructor', 'Lab Instructor') ASC")
            ->orderBy('designation')
            ->get(['id', 'user_id', 'department_id', 'designation', 'qualification', 'specialization', 'join_date', 'employment_type']);

        // HOD: the teacher whose user_id matches department->hod_id
        $hod = $teachers->first(fn ($t) => (int) $t->user_id === (int) $department->hod_id);

        // Faculty (excluding HOD from the main faculty list to avoid duplication)
        $faculty = $teachers->filter(fn ($t) => (int) $t->user_id !== (int) $department->hod_id)->values();

        return compact('department', 'teachers', 'hod', 'faculty');
    }

    /**
     * Get gallery albums for a department.
     */
    public function getDepartmentGallery(string $slug): array
    {
        $department = $this->getDepartmentBySlug($slug);

        $media = Media::where('department_id', $department->id)
            ->where(function ($q) {
                $q->where('file_type', 'gallery')
                  ->orWhere('file_type', 'image')
                  ->orWhere('mime_type', 'like', 'image/%');
            })
            ->latest()
            ->get(['id', 'title', 'file_path', 'file_type', 'mime_type', 'size', 'created_at']);

        return compact('department', 'media');
    }

    /**
     * Get programs for a department.
     */
    public function getDepartmentPrograms(string $slug): array
    {
        $department = $this->getDepartmentBySlug($slug);

        $programs = Program::where('department_id', $department->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'department_id', 'name', 'code', 'slug', 'ctevt_code', 'affiliation_type', 'total_semesters', 'duration_years', 'description', 'is_active']);

        return compact('department', 'programs');
    }

    public function getProgramBySlug(string $departmentSlug, string $programSlug): array
    {
        return Cache::remember("public:program:{$departmentSlug}:{$programSlug}", self::CACHE_TTL, function () use ($departmentSlug, $programSlug) {
            $department = Department::where('slug', $departmentSlug)
                ->active()
                ->with(['hod:id,name,email,avatar'])
                ->firstOrFail(['id', 'name', 'code', 'slug', 'hod_id']);

            // Try to find program by slug first, then fallback to generating slug from name
            $program = Program::where('department_id', $department->id)
                ->active()
                ->where(function ($query) use ($programSlug) {
                    $query->where('slug', $programSlug)
                        ->orWhereRaw('LOWER(REPLACE(REPLACE(name, " ", "-"), "/", "-")) = ?', [strtolower($programSlug)]);
                })
                ->with([
                    'department:id,name,code,slug,hod_id',
                    'coordinator:id,user_id',
                    'coordinator.user:id,name,email,phone,avatar',
                    'subjects' => function ($query) {
                        $query->orderBy('semester')->orderBy('code');
                    }
                ])
                ->firstOrFail([
                    'id', 'department_id', 'coordinator_id', 'name', 'code', 'slug',
                    'ctevt_code', 'affiliation_type', 'total_semesters', 'duration_years',
                    'description', 'eligibility', 'syllabus', 'is_active'
                ]);

            return [
                'program' => $program,
                'department' => $department,
            ];
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

    public function getAlumniDirectory(?int $departmentId = null, ?string $search = null, ?string $graduationYear = null, int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $page = request()->integer('page', 1);
        $version = $this->alumniCacheVersion();
        $cacheKey = "public:alumni:directory:v{$version}:{$departmentId}:{$search}:{$graduationYear}:{$perPage}:{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($departmentId, $search, $graduationYear, $perPage) {
            $query = Alumni::publicVisible()
                ->verified()
                ->with([
                    'user:id,name,avatar',
                    'department:id,name,code,slug',
                    'program:id,name,code,slug',
                    'student:id,user_id,student_no,registration_number,batch,current_semester,section,status,admission_date',
                    'projects' => fn ($projects) => $projects
                        ->where('is_visible', true)
                        ->orderByDesc('year')
                        ->orderBy('type'),
                ])
                ->withCount([
                    'projects as visible_projects_count' => fn ($projects) => $projects->where('is_visible', true),
                    'achievementRecords',
                    'employmentHistory',
                ]);

            if (filled($search = trim((string) $search))) {
                $query->where(function ($builder) use ($search) {
                    $builder->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('current_job', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('work_location', 'like', "%{$search}%")
                    ->orWhere('achievements', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%")
                    ->orWhere('roll_number', 'like', "%{$search}%")
                    ->orWhere('admission_year', 'like', "%{$search}%")
                    ->orWhere('graduation_year', 'like', "%{$search}%");
                });
            }

            if (filled($departmentId)) {
                $query->where('department_id', $departmentId);
            }

            if (filled($graduationYear)) {
                $query->where('graduation_year', $graduationYear);
            }

            return $query
                ->orderByDesc('is_featured')
                ->orderByDesc('graduation_year')
                ->orderBy('id', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        });
    }

    public function getAlumniGraduationYears(): \Illuminate\Support\Collection
    {
        return Cache::remember('public:alumni:graduation_years', self::CACHE_TTL, function () {
            return Alumni::publicVisible()
                ->verified()
                ->select('graduation_year')
                ->distinct()
                ->orderByDesc('graduation_year')
                ->pluck('graduation_year');
        });
    }

    public function getAlumniProfile(int $id): Alumni
    {
        $version = $this->alumniCacheVersion();

        return Cache::remember("public:alumni:profile:v{$version}:{$id}", self::CACHE_TTL, function () use ($id) {
            return Alumni::publicVisible()
                ->verified()
                ->with([
                    'user:id,name,email,phone,address,gender,dob,avatar',
                    'department:id,name,code,slug',
                    'program:id,name,code,slug',
                    'student:id,user_id,student_no,registration_number,batch,current_semester,section,status,admission_date',
                    'projects' => fn ($projects) => $projects
                        ->orderByDesc('is_visible')
                        ->orderByDesc('year'),
                    'achievementRecords' => fn ($records) => $records->orderByDesc('year'),
                    'employmentHistory' => fn ($history) => $history->orderByDesc('start_date'),
                ])
                ->withCount([
                    'projects as visible_projects_count' => fn ($projects) => $projects->where('is_visible', true),
                    'achievementRecords',
                    'employmentHistory',
                ])
                ->findOrFail($id);
        });
    }

    public function getPublicStaffDirectory(
        ?string $search = null,
        ?string $department = null,
        ?string $designation = null,
        ?string $employmentStatus = null,
        ?string $joinedYear = null,
        ?string $featured = null,
        int $perPage = 12
    ): array {
        $page = request()->integer('page', 1);
        $version = $this->staffCacheVersion();
        $cacheKey = "public:staff:directory:v{$version}:{$search}:{$department}:{$designation}:{$employmentStatus}:{$joinedYear}:{$featured}:{$perPage}:{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($search, $department, $designation, $employmentStatus, $joinedYear, $featured, $perPage) {
            $query = Staff::publicVisible()
                ->with(['documents' => fn ($documents) => $documents->where('is_public', true)])
                ->withCount(['documents as public_documents_count' => fn ($documents) => $documents->where('is_public', true)]);

            if ($search = trim((string) $search)) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('staff_code', 'like', "%{$search}%");
                });
            }

            if (filled($department)) {
                $query->where('department', $department);
            }

            if (filled($designation)) {
                $query->where('designation', $designation);
            }

            if (filled($employmentStatus)) {
                $query->where('employment_status', $employmentStatus);
            }

            $this->applyBsJoinedYearFilter($query, $joinedYear);

            if (filled($featured)) {
                $query->where('featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN));
            }

            $staff = $query->orderByDesc('featured')->orderBy('order')->orderBy('name')->paginate($perPage)->withQueryString();

            $departments = Staff::publicVisible()
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department');

            $designations = Staff::publicVisible()
                ->whereNotNull('designation')
                ->where('designation', '!=', '')
                ->distinct()
                ->orderBy('designation')
                ->pluck('designation');

            $joinedYears = Staff::publicVisible()
                ->whereNotNull('join_date')
                ->get(['join_date'])
                ->map(fn ($member) => bsDate($member->join_date, 'Y'))
                ->filter()
                ->unique()
                ->sortDesc()
                ->values();

            $totalVisible = Staff::publicVisible()->count();
            $activeVisible = Staff::publicVisible()->where('employment_status', 'active')->count();
            $resignedVisible = Staff::publicVisible()->where('employment_status', 'resigned')->count();
            $featuredVisible = Staff::publicVisible()->featured()->count();
            $addedThisYear = Staff::publicVisible()->whereYear('created_at', now()->year)->count();

            $topDepartment = Staff::publicVisible()
                ->select('department', DB::raw('count(*) as total'))
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->groupBy('department')
                ->orderByDesc('total')
                ->first();

            return compact(
                'staff', 'departments', 'designations', 'joinedYears',
                'totalVisible', 'activeVisible', 'resignedVisible', 'featuredVisible', 'addedThisYear', 'topDepartment'
            );
        });
    }

    public function getPublicStaffProfile(int $id): Staff
    {
        $version = $this->staffCacheVersion();

        return Cache::remember("public:staff:profile:v{$version}:{$id}", self::CACHE_TTL, function () use ($id) {
            return Staff::publicVisible()
                ->with(['documents' => fn ($documents) => $documents->where('is_public', true)])
                ->withCount(['documents as public_documents_count' => fn ($documents) => $documents->where('is_public', true)])
                ->findOrFail($id);
        });
    }

    private function applyBsJoinedYearFilter($query, ?string $joinedYear): void
    {
        $joinedYear = trim((string) $joinedYear);

        if ($joinedYear === '' || ! preg_match('/^\d{4}$/', $joinedYear)) {
            return;
        }

        $startDate = NepaliDateHelper::toAD("{$joinedYear}-01-01");
        $endDate = NepaliDateHelper::toAD(((int) $joinedYear + 1) . '-01-01');

        if (! $startDate || ! $endDate) {
            return;
        }

        $query->whereDate('join_date', '>=', $startDate->toDateString())
            ->whereDate('join_date', '<', $endDate->toDateString());
    }

    public function getDownloads(?string $category = null): Collection
    {
        $downloads = Cache::remember('public:downloads', self::CACHE_TTL, function () {
            return Download::with('department:id,name,code')
                ->where('is_public', true)
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

    public function getFacilities(?string $departmentSlug = null): \Illuminate\Support\Collection
    {
        $cacheKey = $departmentSlug ? "public:facilities:{$departmentSlug}" : 'public:facilities';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($departmentSlug) {
            // Get Facility records
            $facilitiesQuery = Facility::where('is_published', true)
                ->with('department:id,name,code,slug')
                ->with('program:id,name,code');
            
            if ($departmentSlug) {
                $facilitiesQuery->whereHas('department', function ($q) use ($departmentSlug) {
                    $q->where('slug', $departmentSlug);
                });
            }
            
            $facilities = $facilitiesQuery->latest()->get();
            
            // Get Page records (HOD content) that belong to departments
            $pagesQuery = Page::where('is_published', true);
            
            if ($departmentSlug) {
                $pagesQuery->where('slug', 'like', "{$departmentSlug}-%");
            }
            
            $pages = $pagesQuery->latest()->get()->map(function ($page) {
                // Extract department slug from page slug (format: department-slug-title-timestamp)
                $slugParts = explode('-', $page->slug);
                if (count($slugParts) >= 2) {
                    $possibleDeptSlug = $slugParts[0];
                    if (count($slugParts) >= 3 && !is_numeric($slugParts[1])) {
                        $possibleDeptSlug = $slugParts[0] . '-' . $slugParts[1];
                    }
                    
                    $dept = Department::where('slug', $possibleDeptSlug)->first();
                    if ($dept) {
                        $page->department = $dept;
                        $page->category = 'resources'; // Assign a category for grouping
                        $page->is_page = true; // Flag to identify it's a Page model
                        
                        // Add properties that Facility model has but Page doesn't
                        $page->name = $page->title;
                        $page->description = strip_tags($page->content);
                        $page->location = null;
                        $page->capacity = null;
                        $page->program = null;
                        
                        // Handle images
                        $page->image_urls = $page->featured_image 
                            ? [asset('storage/' . $page->featured_image)]
                            : [];
                        
                        // Handle documents (Page model doesn't have documents)
                        $page->document_urls = [];
                    }
                }
                return $page;
            })->filter(function ($page) {
                return isset($page->department); // Only include pages with valid department
            });
            
            // Merge facilities and pages
            return $facilities->concat($pages);
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

    public function getTeachers(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:teachers', self::CACHE_TTL, function () {
            return Teacher::active()
                ->with(['user:id,name,avatar', 'department:id,name,code,slug'])
                ->orderBy('department_id')
                ->orderBy('designation')
                ->get(['id', 'user_id', 'department_id', 'employee_id', 'designation', 'qualification', 'specialization', 'join_date', 'employment_type', 'is_active']);
        });
    }

    public function getDepartmentHods(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('public:department_hods', self::CACHE_TTL, function () {
            return Department::active()
                ->whereNotNull('hod_id')
                ->with(['hod:id,name,avatar'])
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'slug', 'hod_id']);
        });
    }

    public function getPeopleProfile(string $type, int $id): array
    {
        $normalizedType = strtolower(trim($type));

        return match ($normalizedType) {
            'hod' => $this->buildHodPeopleProfile($id),
            'teacher' => $this->buildTeacherPeopleProfile($id),
            'staff' => $this->buildStaffPeopleProfile($id),
            default => abort(404),
        };
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

    public function bustStaffCaches(): void
    {
        Cache::forever('public:staff:version', $this->staffCacheVersion() + 1);
    }

    public function bustAlumniCaches(): void
    {
        Cache::forever('public:alumni:version', $this->alumniCacheVersion() + 1);
    }

    private function staffCacheVersion(): int
    {
        return (int) Cache::get('public:staff:version', 1);
    }

    private function alumniCacheVersion(): int
    {
        return (int) Cache::get('public:alumni:version', 1);
    }

    public function getNewsEvents(int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notice::published()
            ->with('attachments')
            ->whereIn('type', ['news', 'event'])
            ->latest()
            ->paginate($perPage, ['id', 'title', 'slug', 'type', 'content', 'attachment', 'published_at', 'created_at']);
    }

    public function getLatestNewsEvents(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("public:news_events:{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Notice::published()
                ->with('attachments')
                ->whereIn('type', ['news', 'event'])
                ->latest()
                ->take($limit)
                ->get(['id', 'title', 'slug', 'type', 'content', 'attachment', 'published_at', 'created_at']);
        });
    }

    public function getCtevtGeneralNotices(int $limit = 6): array
    {
        return $this->getCtevtNoticeFeed(false, $limit);
    }

    public function getCtevtResultNotices(int $limit = 6): array
    {
        return $this->getCtevtNoticeFeed(true, $limit);
    }

    public function getCtevtResultForm(): array
    {
        // Use shorter cache for CTEVT form (5 minutes) to ensure it stays updated
        return Cache::remember('public:ctevt_result_form', 300, function () {
            $checkUrl = config('services.ctevt_result.check_url', 'https://itms.ctevt.org.np:5580/check_results');
            $fallbackAction = config('services.ctevt_result.url', 'https://itms.ctevt.org.np:5580/search_results');

            try {
                $response = Http::timeout(12)
                    ->retry(2, 250)
                    ->withoutVerifying()
                    ->accept('text/html,application/xhtml+xml')
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    ])
                    ->get($checkUrl);

                if (! $response->successful()) {
                    return $this->fallbackCtevtResultForm($fallbackAction);
                }

                $html = $response->body();
                $parsedForm = $this->parseCtevtResultForm($html, $fallbackAction);

                if (($parsedForm['source'] ?? '') === 'live') {
                    return $parsedForm;
                }

                $regexParsedForm = $this->parseCtevtResultFormWithRegex($html, $fallbackAction);

                if (($regexParsedForm['source'] ?? '') === 'live') {
                    return $regexParsedForm;
                }

                return $parsedForm;
            } catch (\Throwable $throwable) {
                return $this->fallbackCtevtResultForm($fallbackAction);
            }
        });
    }

    private function getCtevtNoticeFeed(bool $isResult, int $limit): array
    {
        $feedKey = 'public:ctevt_notices:' . ($isResult ? 'result' : 'general') . ':' . $limit;

        return Cache::remember($feedKey, 600, function () use ($isResult, $limit) {
            $feedUrl = config('services.ctevt_notice.feed_url', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices');
            $pageUrl = $isResult
                ? config('services.ctevt_notice.result_url', 'https://itms.ctevt.org.np:5580/notices/result')
                : config('services.ctevt_notice.general_url', 'https://itms.ctevt.org.np:5580/notices');
            
            try {
                $response = Http::timeout(10)
                    ->retry(2, 500)
                    ->withoutVerifying()
                    ->accept('application/json,text/javascript,*/*;q=0.1')
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                        'X-Requested-With' => 'XMLHttpRequest',
                    ])
                    ->get($feedUrl, $this->buildCtevtNoticeFeedParams($isResult, $limit));

                if (! $response->successful()) {
                    return [
                        'source' => $isResult ? 'result' : 'general',
                        'source_state' => 'unavailable',
                        'page_url' => $pageUrl,
                        'items' => [],
                    ];
                }

                $payload = $response->json();

                if (! is_array($payload) || ! isset($payload['data']) || ! is_array($payload['data'])) {
                    return [
                        'source' => $isResult ? 'result' : 'general',
                        'source_state' => 'empty',
                        'page_url' => $pageUrl,
                        'items' => [],
                    ];
                }

                $items = collect($payload['data'])
                    ->map(fn (array $row, int $index) => $this->mapCtevtNoticeRow($row, $index, $isResult))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'source' => $isResult ? 'result' : 'general',
                    'source_state' => 'live',
                    'title' => $isResult ? 'Published Result' : 'General Notices',
                    'page_url' => $pageUrl,
                    'records_total' => (int) ($payload['recordsTotal'] ?? count($items)),
                    'items' => $items,
                ];
            } catch (\Exception $e) {
                // Log the error for debugging but don't crash the page
                \Log::warning('CTEVT API timeout or connection error', [
                    'type' => $isResult ? 'result' : 'general',
                    'error' => $e->getMessage(),
                ]);
                
                return [
                    'source' => $isResult ? 'result' : 'general',
                    'source_state' => 'unavailable',
                    'page_url' => $pageUrl,
                    'items' => [],
                ];
            }
        });
    }

    private function buildCtevtNoticeFeedParams(bool $isResult, int $limit): array
    {
        return [
            'draw' => 1,
            'columns' => [
                ['data' => 'serial_no', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'updated_date', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'notice_title', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'notice_files', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'publisher', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
            'start' => 0,
            'length' => $limit,
            'search' => [
                'value' => '',
                'regex' => 'false',
            ],
            'tab_id' => 'tab-0',
            'is_result' => $isResult ? '1' : '0',
        ];
    }

    private function mapCtevtNoticeRow(array $row, int $index, bool $isResult): ?array
    {
        $titleHtml = trim((string) ($row['notice_title'] ?? ''));

        if ($titleHtml === '') {
            return null;
        }

        [$titleUrl, $titleText] = $this->extractFirstHtmlLink($titleHtml);
        $files = $this->extractHtmlLinks((string) ($row['notice_files'] ?? ''));

        return [
            'notice_cd' => trim((string) ($row['notice_cd'] ?? '')),
            'serial_no' => (int) ($row['serial_no'] ?? ($index + 1)),
            'title' => $titleText ?: $this->cleanText(strip_tags($titleHtml)),
            'url' => $titleUrl,
            'updated_date' => trim((string) ($row['updated_date'] ?? '')),
            'publisher' => trim((string) ($row['publisher'] ?? '')),
            'files' => $files,
            'files_count' => count($files),
            'source' => $isResult ? 'result' : 'general',
        ];
    }

    private function extractFirstHtmlLink(string $html): array
    {
        if (! preg_match('/<a\b[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches)) {
            return [null, $this->cleanText(strip_tags($html))];
        }

        return [
            html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $this->cleanText(html_entity_decode(strip_tags($matches[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
        ];
    }

    private function extractHtmlLinks(string $html): array
    {
        if (! preg_match_all('/<a\b[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return array_values(array_filter(array_map(function (array $match) {
            $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $label = $this->cleanText(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($url === '' && $label === '') {
                return null;
            }

            return [
                'url' => $url,
                'label' => $label,
            ];
        }, $matches)));
    }

    public function getRecentDownloads(int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("public:recent_downloads:{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Download::with('department:id,name,code')
                ->whereNull('department_id')  // Home page: college-wide downloads only
                ->where('is_public', true)
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
        return Cache::remember('public:site_settings_collection', self::CACHE_TTL, function () {
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
                'public:teachers',
                'public:department_hods',
                'public:leadership',
                'public:site_settings',
                'public:site_settings_collection',
                'public:gallery',
                'public:question_bank',
                'public:news_events:5',
                'public:homepage_stats',
                'public:navigation_courses',
                'public:recent_downloads:4',
                'public:ctevt_result_form',
                'public:ctevt_notices:general:5',
                'public:ctevt_notices:result:5',
                'public:ctevt_notices:general:10',
                'public:ctevt_notices:result:10',
            ];

            foreach (array_keys(SiteSetting::managedPageDefinitions()) as $slug) {
                $cacheKeys[] = "public:page:{$slug}";
            }

            foreach ($cacheKeys as $cacheKey) {
                Cache::forget($cacheKey);
            }

            foreach (Department::query()->pluck('slug') as $slug) {
                Cache::forget("public:department:{$slug}");
            }

            Cache::forget('brand:site_logo');

            app(self::class)->bustAlumniCaches();
            app(self::class)->bustStaffCaches();
        } else {
            Cache::forget("public:{$key}");
        }
    }

    private function buildHodPeopleProfile(int $departmentId): array
    {
        $department = Department::query()
            ->active()
            ->with([
                'hod:id,name,email,phone,address,avatar,gender,dob,is_active',
                'programs:id,department_id,name,code,total_semesters',
            ])
            ->withCount(['programs', 'teachers'])
            ->findOrFail($departmentId, ['id', 'name', 'code', 'slug', 'description', 'photo', 'seat_capacity', 'hod_id']);

        $hod = $department->hod;

        if (! $hod) {
            abort(404);
        }

        $teacher = Teacher::query()
            ->with([
                'department:id,name,code,slug,description,seat_capacity',
            ])
            ->where('user_id', $hod->id)
            ->first();

        $teacherSubjects = $teacher
            ? $teacher->currentSubjects()->map(fn ($subject) => [
                'name' => $subject->name,
                'code' => $subject->code,
            ])->values()->all()
            : [];

        return [
            'type' => 'hod',
            'type_label' => 'Head of Department',
            'name' => $hod->name ?? $department->name,
            'designation' => 'Head of Department',
            'avatar_url' => $hod->avatar_url ?? $this->buildFallbackAvatar($hod->name ?? $department->name),
            'summary' => $department->description ?: 'Head of Department for ' . $department->name . '.',
            'department' => [
                'name' => $department->name,
                'slug' => $department->slug,
                'code' => $department->code,
                'description' => $department->description,
                'seat_capacity' => $department->seat_capacity,
                'programs_count' => $department->programs_count,
                'teachers_count' => $department->teachers_count,
                'programs' => $department->programs->map(fn ($program) => [
                    'name' => $program->name,
                    'code' => $program->code,
                    'total_semesters' => $program->total_semesters,
                ])->values()->all(),
            ],
            'highlights' => $this->buildDetailRows([
                ['label' => 'Department', 'value' => $department->name],
                ['label' => 'Code', 'value' => $department->code],
                ['label' => 'Programs', 'value' => $department->programs_count],
                ['label' => 'Teachers', 'value' => $department->teachers_count],
            ]),
            'sections' => [
                $this->buildProfileSection('Contact Details', [
                    ['label' => 'Email', 'value' => $hod->email],
                    ['label' => 'Phone', 'value' => $hod->phone],
                    ['label' => 'Address', 'value' => $hod->address],
                ]),
                $this->buildProfileSection('Department Details', [
                    ['label' => 'Department Name', 'value' => $department->name],
                    ['label' => 'Department Code', 'value' => $department->code],
                    ['label' => 'Seat Capacity', 'value' => $department->seat_capacity],
                    ['label' => 'Description', 'value' => $department->description],
                ]),
            ],
            'subjects' => $teacherSubjects,
            'action_links' => [
                ['label' => 'View Department', 'href' => route('public.department.show', $department->slug)],
                ['label' => 'Department People', 'href' => route('public.people', ['department' => $department->slug])],
                ['label' => 'Back to People Directory', 'href' => route('public.people')],
            ],
        ];
    }

    private function buildTeacherPeopleProfile(int $teacherId): array
    {
        $teacher = Teacher::query()
            ->with([
                'user:id,name,email,phone,address,avatar,gender,dob,is_active',
                'department:id,name,code,slug,description,seat_capacity',
            ])
            ->findOrFail($teacherId, ['id', 'user_id', 'department_id', 'employee_id', 'designation', 'qualification', 'specialization', 'join_date', 'employment_type', 'is_active']);

        $user = $teacher->user;
        $department = $teacher->department;
        $subjects = $teacher->currentSubjects()->map(fn ($subject) => [
            'name' => $subject->name,
            'code' => $subject->code,
        ])->values()->all();

        return [
            'type' => 'teacher',
            'type_label' => 'Teacher',
            'name' => $user?->name ?? $teacher->full_name ?: 'Teacher',
            'designation' => $teacher->designation ?: 'Teacher',
            'avatar_url' => $user?->avatar_url ?? $this->buildFallbackAvatar($user?->name ?? $teacher->full_name ?: 'Teacher'),
            'summary' => $teacher->specialization ?: ($department?->description ?: 'Teacher profile and academic details.'),
            'department' => $department ? [
                'name' => $department->name,
                'slug' => $department->slug,
                'code' => $department->code,
                'description' => $department->description,
                'seat_capacity' => $department->seat_capacity,
            ] : null,
            'highlights' => $this->buildDetailRows([
                ['label' => 'Department', 'value' => $department?->name],
                ['label' => 'Employee ID', 'value' => $teacher->employee_id],
                ['label' => 'Qualification', 'value' => $teacher->qualification],
                ['label' => 'Specialization', 'value' => $teacher->specialization],
            ]),
            'sections' => [
                $this->buildProfileSection('Contact Details', [
                    ['label' => 'Email', 'value' => $user?->email],
                    ['label' => 'Phone', 'value' => $user?->phone],
                    ['label' => 'Address', 'value' => $user?->address],
                ]),
                $this->buildProfileSection('Professional Details', [
                    ['label' => 'Designation', 'value' => $teacher->designation],
                    ['label' => 'Employee ID', 'value' => $teacher->employee_id],
                    ['label' => 'Qualification', 'value' => $teacher->qualification],
                    ['label' => 'Specialization', 'value' => $teacher->specialization],
                    ['label' => 'Employment Type', 'value' => $teacher->employment_type],
                    ['label' => 'Join Date', 'value' => $this->formatDisplayDate($teacher->join_date)],
                ]),
            ],
            'subjects' => $subjects,
            'action_links' => array_values(array_filter([
                $department ? ['label' => 'View Department', 'href' => route('public.department.show', $department->slug)] : null,
                $department ? ['label' => 'Department People', 'href' => route('public.people', ['department' => $department->slug])] : null,
                ['label' => 'Back to People Directory', 'href' => route('public.people')],
            ])),
        ];
    }

    private function buildStaffPeopleProfile(int $staffId): array
    {
        $staff = Staff::query()
            ->with(['user:id,name,email,phone,address,avatar,gender,dob,is_active'])
            ->findOrFail($staffId, ['id', 'user_id', 'name', 'designation', 'department', 'email', 'phone', 'photo', 'order', 'is_active']);

        $user = $staff->user;
        $department = $this->resolveDepartmentByLabel($staff->department);
        $roleLabel = str_contains(strtolower((string) $staff->designation), 'lab') ? 'Lab Technician' : 'Staff';

        return [
            'type' => 'staff',
            'type_label' => $roleLabel,
            'name' => $staff->name ?: ($user?->name ?? 'Staff'),
            'designation' => $staff->designation ?: $roleLabel,
            'avatar_url' => $staff->photo_url ?? $this->buildFallbackAvatar($staff->name ?: ($user?->name ?? 'Staff')), 
            'summary' => $staff->designation ?: $roleLabel,
            'department' => [
                'name' => $department?->name ?? ($staff->department ?: 'General'),
                'slug' => $department?->slug,
                'code' => $department?->code,
                'description' => $department?->description,
            ],
            'highlights' => $this->buildDetailRows([
                ['label' => 'Department', 'value' => $department?->name ?? $staff->department],
                ['label' => 'Role', 'value' => $roleLabel],
                ['label' => 'Email', 'value' => $staff->email ?: $user?->email],
                ['label' => 'Phone', 'value' => $staff->phone ?: $user?->phone],
            ]),
            'sections' => [
                $this->buildProfileSection('Contact Details', [
                    ['label' => 'Email', 'value' => $staff->email ?: $user?->email],
                    ['label' => 'Phone', 'value' => $staff->phone ?: $user?->phone],
                    ['label' => 'Address', 'value' => $user?->address],
                ]),
                $this->buildProfileSection('Professional Details', [
                    ['label' => 'Designation', 'value' => $staff->designation],
                    ['label' => 'Department', 'value' => $department?->name ?? $staff->department],
                    ['label' => 'Role Type', 'value' => $roleLabel],
                    ['label' => 'Display Order', 'value' => $staff->order],
                ]),
            ],
            'subjects' => [],
            'action_links' => array_values(array_filter([
                $department ? ['label' => 'View Department', 'href' => route('public.department.show', $department->slug)] : null,
                $department ? ['label' => 'Department People', 'href' => route('public.people', ['department' => $department->slug])] : null,
                ['label' => 'Administrative Staff', 'href' => route('public.staff')],
                ['label' => 'Back to People Directory', 'href' => route('public.people')],
            ])),
        ];
    }

    private function buildProfileSection(string $title, array $rows): array
    {
        return [
            'title' => $title,
            'rows' => $this->buildDetailRows($rows),
        ];
    }

    private function buildDetailRows(array $rows): array
    {
        return array_values(array_filter($rows, static fn (array $row) => filled($row['value'] ?? null)));
    }

    private function buildFallbackAvatar(string $name, string $background = '8B0000'): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=' . $background . '&color=fff';
    }

    private function formatDisplayDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d M Y');
        }

        $formatted = trim((string) $value);

        return $formatted !== '' ? $formatted : null;
    }

    private function resolveDepartmentByLabel(?string $departmentLabel): ?Department
    {
        $normalizedLabel = mb_strtolower(trim((string) $departmentLabel));

        if ($normalizedLabel === '') {
            return null;
        }

        return Department::active()
            ->get(['id', 'name', 'code', 'slug', 'description'])
            ->first(function (Department $department) use ($normalizedLabel) {
                $name = mb_strtolower(trim((string) $department->name));
                $code = mb_strtolower(trim((string) $department->code));

                return ($name !== '' && (
                    $normalizedLabel === $name
                    || str_contains($normalizedLabel, $name)
                    || str_contains($name, $normalizedLabel)
                )) || ($code !== '' && (
                    $normalizedLabel === $code
                    || str_contains($normalizedLabel, $code)
                    || str_contains($code, $normalizedLabel)
                ));
            });
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

    private function parseCtevtResultForm(string $html, string $fallbackAction): array
    {
        $previousLibxmlSetting = libxml_use_internal_errors(true);

        try {
            $dom = new \DOMDocument();
            $dom->loadHTML($this->normalizeHtmlForDom($html));
            $xpath = new \DOMXPath($dom);

            $formNode = $xpath->query('//form[@id="frmCheckResults" or @name="frmCheckResults"]')->item(0);

            if (! $formNode) {
                $formNode = $xpath->query('//form[contains(@action, "search_results")]')->item(0);
            }

            if (! $formNode) {
                return $this->fallbackCtevtResultForm($fallbackAction);
            }

            $fields = [];
            $hiddenFields = [];

            foreach ($xpath->query('.//input[@type="hidden"]', $formNode) as $hiddenNode) {
                $hiddenName = trim((string) $hiddenNode->getAttribute('name'));

                if ($hiddenName === '') {
                    continue;
                }

                $hiddenFields[] = [
                    'name' => $hiddenName,
                    'value' => trim((string) $hiddenNode->getAttribute('value')),
                ];
            }

            foreach ($xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " row ")]',$formNode) as $rowNode) {
                $labelNode = $xpath->query('.//label', $rowNode)->item(0);
                $controlNode = $xpath->query('.//select[1] | .//input[not(@type="hidden") and not(@type="submit")][1]', $rowNode)->item(0);

                if (! $labelNode || ! $controlNode) {
                    continue;
                }

                $field = [
                    'label' => $this->cleanText($labelNode->textContent),
                    'name' => trim((string) $controlNode->getAttribute('name')) ?: trim((string) $controlNode->getAttribute('id')),
                    'id' => trim((string) $controlNode->getAttribute('id')) ?: trim((string) $controlNode->getAttribute('name')),
                    'required' => $controlNode->hasAttribute('required'),
                ];

                if (strtolower($controlNode->nodeName) === 'select') {
                    $field['type'] = 'select';
                    $field['options'] = [];

                    foreach ($xpath->query('./option', $controlNode) as $optionNode) {
                        $field['options'][] = [
                            'label' => $this->cleanText($optionNode->textContent),
                            'value' => $optionNode->getAttribute('value'),
                            'selected' => $optionNode->hasAttribute('selected'),
                        ];
                    }
                } else {
                    $field['type'] = 'input';
                    $field['input_type'] = strtolower(trim((string) $controlNode->getAttribute('type')) ?: 'text');
                    $field['placeholder'] = trim((string) $controlNode->getAttribute('placeholder'));
                }

                $fields[] = $field;
            }

            $submitNode = $xpath->query('.//input[@type="submit"]', $formNode)->item(0)
                ?? $xpath->query('.//button[@type="submit"]', $formNode)->item(0);

            $submitLabel = 'Search';

            if ($submitNode) {
                $submitLabel = $this->cleanText($submitNode->getAttribute('value') ?: $submitNode->textContent) ?: 'Search';
            }

            if ($fields === []) {
                return $this->fallbackCtevtResultForm($fallbackAction);
            }

            return [
                'title' => $this->cleanText($this->firstTextNode($xpath, $formNode, ['.//h5', './/h2', './/h1'])) ?: 'Yearly/Semester Check Results',
                'action' => trim((string) $formNode->getAttribute('action')) ?: $fallbackAction,
                'method' => strtolower(trim((string) $formNode->getAttribute('method'))) ?: 'post',
                'target' => trim((string) $formNode->getAttribute('target')) ?: '_blank',
                'autocomplete' => trim((string) $formNode->getAttribute('autocomplete')) ?: 'off',
                'fields' => $fields,
                'hidden_fields' => $hiddenFields,
                'submit' => [
                    'label' => $submitLabel,
                ],
                'source' => 'live',
                'source_state' => 'live',
            ];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }
    }

    private function parseCtevtResultFormWithRegex(string $html, string $fallbackAction): array
    {
        if (! preg_match_all('/<form\b([^>]*)>(.*?)<\/form>/is', $html, $formMatches, PREG_SET_ORDER)) {
            return $this->fallbackCtevtResultForm($fallbackAction);
        }

        foreach ($formMatches as $formMatch) {
            $formAttributes = $this->parseHtmlAttributes($formMatch[1]);
            $action = trim((string) ($formAttributes['action'] ?? ''));
            $formId = trim((string) ($formAttributes['id'] ?? ''));
            $formName = trim((string) ($formAttributes['name'] ?? ''));

            if ($formId !== 'frmCheckResults' && $formName !== 'frmCheckResults' && ! str_contains($action, 'search_results')) {
                continue;
            }

            $innerHtml = $formMatch[2];
            $fields = [];

            foreach (['src_year', 'src_level', 'exam_symbol_number', 'dob'] as $fieldId) {
                $field = $this->parseCtevtFieldFromHtml($innerHtml, $fieldId);

                if ($field) {
                    $fields[] = $field;
                }
            }

            if ($fields === []) {
                return $this->fallbackCtevtResultForm($fallbackAction);
            }

            $hiddenFields = [];

            foreach ($this->findHtmlTags($innerHtml, 'input') as $tag) {
                $hiddenAttributes = $this->parseHtmlAttributes($tag['attributes']);

                if (($hiddenAttributes['type'] ?? '') !== 'hidden') {
                    continue;
                }

                $hiddenName = trim((string) ($hiddenAttributes['name'] ?? ''));

                if ($hiddenName === '') {
                    continue;
                }

                $hiddenFields[] = [
                    'name' => $hiddenName,
                    'value' => trim((string) ($hiddenAttributes['value'] ?? '')),
                ];
            }

            $submitLabel = 'Search';

            foreach ($this->findHtmlTags($innerHtml, 'input') as $tag) {
                $submitAttributes = $this->parseHtmlAttributes($tag['attributes']);

                if (($submitAttributes['type'] ?? '') !== 'submit') {
                    continue;
                }

                $submitLabel = trim((string) ($submitAttributes['value'] ?? '')) ?: trim(strip_tags($tag['inner'])) ?: 'Search';
                break;
            }

            $title = $this->cleanText($this->firstRegexMatch($html, '/<h5[^>]*>(.*?)<\/h5>/is')) ?: 'Yearly/Semester Check Results';

            return [
                'title' => $title,
                'action' => $action ?: $fallbackAction,
                'method' => strtolower(trim((string) ($formAttributes['method'] ?? 'post'))) ?: 'post',
                'target' => trim((string) ($formAttributes['target'] ?? '_blank')) ?: '_blank',
                'autocomplete' => trim((string) ($formAttributes['autocomplete'] ?? 'off')) ?: 'off',
                'fields' => $fields,
                'hidden_fields' => $hiddenFields,
                'submit' => [
                    'label' => $submitLabel,
                ],
                'source' => 'live',
                'source_state' => 'live',
            ];
        }

        return $this->fallbackCtevtResultForm($fallbackAction);
    }

    private function parseCtevtFieldFromHtml(string $html, string $fieldId): ?array
    {
        $label = $this->cleanText($this->firstRegexMatch($html, '/<label\b[^>]*for=["\']'.preg_quote($fieldId, '/').'["\'][^>]*>(.*?)<\/label>/is'));

        $selectTag = $this->findHtmlTagByIdOrName($html, 'select', $fieldId);

        if ($selectTag) {
            $attributes = $this->parseHtmlAttributes($selectTag['attributes']);
            $options = [];

            if (preg_match_all('/<option\b([^>]*)>(.*?)<\/option>/is', $selectTag['inner'], $optionMatches, PREG_SET_ORDER)) {
                foreach ($optionMatches as $optionMatch) {
                    $optionAttributes = $this->parseHtmlAttributes($optionMatch[1]);

                    $options[] = [
                        'label' => $this->cleanText(strip_tags($optionMatch[2])),
                        'value' => trim((string) ($optionAttributes['value'] ?? '')),
                        'selected' => array_key_exists('selected', $optionAttributes),
                    ];
                }
            }

            return [
                'label' => $label ?: $fieldId,
                'name' => trim((string) ($attributes['name'] ?? $fieldId)) ?: $fieldId,
                'id' => trim((string) ($attributes['id'] ?? $fieldId)) ?: $fieldId,
                'type' => 'select',
                'required' => array_key_exists('required', $attributes),
                'options' => $options,
            ];
        }

        $inputTag = $this->findHtmlTagByIdOrName($html, 'input', $fieldId);

        if (! $inputTag) {
            return null;
        }

        $attributes = $this->parseHtmlAttributes($inputTag['attributes']);

        return [
            'label' => $label ?: $fieldId,
            'name' => trim((string) ($attributes['name'] ?? $fieldId)) ?: $fieldId,
            'id' => trim((string) ($attributes['id'] ?? $fieldId)) ?: $fieldId,
            'type' => 'input',
            'input_type' => strtolower(trim((string) ($attributes['type'] ?? 'text'))) ?: 'text',
            'placeholder' => trim((string) ($attributes['placeholder'] ?? '')),
            'required' => array_key_exists('required', $attributes),
        ];
    }

    private function findHtmlTags(string $html, string $tagName): array
    {
        $pattern = $tagName === 'select'
            ? '/<select\b([^>]*)>(.*?)<\/select>/is'
            : '/<input\b([^>]*)>/is';

        if (! preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return array_map(static function (array $match) {
            return [
                'attributes' => $match[1] ?? '',
                'inner' => $match[2] ?? '',
            ];
        }, $matches);
    }

    private function findHtmlTagByIdOrName(string $html, string $tagName, string $fieldId): ?array
    {
        foreach ($this->findHtmlTags($html, $tagName) as $tag) {
            $attributes = $this->parseHtmlAttributes($tag['attributes']);
            $name = trim((string) ($attributes['name'] ?? ''));
            $id = trim((string) ($attributes['id'] ?? ''));

            if ($name === $fieldId || $id === $fieldId) {
                return $tag;
            }
        }

        return null;
    }

    private function parseHtmlAttributes(string $attributesString): array
    {
        $attributes = [];

        if (preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*("([^"]*)"|\'([^\']*)\')/s', $attributesString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributes[strtolower($match[1])] = $match[3] !== '' ? $match[3] : $match[4];
            }
        }

        if (preg_match_all('/\b(required|selected|disabled|checked)\b/i', $attributesString, $booleanMatches)) {
            foreach ($booleanMatches[1] as $booleanAttribute) {
                $attributes[strtolower($booleanAttribute)] = true;
            }
        }

        return $attributes;
    }

    private function firstRegexMatch(string $html, string $pattern): ?string
    {
        if (! preg_match($pattern, $html, $matches)) {
            return null;
        }

        return $matches[1] ?? null;
    }

    private function fallbackCtevtResultForm(string $fallbackAction): array
    {
        // Generate years dynamically from current BS year down to 2077
        $currentBsYear = (int) bsDate(now(), 'Y');
        $yearOptions = [['label' => '-- Select --', 'value' => '']];
        
        // Generate years from current year down to 2077
        for ($year = $currentBsYear; $year >= 2077; $year--) {
            $yearOptions[] = ['label' => (string) $year, 'value' => (string) $year];
        }
        
        return [
            'title' => 'Yearly/Semester Check Results',
            'action' => $fallbackAction,
            'method' => 'post',
            'target' => '_blank',
            'autocomplete' => 'off',
            'fields' => [
                [
                    'label' => 'Examination Year',
                    'name' => 'src_year',
                    'id' => 'src_year',
                    'type' => 'select',
                    'required' => true,
                    'options' => $yearOptions,
                ],
                [
                    'label' => 'Level',
                    'name' => 'src_level',
                    'id' => 'src_level',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        ['label' => '-- Select --', 'value' => ''],
                        ['label' => 'Pre-diploma', 'value' => '2'],
                        ['label' => 'Diploma/PCL', 'value' => '3'],
                    ],
                ],
                [
                    'label' => 'Symbol Number',
                    'name' => 'exam_symbol_number',
                    'id' => 'exam_symbol_number',
                    'type' => 'input',
                    'input_type' => 'text',
                    'placeholder' => 'Enter your symbol number',
                    'required' => true,
                ],
                [
                    'label' => 'Date of Birth (B.S.)',
                    'name' => 'dob',
                    'id' => 'dob',
                    'type' => 'input',
                    'input_type' => 'text',
                    'placeholder' => 'YYYY-MM-DD (B.S.)',
                    'required' => true,
                ],
            ],
            'hidden_fields' => [],
            'submit' => [
                'label' => 'Search',
            ],
            'source' => 'fallback',
            'source_state' => 'fallback',
        ];
    }

    private function cleanText(?string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '';
    }

    private function firstTextNode(\DOMXPath $xpath, \DOMNode $contextNode, array $queries): ?string
    {
        foreach ($queries as $query) {
            $node = $xpath->query($query, $contextNode)->item(0);

            if ($node) {
                return trim((string) $node->textContent);
            }
        }

        return null;
    }

    private function normalizeHtmlForDom(string $html): string
    {
        return '<?xml encoding="UTF-8">'.$html;
    }
}
