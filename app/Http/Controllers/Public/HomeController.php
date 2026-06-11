<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicDataService;
use App\Services\SeoService;
use Illuminate\Http\Request;

/**
 * HomeController — renders public Blade views.
 * NEVER queries the database directly.
 * ALL data flows through: HomeController → PublicDataService → DB
 */
class HomeController extends Controller
{
    public function __construct(private PublicDataService $service) {}

    public function index()
    {
        $data = $this->service->getHomepageData();
        $leadership = $this->service->getLeadership();
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');

        // Home welcome box should use its own managed setting.
        $welcomeMessage = trim((string) optional($siteSettings->get('welcome_message'))->value);
        if ($welcomeMessage !== '') {
            $siteSettings->put('what_is_mmp', $siteSettings->get('welcome_message'));
        }

        // Principal section should prioritize Web Control's principal message.
        $principalName = trim((string) optional($siteSettings->get('principal_name'))->value);
        $principalMessage = trim((string) optional($siteSettings->get('principals_message'))->value);
        $principalPhoto = trim((string) optional($siteSettings->get('principal_photo'))->value);

        if (isset($leadership['principals'])) {
            $principals = collect($leadership['principals']);
            $currentPrincipal = $principals->firstWhere('is_current', true);

            if (!$currentPrincipal) {
                $currentPrincipal = (object) [
                    'name' => 'Principal',
                    'designation' => 'Principal, MMP',
                    'avatar' => null,
                    'message' => null,
                    'is_current' => true,
                ];
                $principals = collect([$currentPrincipal])->merge($principals);
            }

            if ($principalName !== '') {
                $currentPrincipal->name = ltrim($principalName, "- \t");
            }
            if ($principalMessage !== '') {
                $currentPrincipal->message = $principalMessage;
            }
            if ($principalPhoto !== '') {
                if (filter_var($principalPhoto, FILTER_VALIDATE_URL)) {
                    $currentPrincipal->avatar_url = $principalPhoto;
                } else {
                    $currentPrincipal->avatar = $principalPhoto;
                    $currentPrincipal->avatar_url = asset('storage/' . $principalPhoto);
                }
            }

            $leadership['principals'] = $principals;
        }

        $staff          = $this->service->getStaff();
        $newsEvents     = $this->service->getLatestNewsEvents(5);
        $recentDownloads = $this->service->getRecentDownloads(4);
        $stats          = $this->service->getHomepageStats();

        $seo = SeoService::home();

        return response()
            ->view('public.home', array_merge($data, compact('leadership', 'siteSettings', 'staff', 'newsEvents', 'recentDownloads', 'stats', 'seo')))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function notices(Request $request)
    {
        $activeType = in_array($request->string('type')->toString(), ['general', 'exam', 'department', 'program', 'academic', 'all'], true)
            ? $request->string('type')->toString()
            : 'all';

        $notices = $this->service->getNotices(15, $activeType);
        $seo     = SeoService::notices();

        return view('public.notices', compact('notices', 'activeType', 'seo'));
    }

    public function noticeShow(string $slug)
    {
        $notice = $this->service->getPublishedItemBySlug($slug);

        if (in_array($notice->type, ['news', 'event'], true)) {
            return redirect()->route('public.news-events.show', $notice->slug);
        }

        $relatedNotices = $this->service->getRelatedItemsByType($notice->type, $notice->id, 5);
        $seo            = SeoService::notice($notice);

        return view('public.notice-show', compact('notice', 'relatedNotices', 'seo'));
    }

    public function newsEventShow(string $slug)
    {
        $notice = $this->service->getPublishedItemBySlug($slug);

        if (! in_array($notice->type, ['news', 'event'], true)) {
            return redirect()->route('public.notice.show', $notice->slug);
        }

        $relatedNotices = $this->service->getRelatedItemsByType($notice->type, $notice->id, 5);
        $seo            = SeoService::newsEvent($notice);

        return view('public.news-event-show', compact('notice', 'relatedNotices', 'seo'));
    }

    public function departments()
    {
        $departments = $this->service->getDepartments();
        $seo         = SeoService::departments();

        return view('public.departments', compact('departments', 'seo'));
    }

    public function departmentShow(string $slug)
    {
        $department = $this->service->getDepartmentBySlug($slug);
        $seo        = SeoService::department($department);

        return view('public.department-show', compact('department', 'seo'));
    }

    public function programShow(string $departmentSlug, string $programSlug)
    {
        $data = $this->service->getProgramBySlug($departmentSlug, $programSlug);

        $program = $data['program'] ?? null;
        $dept    = $data['department'] ?? null;

        $seo = SeoService::build([
            'title'       => ($program->name ?? 'Program') . ($dept ? ' — ' . $dept->name : ''),
            'description' => $program->description ?? 'CTEVT diploma program at Manmohan Memorial Polytechnic, Morang Nepal.',
            'canonical'   => url("/departments/{$departmentSlug}/{$programSlug}"),
            'breadcrumbs' => [
                ['name' => 'Home',                   'url' => url('/')],
                ['name' => 'Departments',            'url' => url('/departments')],
                ['name' => $dept->name ?? 'Dept',   'url' => url("/departments/{$departmentSlug}")],
                ['name' => $program->name ?? 'Program', 'url' => url("/departments/{$departmentSlug}/{$programSlug}")],
            ],
        ]);

        return view('public.program-show', array_merge($data, compact('seo')));
    }

    public function downloads(Request $request)
    {
        $category   = trim($request->string('category')->toString());
        $department = trim($request->string('department')->toString());
        $search     = trim($request->string('search')->toString());

        $downloads = $this->service->getDownloads($category !== '' ? $category : null);

        if ($department !== '') {
            $downloads = $downloads->filter(function ($download) use ($department) {
                return $download->department?->code === $department || $download->department?->name === $department;
            })->values();
        }

        if ($search !== '') {
            $downloads = $downloads->filter(function ($download) use ($search) {
                return stripos($download->title, $search) !== false;
            })->values();
        }

        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $seo         = SeoService::downloads();

        return view('public.downloads', compact('downloads', 'departments', 'seo'));
    }

    public function downloadFile(\App\Models\Download $download)
    {
        abort_unless($download->is_public, 403);
        abort_unless($download->file_path, 404);

        $disk = $download->storageDisk();
        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($download->file_path), 404);

        $filename = $download->file_name ?: basename($download->file_path);

        return \Illuminate\Support\Facades\Storage::disk($disk)->response($download->file_path, $filename, [
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }

    public function page(string $slug)
    {
        $page         = $this->service->getPage($slug);
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        $seo          = SeoService::page($page);

        return view('public.content-page', compact('page', 'siteSettings', 'seo'));
    }

    public function leadership()
    {
        $leadership = $this->service->getLeadership();
        $seo        = SeoService::leadership();

        return view('public.leadership', array_merge($leadership, compact('seo')));
    }

    public function facilities(Request $request)
    {
        $department  = $request->query('department');
        $facilities  = $this->service->getFacilities($department);
        $departments = $this->service->getDepartments();
        $seo         = SeoService::facilities();

        return view('public.facilities', compact('facilities', 'departments', 'seo'));
    }

    public function newsEvents(Request $request)
    {
        $items = $this->service->getNewsEvents(12);
        $seo   = SeoService::newsEvents();

        return view('public.news-events', compact('items', 'seo'));
    }

    public function gallery()
    {
        $media = $this->service->getGalleryMedia();
        $seo   = SeoService::gallery();

        return view('public.gallery', compact('media', 'seo'));
    }

    public function result()
    {
        $resultForm = $this->service->getCtevtResultForm();
        $seo        = SeoService::result();

        return view('public.result', compact('resultForm', 'seo'));
    }

    public function resultSubmit(Request $request)
    {
        $resultForm = $this->service->getCtevtResultForm();
        $fields     = collect($resultForm['fields'] ?? []);

        $rules = [];
        foreach ($fields as $field) {
            $fieldName = $field['name'] ?? '';
            if ($fieldName === '') continue;

            $fieldRules = [];
            if ($field['required'] ?? false) $fieldRules[] = 'required';

            if ($field['type'] === 'select') {
                $options = collect($field['options'] ?? [])->pluck('value')->filter(fn($v) => $v !== '')->toArray();
                if (!empty($options)) $fieldRules[] = 'in:' . implode(',', $options);
            } else {
                $inputType = $field['input_type'] ?? 'text';
                if ($inputType === 'text') {
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:100';
                    if ($fieldName === 'dob' || str_contains(strtolower($field['label'] ?? ''), 'date')) {
                        $fieldRules[] = 'regex:/^\d{4}-\d{2}-\d{2}$/';
                    } elseif ($fieldName === 'exam_symbol_number' || str_contains(strtolower($field['label'] ?? ''), 'symbol')) {
                        $fieldRules[] = 'regex:/^[A-Za-z0-9\-]+$/';
                    }
                }
            }

            if (!empty($fieldRules)) $rules[$fieldName] = $fieldRules;
        }

        if (empty($rules)) {
            $rules = [
                'src_year'          => ['required', 'string'],
                'src_level'         => ['required', 'string'],
                'exam_symbol_number' => ['required', 'string', 'max:50'],
                'dob'               => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            ];
        }

        $request->validate($rules);

        $actionUrl = $resultForm['action'] ?? config('services.ctevt_result.url', 'https://itms.ctevt.org.np:5580/search_results');

        return response('', 307)->header('Location', $actionUrl);
    }

    public function questionBank()
    {
        $downloads = $this->service->getQuestionBankDownloads();
        $seo       = SeoService::build([
            'title'       => 'Question Bank',
            'description' => 'Download previous year question papers and question bank from Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',          'url' => url('/')],
                ['name' => 'Question Bank', 'url' => url('/question-bank')],
            ],
        ]);

        return view('public.question-bank', compact('downloads', 'seo'));
    }

