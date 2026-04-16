<?php

namespace App\Services;

use App\Models\{Department, Notice, Banner, Alumni, Download, Page, Program, Staff, Student, SiteSetting, Facility, Executive, Media, Teacher};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        return Cache::remember('public:ctevt_result_form', self::CACHE_TTL, function () {
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

        return Cache::remember($feedKey, self::CACHE_TTL, function () use ($isResult, $limit) {
            $feedUrl = config('services.ctevt_notice.feed_url', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices');
            $pageUrl = $isResult
                ? config('services.ctevt_notice.result_url', 'https://itms.ctevt.org.np:5580/notices/result')
                : config('services.ctevt_notice.general_url', 'https://itms.ctevt.org.np:5580/notices');

            $response = Http::timeout(20)
                ->retry(2, 250)
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
                'public:teachers',
                'public:department_hods',
                'public:leadership',
                'public:site_settings',
                'public:gallery',
                'public:question_bank',
                'public:news_events:5',
                'public:homepage_stats',
                'public:navigation_courses',
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
                    'options' => [
                        ['label' => '-- Select --', 'value' => ''],
                        ['label' => '2082', 'value' => '2082'],
                        ['label' => '2081', 'value' => '2081'],
                        ['label' => '2080', 'value' => '2080'],
                        ['label' => '2079', 'value' => '2079'],
                        ['label' => '2078', 'value' => '2078'],
                        ['label' => '2077', 'value' => '2077'],
                    ],
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
