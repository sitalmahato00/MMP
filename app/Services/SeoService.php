<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\SiteSetting;

class SeoService
{
    // ─── Defaults pulled once per request ──────────────────────────────────

    private static function siteSettings(): array
    {
        return Cache::remember('public:site_settings', 600, function () {
            SiteSetting::ensureDefaults();
            return SiteSetting::all()->pluck('value', 'key')->toArray();
        });
    }

    private static function baseUrl(): string
    {
        // Always derive from config which reads APP_URL — never hardcode a domain here
        return rtrim(config('seo.url', config('app.url')), '/');
    }

    // ─── Core tag builder ──────────────────────────────────────────────────

    /**
     * Build a complete SEO meta array for a page.
     *
     * @param array $overrides  Keys: title, description, keywords, robots, canonical,
     *                                og_type, og_image, twitter_card, noindex,
     *                                breadcrumbs (array), faqs (array), extra_json_ld (array)
     */
    public static function build(array $overrides = []): array
    {
        $settings = self::siteSettings();
        $baseUrl  = self::baseUrl();

        $siteName    = $settings['college_name']    ?? config('seo.site_name');
        $defaultDesc = $settings['meta_description'] ?? config('seo.default_description');
        $canonical   = $overrides['canonical']       ?? url()->current();

        // Ensure canonical always uses the production domain
        $canonical = self::normaliseCanonical($canonical, $baseUrl);

        $rawTitle   = $overrides['title']       ?? $siteName;
        $suffix     = config('seo.title_suffix', ' — Manmohan Memorial Polytechnic');

        // Always append the institution name unless the title already contains it
        $fullTitle  = str_contains($rawTitle, 'Manmohan Memorial Polytechnic')
            ? $rawTitle
            : $rawTitle . $suffix;

        $description = $overrides['description'] ?? $defaultDesc;
        $keywords    = $overrides['keywords']    ?? ($settings['meta_keywords'] ?? config('seo.default_keywords'));
        $robots      = $overrides['noindex'] ?? false
            ? 'noindex, nofollow'
            : ($overrides['robots'] ?? config('seo.default_robots'));

        $ogType  = $overrides['og_type']    ?? 'website';
        $ogImage = $overrides['og_image']   ?? self::defaultOgImage($settings);
        $ogImage = self::absoluteUrl($ogImage, $baseUrl);

        $twitterCard = $overrides['twitter_card'] ?? 'summary_large_image';

        // og:title and twitter:title — always the full institution-branded title
        $brandedOgTitle      = $overrides['og_title']  ?? $fullTitle;
        $brandedTwitterTitle = $overrides['og_title']  ?? $fullTitle;

        return [
            'title'           => $fullTitle,
            'raw_title'       => $rawTitle,
            'description'     => $description,
            'keywords'        => $keywords,
            'robots'          => $robots,
            'canonical'       => $canonical,
            'og_title'        => $brandedOgTitle,
            'og_description'  => $overrides['og_description'] ?? $description,
            'og_type'         => $ogType,
            'og_image'        => $ogImage,
            'og_image_width'  => $overrides['og_image_width']  ?? config('seo.default_og_image_width',  1200),
            'og_image_height' => $overrides['og_image_height'] ?? config('seo.default_og_image_height', 630),
            'og_url'          => $canonical,
            'og_site_name'    => 'Manmohan Memorial Polytechnic',
            'og_locale'       => config('seo.locale', 'en_US'),
            'twitter_card'    => $twitterCard,
            'twitter_site'    => config('seo.twitter_site',    ''),
            'twitter_creator' => config('seo.twitter_creator', ''),
            'twitter_title'   => $brandedTwitterTitle,
            'twitter_description' => $overrides['og_description'] ?? $description,
            'twitter_image'   => $ogImage,

            // Structured data
            'breadcrumbs'     => $overrides['breadcrumbs'] ?? [],
            'faqs'            => $overrides['faqs']        ?? [],
            'extra_json_ld'   => $overrides['extra_json_ld'] ?? [],

            // Verification
            'google_verification' => config('seo.google_verification', ''),
            'bing_verification'   => config('seo.bing_verification',   ''),

            // Schema config
            'org'  => config('seo.organization'),
            'base' => $baseUrl,
        ];
    }

    // ─── Per-page preset builders ──────────────────────────────────────────

    public static function home(): array
    {
        return self::build([
            'title'       => 'Manmohan Memorial Polytechnic | Technical Education in Nepal',
            'description' => 'Manmohan Memorial Polytechnic (MMP), Budhiganga-4 Morang — the leading CTEVT-affiliated technical college in Koshi Province. Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.',
            'og_type'     => 'website',
            'breadcrumbs' => [],
        ]);
    }