    public function people(Request $request)
    {
        $departments        = $this->service->getDepartments()->sortBy('name')->values();
        $departmentHods     = $this->service->getDepartmentHods()->keyBy('id');
        $teachers           = $this->service->getTeachers();
        $staffMembers       = $this->service->getStaff();

        $selectedDepartmentSlug = trim($request->string('department')->toString());
        if ($selectedDepartmentSlug === 'all') $selectedDepartmentSlug = '';

        $selectedDepartment = $selectedDepartmentSlug !== ''
            ? $departments->firstWhere('slug', $selectedDepartmentSlug)
            : null;

        if ($selectedDepartmentSlug !== '' && ! $selectedDepartment) $selectedDepartmentSlug = '';

        $resolveDepartment = function (?string $departmentName) use ($departments) {
            $n = mb_strtolower(trim((string) $departmentName));
            if ($n === '') return null;
            foreach ($departments as $department) {
                $name = mb_strtolower(trim((string) $department->name));
                $code = mb_strtolower(trim((string) $department->code));
                if ($name !== '' && (str_contains($n, $name) || str_contains($name, $n))) return $department;
                if ($code !== '' && (str_contains($n, $code) || str_contains($code, $n))) return $department;
            }
            return null;
        };

        $isLabTech = fn ($m): bool => str_contains(mb_strtolower((($m->designation ?? '') . ' ' . ($m->department ?? ''))), 'lab');

        $avatarUrl = fn (string $name, string $bg = '8B0000'): string =>
            'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=' . $bg . '&color=fff';

        $buildHodCard = fn ($hod, $dept) => (object)[
            'id' => $dept->id, 'profile_type' => 'hod',
            'profile_url' => route('public.people.profile', ['type' => 'hod', 'id' => $dept->id]),
            'name' => trim((string)($hod->name ?? $dept->name)) ?: $dept->name,
            'designation' => 'Head of Department', 'department' => $dept->name,
            'department_slug' => $dept->slug,
            'photo_url' => $hod->avatar_url ?? $avatarUrl($hod->name ?? $dept->name),
        ];

        $buildTeacherCard = fn ($t) => (object)[
            'id' => $t->id, 'profile_type' => 'teacher',
            'profile_url' => route('public.people.profile', ['type' => 'teacher', 'id' => $t->id]),
            'name' => trim((string)($t->user?->name ?: $t->full_name ?: 'Teacher')),
            'designation' => $t->designation ?: 'Teacher',
            'department' => $t->department?->name, 'department_slug' => $t->department?->slug,
            'photo_url' => $t->user?->avatar_url ?? $avatarUrl($t->user?->name ?: $t->full_name ?: 'Teacher'),
        ];

        $buildStaffCard = fn ($m, string $fd = 'Staff', ?object $dept = null) => (object)[
            'id' => $m->id, 'profile_type' => 'staff',
            'profile_url' => route('public.people.profile', ['type' => 'staff', 'id' => $m->id]),
            'name' => trim((string)($m->name ?? $fd)) ?: $fd,
            'designation' => $m->designation ?: $fd,
            'department' => $dept?->name ?: ($m->department ?: null),
            'department_slug' => $dept?->slug,
            'photo_url' => $m->photo_url ?? $avatarUrl($m->name ?? $fd),
        ];

        $departmentSections = $departments->map(function ($dept) use ($departmentHods, $teachers, $staffMembers, $resolveDepartment, $isLabTech, $buildHodCard, $buildTeacherCard, $buildStaffCard) {
            $hodDept = $departmentHods->get($dept->id);
            $hodCard = $hodDept?->hod ? $buildHodCard($hodDept->hod, $dept) : null;
            $teacherCards = $teachers->filter(fn($t) => (int)$t->department_id === (int)$dept->id)->map($buildTeacherCard)->values();
            $assignedStaff = $staffMembers->filter(fn($m) => ($rd = $resolveDepartment($m->department)) && (int)$rd->id === (int)$dept->id)->values();
            $labTechCards = $assignedStaff->filter($isLabTech)->map(fn($m) => $buildStaffCard($m, 'Lab Tech', $dept))->values();
            $staffCards   = $assignedStaff->reject($isLabTech)->map(fn($m) => $buildStaffCard($m, 'Staff', $dept))->values();
            $count = ($hodCard ? 1 : 0) + $teacherCards->count() + $labTechCards->count() + $staffCards->count();
            return (object)['department' => $dept, 'hod' => $hodCard, 'teachers' => $teacherCards, 'staff' => $staffCards, 'labtechs' => $labTechCards, 'count' => $count];
        })->values();

        $otherMembers = $staffMembers->filter(fn($m) => $resolveDepartment($m->department) === null)->values();
        $otherLabTechs = $otherMembers->filter($isLabTech)->map(fn($m) => $buildStaffCard($m, 'Lab Tech'))->values();
        $otherStaff    = $otherMembers->reject($isLabTech)->map(fn($m) => $buildStaffCard($m, 'Staff'))->values();
        $otherCount    = $otherLabTechs->count() + $otherStaff->count();

        $visibleDepartmentSections = $selectedDepartment
            ? $departmentSections->filter(fn($s) => (int)$s->department->id === (int)$selectedDepartment->id)->values()
            : $departmentSections->filter(fn($s) => $s->count > 0)->values();

        $activeDepartmentLabel = $selectedDepartment?->name ?? 'All Departments';
        $totalCount = $departmentSections->sum('count') + $otherCount;

        $seo = SeoService::people();

        return view('public.people', compact(
            'departments', 'selectedDepartmentSlug', 'selectedDepartment',
            'activeDepartmentLabel', 'departmentSections', 'visibleDepartmentSections',
            'otherLabTechs', 'otherStaff', 'otherCount', 'totalCount', 'seo'
        ));
    }