    public static function departments(): array
    {
        return self::build([
            'title'       => 'Departments & Programs — Manmohan Memorial Polytechnic',
            'description' => 'Explore all diploma programs at Manmohan Memorial Polytechnic — Civil, Electrical, Mechanical, Electronics & IT Engineering. CTEVT affiliated, Morang Nepal.',
            'breadcrumbs' => [
                ['name' => 'Home',        'url' => url('/')],
                ['name' => 'Departments', 'url' => url('/departments')],
            ],
        ]);
    }

    public static function department(object $dept): array
    {
        return self::build([
            'title'       => $dept->name . ' Department — Manmohan Memorial Polytechnic',
            'description' => $dept->description ?? 'Learn about the ' . $dept->name . ' department at Manmohan Memorial Polytechnic, Morang Nepal.',
            'canonical'   => url('/departments/' . $dept->slug),
            'breadcrumbs' => [
                ['name' => 'Home',        'url' => url('/')],
                ['name' => 'Departments', 'url' => url('/departments')],
                ['name' => $dept->name,   'url' => url('/departments/' . $dept->slug)],
            ],
        ]);
    }

    public static function contact(): array
    {
        return self::build([
            'title'       => 'Contact Us — Manmohan Memorial Polytechnic',
            'description' => 'Contact Manmohan Memorial Polytechnic. Address: Budhiganga-4, Morang, Nepal. Phone: +977-21-590696. Email: info@mmp.edu.np.',
            'breadcrumbs' => [
                ['name' => 'Home',       'url' => url('/')],
                ['name' => 'Contact Us', 'url' => url('/contact')],
            ],
        ]);
    }

    public static function notices(): array
    {
        return self::build([
            'title'       => 'Notice Board — Manmohan Memorial Polytechnic',
            'description' => 'Official notices, circulars and announcements from Manmohan Memorial Polytechnic, Morang Nepal.',
            'breadcrumbs' => [
                ['name' => 'Home',    'url' => url('/')],
                ['name' => 'Notices', 'url' => url('/notices')],
            ],
        ]);
    }

    public static function notice(object $notice): array
    {
        return self::build([
            'title'       => $notice->title . ' — Manmohan Memorial Polytechnic',
            'description' => $notice->excerpt ?? strip_tags((string) $notice->content ?? ''),
            'canonical'   => url('/notices/' . $notice->slug),
            'og_type'     => 'article',
            'breadcrumbs' => [
                ['name' => 'Home',    'url' => url('/')],
                ['name' => 'Notices', 'url' => url('/notices')],
                ['name' => $notice->title, 'url' => url('/notices/' . $notice->slug)],
            ],
        ]);
    }

    public static function newsEvents(): array
    {
        return self::build([
            'title'       => 'News & Events — Manmohan Memorial Polytechnic',
            'description' => 'Latest news and events from Manmohan Memorial Polytechnic, Morang Nepal.',
            'breadcrumbs' => [
                ['name' => 'Home',         'url' => url('/')],
                ['name' => 'News & Events', 'url' => url('/news-events')],
            ],
        ]);
    }

    public static function newsEvent(object $item): array
    {
        return self::build([
            'title'       => $item->title . ' — Manmohan Memorial Polytechnic',
            'description' => $item->excerpt ?? strip_tags((string) $item->content ?? ''),
            'canonical'   => url('/news-events/' . $item->slug),
            'og_type'     => 'article',
            'breadcrumbs' => [
                ['name' => 'Home',         'url' => url('/')],
                ['name' => 'News & Events', 'url' => url('/news-events')],
                ['name' => $item->title,   'url' => url('/news-events/' . $item->slug)],
            ],
        ]);
    }

    public static function result(): array
    {
        return self::build([
            'title'       => 'Entrance / CTEVT Result — Manmohan Memorial Polytechnic',
            'description' => 'Check CTEVT exam results, entrance results and academic results for Manmohan Memorial Polytechnic students.',
            'breadcrumbs' => [
                ['name' => 'Home',           'url' => url('/')],
                ['name' => 'Entrance Result', 'url' => url('/result')],
            ],
        ]);
    }

    public static function leadership(): array
    {
        return self::build([
            'title'       => 'Presidents & Principal Message — Manmohan Memorial Polytechnic',
            'description' => 'Meet the leadership team of Manmohan Memorial Polytechnic — Principal, Presidents and senior executives.',
            'breadcrumbs' => [
                ['name' => 'Home',       'url' => url('/')],
                ['name' => 'Leadership', 'url' => url('/leadership')],
            ],
        ]);
    }

    public static function people(): array
    {
        return self::build([
            'title'       => 'Faculty & Staff — Manmohan Memorial Polytechnic',
            'description' => 'Meet the faculty, department heads, teachers and administrative staff of Manmohan Memorial Polytechnic, Morang Nepal.',
            'breadcrumbs' => [
                ['name' => 'Home',   'url' => url('/')],
                ['name' => 'People', 'url' => url('/people')],
            ],
        ]);
    }

    public static function staff(): array
    {
        return self::build([
            'title'       => 'Administrative Staff — Manmohan Memorial Polytechnic',
            'description' => 'Administrative and support staff directory of Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',  'url' => url('/')],
                ['name' => 'Staff', 'url' => url('/staff')],
            ],
        ]);
    }

    public static function gallery(): array
    {
        return self::build([
            'title'       => 'Photo Gallery — Manmohan Memorial Polytechnic',
            'description' => 'Photo gallery of Manmohan Memorial Polytechnic — campus life, events, labs and activities.',
            'breadcrumbs' => [
                ['name' => 'Home',    'url' => url('/')],
                ['name' => 'Gallery', 'url' => url('/gallery')],
            ],
        ]);
    }

    public static function downloads(): array
    {
        return self::build([
            'title'       => 'Downloads & Resources — Manmohan Memorial Polytechnic',
            'description' => 'Download official forms, syllabus, notes and other documents from Manmohan Memorial Polytechnic.',
            'breadcrumbs' => [
                ['name' => 'Home',      'url' => url('/')],
                ['name' => 'Downloads', 'url' => url('/downloads')],
            ],
        ]);
    }

    public static function alumni(): array
    {
        return self::build([
            'title'       => 'Alumni Directory — Manmohan Memorial Polytechnic',
            'description' => 'Connect with MMP alumni. Find graduates of Manmohan Memorial Polytechnic across various industries.',
            'breadcrumbs' => [
                ['name' => 'Home',   'url' => url('/')],
                ['name' => 'Alumni', 'url' => url('/alumni')],
            ],
        ]);
    }

    public static function facilities(): array
    {
        return self::build([
            'title'       => 'Campus Facilities & Resources — Manmohan Memorial Polytechnic',
            'description' => 'Explore the campus facilities at Manmohan Memorial Polytechnic — labs, library, workshops and more.',
            'breadcrumbs' => [
                ['name' => 'Home',       'url' => url('/')],
                ['name' => 'Facilities', 'url' => url('/facilities')],
            ],
        ]);
    }

    public static function page(object $page): array
    {
        $pageTitle = $page->title ?? 'Page';
        return self::build([
            'title'       => $pageTitle . ' — Manmohan Memorial Polytechnic',
            'description' => $page->meta_description ?? strip_tags((string) ($page->content ?? '')),
            'canonical'   => url('/page/' . $page->slug),
            'breadcrumbs' => [
                ['name' => 'Home',     'url' => url('/')],
                ['name' => $pageTitle, 'url' => url('/page/' . $page->slug)],
            ],
        ]);
    }

    // ─── Structured Data Generators ────────────────────────────────────────

    /**
     * Generate all JSON-LD scripts for a page as a single string.
     */
    public static function jsonLd(array $seo): string
    {
        $scripts = [];
        $org     = $seo['org']  ?? config('seo.organization', []);
        $base    = $seo['base'] ?? config('app.url');

        // 1. Organization + CollegeOrUniversity schema (on every page)
        $scripts[] = self::organizationSchema($org, $base);

        // 2. Website + SearchAction schema (on every page)
        $scripts[] = self::websiteSchema($org, $base);

        // 3. Local Business schema (on every page)
        $scripts[] = self::localBusinessSchema($org, $base);

        // 4. Breadcrumb schema (when breadcrumbs are provided)
        if (!empty($seo['breadcrumbs'])) {
            $scripts[] = self::breadcrumbSchema($seo['breadcrumbs']);
        }

        // 5. FAQ schema (when FAQs are provided)
        if (!empty($seo['faqs'])) {
            $scripts[] = self::faqSchema($seo['faqs']);
        }

        // 6. Extra custom schemas
        foreach ($seo['extra_json_ld'] ?? [] as $schema) {
            $scripts[] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $output = '';
        foreach ($scripts as $json) {
            $output .= '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>' . "\n";
        }
        return $output;
    }

    // ─── Schema helpers ────────────────────────────────────────────────────

    private static function organizationSchema(array $org, string $base): string
    {
        $phones = (array) ($org['telephone'] ?? []);
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => ['CollegeOrUniversity', 'EducationalOrganization'],
            '@id'         => $base . '/#organization',
            'name'        => $org['name'] ?? config('seo.site_name'),
            'alternateName' => $org['alternate_name'] ?? config('seo.short_name'),
            'url'         => $org['url'] ?? $base,
            'logo' => [
                '@type'  => 'ImageObject',
                'url'    => $org['logo'] ?? ($base . '/brand-logo'),
                'width'  => 200,
                'height' => 200,
            ],
            'foundingDate' => $org['founded'] ?? '2008',
            'telephone'    => count($phones) === 1 ? $phones[0] : $phones,
            'email'        => $org['email'] ?? config('seo.organization.email', ''),
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $org['address']['street']   ?? 'Budhiganga-4',
                'addressLocality' => $org['address']['locality']  ?? 'Morang',
                'addressRegion'   => $org['address']['region']   ?? 'Koshi Province',
                'postalCode'      => $org['address']['postal']   ?? '',
                'addressCountry'  => $org['address']['country']  ?? 'NP',
            ],
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $org['geo']['latitude']  ?? '26.6353',
                'longitude' => $org['geo']['longitude'] ?? '87.2823',
            ],
            'contactPoint' => [
                '@type'             => 'ContactPoint',
                'telephone'         => $phones[0] ?? '',
                'contactType'       => 'admissions',
                'areaServed'        => 'NP',
                'availableLanguage' => ['English', 'Nepali'],
            ],
        ];

        if (!empty($org['same_as'])) {
            $schema['sameAs'] = $org['same_as'];
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private static function websiteSchema(array $org, string $base): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            '@id'      => $base . '/#website',
            'url'      => $base,
            'name'     => $org['name'] ?? config('seo.site_name'),
            'alternateName' => $org['alternate_name'] ?? config('seo.short_name'),
            'publisher' => [
                '@id' => $base . '/#organization',
            ],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target' => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => $base . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
            'inLanguage' => 'en',
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private static function localBusinessSchema(array $org, string $base): string
    {
        $phones = (array) ($org['telephone'] ?? []);
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            '@id'      => $base . '/#localbusiness',
            'name'     => $org['name'] ?? config('seo.site_name'),
            'image'    => $org['logo'] ?? ($base . '/brand-logo'),
            'url'      => $org['url'] ?? $base,
            'telephone' => $phones[0] ?? '',
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $org['address']['street']   ?? 'Budhiganga-4',
                'addressLocality' => $org['address']['locality']  ?? 'Morang',
                'addressRegion'   => $org['address']['region']   ?? 'Koshi Province',
                'addressCountry'  => $org['address']['country']  ?? 'NP',
            ],
            'geo' => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $org['geo']['latitude']  ?? '26.6353',
                'longitude' => $org['geo']['longitude'] ?? '87.2823',
            ],
            'openingHoursSpecification' => [
                [
                    '@type'    => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'opens'    => '06:00',
                    'closes'   => '17:00',
                ],
            ],
            'priceRange' => '₨₨',
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private static function breadcrumbSchema(array $breadcrumbs): string
    {
        $items = [];
        foreach ($breadcrumbs as $i => $crumb) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $crumb['name'],
                'item'     => $crumb['url'],
            ];
        }

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private static function faqSchema(array $faqs): string
    {
        $mainEntity = [];
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ],
            ];
        }

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ─── Utility helpers ───────────────────────────────────────────────────

    private static function defaultOgImage(array $settings): string
    {
        $logo = $settings['site_logo'] ?? '';
        if ($logo !== '' && filter_var($logo, FILTER_VALIDATE_URL)) {
            return $logo;
        }
        if ($logo !== '') {
            return asset('storage/' . ltrim($logo, '/'));
        }
        return config('seo.default_og_image', '/images/seo-og-default.jpg');
    }

    private static function absoluteUrl(string $url, string $base): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return $base . '/' . ltrim($url, '/');
    }

    private static function normaliseCanonical(string $url, string $base): string
    {
        // Replace whatever APP_URL is with the real production URL
        $appUrl = rtrim(config('app.url', ''), '/');
        if ($appUrl && $appUrl !== $base) {
            $url = str_replace($appUrl, $base, $url);
        }
        return $url;
    }
}