    public function staff(Request $request)
    {
        $directory = $this->service->getPublicStaffDirectory(
            search: $request->string('search')->toString() ?: null,
            department: $request->string('department')->toString() ?: null,
            designation: $request->string('designation')->toString() ?: null,
            employmentStatus: $request->string('employment_status')->toString() ?: null,
            joinedYear: $request->string('joined_year')->toString() ?: null,
            featured: $request->string('featured')->toString() ?: null,
            perPage: 12,
        );

        $seo = SeoService::staff();

        return view('public.staff', array_merge($directory, compact('seo')));
    }

    public function staffProfile(int $id)
    {
        $staff = $this->service->getPublicStaffProfile($id);

        $seo = SeoService::build([
            'title'       => $staff->name ?? 'Staff Profile',
            'description' => ($staff->designation ?? 'Staff') . ' at Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',  'url' => url('/')],
                ['name' => 'Staff', 'url' => url('/staff')],
                ['name' => $staff->name ?? 'Profile', 'url' => url('/staff/' . $id)],
            ],
        ]);

        return view('public.staff-profile', compact('staff', 'seo'));
    }

    public function peopleProfile(string $type, int $id)
    {
        $normalizedType = strtolower($type);
        if (! in_array($normalizedType, ['hod', 'teacher', 'staff'], true)) abort(404);

        $profile     = $this->service->getPeopleProfile($normalizedType, $id);
        $departments = $this->service->getDepartments();

        $seo = SeoService::build([
            'title'       => $profile->name ?? 'Profile',
            'description' => ($profile->designation ?? ucfirst($normalizedType)) . ' at Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',   'url' => url('/')],
                ['name' => 'People', 'url' => url('/people')],
                ['name' => $profile->name ?? 'Profile', 'url' => url("/people/{$type}/{$id}")],
            ],
        ]);

        return view('public.people-profile', compact('profile', 'departments', 'seo'));
    }

    public function contact()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        $seo          = SeoService::contact();

        return view('public.contact', compact('siteSettings', 'seo'));
    }

    public function alumniDirectory()
    {
        $alumni = $this->service->getAlumniDirectory(
            request('department') ? (int) request('department') : null,
            request('search'),
            request('year'),
        );
        $departments     = $this->service->getDepartments();
        $graduationYears = $this->service->getAlumniGraduationYears();
        $seo             = SeoService::alumni();

        return view('public.alumni', compact('alumni', 'departments', 'graduationYears', 'seo'));
    }

    public function alumniProfile(int $id)
    {
        $alumnus = $this->service->getAlumniProfile($id);

        $seo = SeoService::build([
            'title'       => $alumnus->user?->name ?? 'Alumni Profile',
            'description' => 'MMP Alumni — ' . ($alumnus->user?->name ?? '') . '. Graduate of Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',   'url' => url('/')],
                ['name' => 'Alumni', 'url' => url('/alumni')],
                ['name' => $alumnus->user?->name ?? 'Profile', 'url' => url('/alumni/' . $id)],
            ],
        ]);

        return view('public.alumni-profile', compact('alumnus', 'seo'));
    }

    public function about()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        $seo          = SeoService::build([
            'title'       => 'About Manmohan Memorial Polytechnic',
            'description' => 'Learn about Manmohan Memorial Polytechnic (MMP) — founding history, vision, mission and objectives.',
            'breadcrumbs' => [
                ['name' => 'Home',  'url' => url('/')],
                ['name' => 'About', 'url' => url('/about')],
            ],
        ]);

        return view('public.about', compact('siteSettings', 'seo'));
    }
}

    public function index()
    {
        $data = $this->service->getHomepageData();
        $leadership = $this->service->getLeadership();
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');

        // Home welcome box should use its own managed setting.
        $welcomeMessage = trim((string) optional($siteSettings->get('welcome_message'))->value);
        if ($welcomeMessage !== '') {
            $siteSettings->put('what_is_mmp', $siteSettings->get('welcome_message'));
        }

        // Principal section should prioritize Web Control's principal message.
        $principalName = trim((string) optional($siteSettings->get('principal_name'))->value);
        $principalMessage = trim((string) optional($siteSettings->get('principals_message'))->value);
        $principalPhoto = trim((string) optional($siteSettings->get('principal_photo'))->value);

        if (isset($leadership['principals'])) {
            $principals = collect($leadership['principals']);
            $currentPrincipal = $principals->firstWhere('is_current', true);

            if (!$currentPrincipal) {
                $currentPrincipal = (object) [
                    'name' => 'Principal',
                    'designation' => 'Principal, MMP',
                    'avatar' => null,
                    'message' => null,
                    'is_current' => true,
                ];
                $principals = collect([$currentPrincipal])->merge($principals);
            }

            if ($principalName !== '') {
                $currentPrincipal->name = ltrim($principalName, "- \t");
            }
            if ($principalMessage !== '') {
                $currentPrincipal->message = $principalMessage;
            }
            if ($principalPhoto !== '') {
                // Set avatar_url directly if it's a full URL, otherwise treat as storage path
                if (filter_var($principalPhoto, FILTER_VALIDATE_URL)) {
                    $currentPrincipal->avatar_url = $principalPhoto;
                } else {
                    $currentPrincipal->avatar = $principalPhoto;
                    $currentPrincipal->avatar_url = asset('storage/' . $principalPhoto);
                }
            }

            $leadership['principals'] = $principals;
        }

        $staff = $this->service->getStaff();
        $newsEvents = $this->service->getLatestNewsEvents(5);
        $recentDownloads = $this->service->getRecentDownloads(4);
        $stats = $this->service->getHomepageStats();

        return response()
            ->view('public.home', array_merge($data, compact('leadership', 'siteSettings', 'staff', 'newsEvents', 'recentDownloads', 'stats')))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function notices(Request $request)
    {
        $activeType = in_array($request->string('type')->toString(), ['general', 'exam', 'department', 'program', 'academic', 'all'], true)
            ? $request->string('type')->toString()
            : 'all';

        $notices = $this->service->getNotices(15, $activeType);

        return view('public.notices', compact('notices', 'activeType'));
    }

    public function noticeShow(string $slug)
    {
        $notice = $this->service->getPublishedItemBySlug($slug);

        if (in_array($notice->type, ['news', 'event'], true)) {
            return redirect()->route('public.news-events.show', $notice->slug);
        }

        $relatedNotices = $this->service->getRelatedItemsByType($notice->type, $notice->id, 5);

        return view('public.notice-show', compact('notice', 'relatedNotices'));
    }

    public function newsEventShow(string $slug)
    {
        $notice = $this->service->getPublishedItemBySlug($slug);

        if (! in_array($notice->type, ['news', 'event'], true)) {
            return redirect()->route('public.notice.show', $notice->slug);
        }

        $relatedNotices = $this->service->getRelatedItemsByType($notice->type, $notice->id, 5);

        return view('public.news-event-show', compact('notice', 'relatedNotices'));
    }

    public function departments()
    {
        $departments = $this->service->getDepartments();
        return view('public.departments', compact('departments'));
    }

    public function departmentShow(string $slug)
    {
        $department = $this->service->getDepartmentBySlug($slug);
        return view('public.department-show', compact('department'));
    }

    public function programShow(string $departmentSlug, string $programSlug)
    {
        $data = $this->service->getProgramBySlug($departmentSlug, $programSlug);
        return view('public.program-show', $data);
    }

    public function downloads(Request $request)
    {
        $category = trim($request->string('category')->toString());
        $department = trim($request->string('department')->toString());
        $search = trim($request->string('search')->toString());
        
        $downloads = $this->service->getDownloads($category !== '' ? $category : null);
        
        // Filter by department if specified
        if ($department !== '') {
            $downloads = $downloads->filter(function ($download) use ($department) {
                return $download->department?->code === $department || $download->department?->name === $department;
            })->values();
        }
        
        // Filter by search term if specified
        if ($search !== '') {
            $downloads = $downloads->filter(function ($download) use ($search) {
                return stripos($download->title, $search) !== false;
            })->values();
        }
        
        // Get all departments for filter dropdown
        $departments = \App\Models\Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        return view('public.downloads', compact('downloads', 'departments'));
    }

    public function downloadFile(\App\Models\Download $download)
    {
        // Only allow downloading public files
        abort_unless($download->is_public, 403);
        abort_unless($download->file_path, 404);

        $disk = $download->storageDisk();
        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($download->file_path), 404);
        
        $filename = $download->file_name ?: basename($download->file_path);

        return \Illuminate\Support\Facades\Storage::disk($disk)->response($download->file_path, $filename, [
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }

    public function page(string $slug)
    {
        $page = $this->service->getPage($slug);
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');

        return view('public.content-page', compact('page', 'siteSettings'));
    }

    public function leadership()
    {
        $leadership = $this->service->getLeadership();
        return view('public.leadership', $leadership);
    }

    public function facilities(Request $request)
    {
        $department = $request->query('department');
        $facilities = $this->service->getFacilities($department);
        $departments = $this->service->getDepartments();
        return view('public.facilities', compact('facilities', 'departments'));
    }

    public function newsEvents(Request $request)
    {
        $items = $this->service->getNewsEvents(12);

        return view('public.news-events', compact('items'));
    }

    public function gallery()
    {
        $media = $this->service->getGalleryMedia();
        return view('public.gallery', compact('media'));
    }

    public function result()
    {
        $resultForm = $this->service->getCtevtResultForm();

        return view('public.result', compact('resultForm'));
    }

    public function resultSubmit(Request $request)
    {
        // Get the form structure to build dynamic validation rules
        $resultForm = $this->service->getCtevtResultForm();
        $fields = collect($resultForm['fields'] ?? []);
        
        // Build validation rules dynamically based on the form structure
        $rules = [];
        foreach ($fields as $field) {
            $fieldName = $field['name'] ?? '';
            if ($fieldName === '') {
                continue;
            }
            
            $fieldRules = [];
            
            // Add required rule if field is required
            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            }
            
            // Add type-specific rules
            if ($field['type'] === 'select') {
                // For select fields, validate against available options
                $options = collect($field['options'] ?? [])
                    ->pluck('value')
                    ->filter(fn($v) => $v !== '')
                    ->toArray();
                
                if (!empty($options)) {
                    $fieldRules[] = 'in:' . implode(',', $options);
                }
            } else {
                // For input fields
                $inputType = $field['input_type'] ?? 'text';
                
                if ($inputType === 'text') {
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:100';
                    
                    // Special validation for date fields
                    if ($fieldName === 'dob' || str_contains(strtolower($field['label'] ?? ''), 'date')) {
                        $fieldRules[] = 'regex:/^\d{4}-\d{2}-\d{2}$/';
                    }
                    // Special validation for symbol number
                    else if ($fieldName === 'exam_symbol_number' || str_contains(strtolower($field['label'] ?? ''), 'symbol')) {
                        $fieldRules[] = 'regex:/^[A-Za-z0-9\-]+$/';
                    }
                }
            }
            
            if (!empty($fieldRules)) {
                $rules[$fieldName] = $fieldRules;
            }
        }
        
        // Fallback to basic validation if no rules were generated
        if (empty($rules)) {
            $rules = [
                'src_year' => ['required', 'string'],
                'src_level' => ['required', 'string'],
                'exam_symbol_number' => ['required', 'string', 'max:50'],
                'dob' => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            ];
        }
        
        $request->validate($rules);
        
        // Get the form action URL (use dynamic URL from CTEVT or fallback to config)
        $actionUrl = $resultForm['action'] ?? config('services.ctevt_result.url', 'https://itms.ctevt.org.np:5580/search_results');
        
        // Return a 307 redirect to the CTEVT result page
        return response('', 307)->header('Location', $actionUrl);
    }

    public function questionBank()
    {
        $downloads = $this->service->getQuestionBankDownloads();
        return view('public.question-bank', compact('downloads'));
    }

    public function people(Request $request)
    {
        $departments = $this->service->getDepartments()->sortBy('name')->values();
        $departmentHods = $this->service->getDepartmentHods()->keyBy('id');
        $teachers = $this->service->getTeachers();
        $staffMembers = $this->service->getStaff();

        $selectedDepartmentSlug = trim($request->string('department')->toString());

        if ($selectedDepartmentSlug === 'all') {
            $selectedDepartmentSlug = '';
        }

        $selectedDepartment = $selectedDepartmentSlug !== ''
            ? $departments->firstWhere('slug', $selectedDepartmentSlug)
            : null;

        if ($selectedDepartmentSlug !== '' && ! $selectedDepartment) {
            $selectedDepartmentSlug = '';
        }

        $resolveDepartment = function (?string $departmentName) use ($departments) {
            $normalizedDepartmentName = mb_strtolower(trim((string) $departmentName));

            if ($normalizedDepartmentName === '') {
                return null;
            }

            foreach ($departments as $department) {
                $name = mb_strtolower(trim((string) $department->name));
                $code = mb_strtolower(trim((string) $department->code));

                if ($name !== '' && (
                    $normalizedDepartmentName === $name
                    || str_contains($normalizedDepartmentName, $name)
                    || str_contains($name, $normalizedDepartmentName)
                )) {
                    return $department;
                }

                if ($code !== '' && (
                    $normalizedDepartmentName === $code
                    || str_contains($normalizedDepartmentName, $code)
                    || str_contains($code, $normalizedDepartmentName)
                )) {
                    return $department;
                }
            }

            return null;
        };

        $isLabTech = fn ($member): bool => str_contains(
            mb_strtolower(trim((string) (($member->designation ?? '') . ' ' . ($member->department ?? '')))),
            'lab'
        );

        $avatarUrl = function (string $name, string $background = '8B0000'): string {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=' . $background . '&color=fff';
        };

        $buildHodCard = function ($hod, $department) use ($avatarUrl) {
            $name = trim((string) ($hod->name ?? $department->name));

            return (object) [
                'id' => $department->id,
                'profile_type' => 'hod',
                'profile_url' => route('public.people.profile', ['type' => 'hod', 'id' => $department->id]),
                'name' => $name !== '' ? $name : $department->name,
                'designation' => 'Head of Department',
                'department' => $department->name,
                'department_slug' => $department->slug,
                'photo_url' => $hod->avatar_url ?? $avatarUrl($name !== '' ? $name : $department->name),
            ];
        };

        $buildTeacherCard = function ($teacher) use ($avatarUrl) {
            $name = trim((string) ($teacher->user?->name ?: $teacher->full_name ?: 'Teacher'));

            return (object) [
                'id' => $teacher->id,
                'profile_type' => 'teacher',
                'profile_url' => route('public.people.profile', ['type' => 'teacher', 'id' => $teacher->id]),
                'name' => $name,
                'designation' => $teacher->designation ?: 'Teacher',
                'department' => $teacher->department?->name,
                'department_slug' => $teacher->department?->slug,
                'photo_url' => $teacher->user?->avatar_url ?? $avatarUrl($name),
            ];
        };

        $buildStaffCard = function ($member, string $fallbackDesignation = 'Staff', ?object $department = null) use ($avatarUrl) {
            $name = trim((string) ($member->name ?? $fallbackDesignation));
            $resolvedDepartmentName = $department?->name ?: ($member->department ?: null);

            return (object) [
                'id' => $member->id,
                'profile_type' => 'staff',
                'profile_url' => route('public.people.profile', ['type' => 'staff', 'id' => $member->id]),
                'name' => $name !== '' ? $name : $fallbackDesignation,
                'designation' => $member->designation ?: $fallbackDesignation,
                'department' => $resolvedDepartmentName,
                'department_slug' => $department?->slug,
                'photo_url' => $member->photo_url ?? $avatarUrl($name !== '' ? $name : $fallbackDesignation),
            ];
        };

        $departmentSections = $departments->map(function ($department) use ($departmentHods, $teachers, $staffMembers, $resolveDepartment, $isLabTech, $buildHodCard, $buildTeacherCard, $buildStaffCard) {
            $hodDepartment = $departmentHods->get($department->id);
            $hodCard = $hodDepartment?->hod ? $buildHodCard($hodDepartment->hod, $department) : null;

            $teacherCards = $teachers
                ->filter(fn ($teacher) => (int) $teacher->department_id === (int) $department->id)
                ->map($buildTeacherCard)
                ->values();

            $assignedStaff = $staffMembers
                ->filter(function ($member) use ($resolveDepartment, $department) {
                    $resolvedDepartment = $resolveDepartment($member->department);

                    return $resolvedDepartment && (int) $resolvedDepartment->id === (int) $department->id;
                })
                ->values();

            $labTechCards = $assignedStaff
                ->filter($isLabTech)
                ->map(fn ($member) => $buildStaffCard($member, 'Lab Tech', $department))
                ->values();

            $staffCards = $assignedStaff
                ->reject($isLabTech)
                ->map(fn ($member) => $buildStaffCard($member, 'Staff', $department))
                ->values();

            $count = ($hodCard ? 1 : 0) + $teacherCards->count() + $labTechCards->count() + $staffCards->count();

            return (object) [
                'department' => $department,
                'hod' => $hodCard,
                'teachers' => $teacherCards,
                'staff' => $staffCards,
                'labtechs' => $labTechCards,
                'count' => $count,
            ];
        })->values();

        $otherMembers = $staffMembers
            ->filter(fn ($member) => $resolveDepartment($member->department) === null)
            ->values();

        $otherLabTechs = $otherMembers
            ->filter($isLabTech)
            ->map(fn ($member) => $buildStaffCard($member, 'Lab Tech'))
            ->values();

        $otherStaff = $otherMembers
            ->reject($isLabTech)
            ->map(fn ($member) => $buildStaffCard($member, 'Staff'))
            ->values();

        $otherCount = $otherLabTechs->count() + $otherStaff->count();

        $visibleDepartmentSections = $selectedDepartment
            ? $departmentSections->filter(fn ($section) => (int) $section->department->id === (int) $selectedDepartment->id)->values()
            : $departmentSections->filter(fn ($section) => $section->count > 0)->values();

        $activeDepartmentLabel = $selectedDepartment?->name ?? 'All Departments';
        $totalCount = $departmentSections->sum('count') + $otherCount;

        return view('public.people', compact(
            'departments',
            'selectedDepartmentSlug',
            'selectedDepartment',
            'activeDepartmentLabel',
            'departmentSections',
            'visibleDepartmentSections',
            'otherLabTechs',
            'otherStaff',
            'otherCount',
            'totalCount'
        ));
    }

    public function staff(Request $request)
    {
        $directory = $this->service->getPublicStaffDirectory(
            search: $request->string('search')->toString() ?: null,
            department: $request->string('department')->toString() ?: null,
            designation: $request->string('designation')->toString() ?: null,
            employmentStatus: $request->string('employment_status')->toString() ?: null,
            joinedYear: $request->string('joined_year')->toString() ?: null,
            featured: $request->string('featured')->toString() ?: null,
            perPage: 12,
        );

        return view('public.staff', $directory);
    }

    public function staffProfile(int $id)
    {
        $staff = $this->service->getPublicStaffProfile($id);

        return view('public.staff-profile', compact('staff'));
    }

    public function peopleProfile(string $type, int $id)
    {
        $normalizedType = strtolower($type);

        if (! in_array($normalizedType, ['hod', 'teacher', 'staff'], true)) {
            abort(404);
        }

        $profile = $this->service->getPeopleProfile($normalizedType, $id);
        $departments = $this->service->getDepartments();

        return view('public.people-profile', compact('profile', 'departments'));
    }

    public function contact()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.contact', compact('siteSettings'));
    }

    public function alumniDirectory()
    {
        $alumni = $this->service->getAlumniDirectory(
            request('department') ? (int) request('department') : null,
            request('search'),
            request('year'),
        );
        $departments = $this->service->getDepartments();
        $graduationYears = $this->service->getAlumniGraduationYears();
        return view('public.alumni', compact('alumni', 'departments', 'graduationYears'));
    }

    public function alumniProfile(int $id)
    {
        $alumnus = $this->service->getAlumniProfile($id);
        return view('public.alumni-profile', compact('alumnus'));
    }

    public function about()
    {
        $siteSettings = $this->service->getSiteSettings()->keyBy('key');
        return view('public.about', compact('siteSettings'));
    }

    // Application feature removed
}
