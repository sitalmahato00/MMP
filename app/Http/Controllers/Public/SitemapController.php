<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notice;
use App\Models\Program;
use App\Models\Download;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Serve the sitemap index — lists all sub-sitemaps.
     */
    public function index(): Response
    {
        $lastMod = now()->toAtomString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach (['static', 'departments', 'notices', 'news', 'downloads'] as $section) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . e(url("/sitemap-{$section}.xml")) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type'  => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Static pages sitemap.
     */
    public function staticPages(): Response
    {
        $today = now()->toAtomString();

        $pages = [
            ['loc' => url('/'),             'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => url('/departments'),  'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/notices'),      'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => url('/news-events'),  'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => url('/result'),       'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/people'),       'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => url('/staff'),        'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => url('/leadership'),   'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => url('/facilities'),   'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/gallery'),      'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => url('/downloads'),    'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => url('/question-bank'),'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/alumni'),       'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => url('/contact'),      'priority' => '0.8', 'changefreq' => 'monthly'],

            // Important static content pages
            ['loc' => url('/page/what-is-mmp'),          'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => url('/page/objectives'),            'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/page/scholarship-schemes'),  'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/page/internships'),          'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        $xml = $this->openUrlset();
        foreach ($pages as $page) {
            $xml .= $this->urlEntry($page['loc'], $today, $page['changefreq'], $page['priority']);
        }
        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    /**
     * Departments + Programs sitemap.
     */
    public function departments(): Response
    {
        $departments = Cache::remember('sitemap:departments', 3600, function () {
            return Department::where('is_active', true)
                ->with(['programs' => fn($q) => $q->where('is_active', true)])
                ->get(['id', 'slug', 'name', 'updated_at']);
        });

        $xml = $this->openUrlset();

        foreach ($departments as $dept) {
            $xml .= $this->urlEntry(
                url("/departments/{$dept->slug}"),
                $dept->updated_at?->toAtomString() ?? now()->toAtomString(),
                'monthly',
                '0.8'
            );

            foreach ($dept->programs ?? [] as $program) {
                $xml .= $this->urlEntry(
                    url("/departments/{$dept->slug}/{$program->slug}"),
                    $program->updated_at?->toAtomString() ?? now()->toAtomString(),
                    'monthly',
                    '0.7'
                );
            }
        }

        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    /**
     * Notices sitemap.
     */
    public function notices(): Response
    {
        $notices = Cache::remember('sitemap:notices', 1800, function () {
            return Notice::published()
                ->whereNotIn('type', ['news', 'event'])
                ->orderByDesc('published_at')
                ->limit(1000)
                ->get(['slug', 'title', 'published_at', 'updated_at']);
        });

        $xml = $this->openUrlset();

        foreach ($notices as $notice) {
            $xml .= $this->urlEntry(
                url("/notices/{$notice->slug}"),
                $notice->updated_at?->toAtomString() ?? $notice->published_at?->toAtomString() ?? now()->toAtomString(),
                'weekly',
                '0.6'
            );
        }

        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    /**
     * News & Events sitemap.
     */
    public function news(): Response
    {
        $items = Cache::remember('sitemap:news', 1800, function () {
            return Notice::published()
                ->whereIn('type', ['news', 'event'])
                ->orderByDesc('published_at')
                ->limit(500)
                ->get(['slug', 'title', 'published_at', 'updated_at']);
        });

        $xml = $this->openUrlset();

        foreach ($items as $item) {
            $xml .= $this->urlEntry(
                url("/news-events/{$item->slug}"),
                $item->updated_at?->toAtomString() ?? $item->published_at?->toAtomString() ?? now()->toAtomString(),
                'weekly',
                '0.5'
            );
        }

        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    /**
     * Downloads sitemap.
     */
    public function downloads(): Response
    {
        $downloads = Cache::remember('sitemap:downloads', 3600, function () {
            return Download::where('is_public', true)
                ->orderByDesc('created_at')
                ->limit(500)
                ->get(['id', 'updated_at']);
        });

        $xml = $this->openUrlset();

        foreach ($downloads as $dl) {
            $xml .= $this->urlEntry(
                url("/downloads/{$dl->id}/file"),
                $dl->updated_at?->toAtomString() ?? now()->toAtomString(),
                'monthly',
                '0.4'
            );
        }

        $xml .= '</urlset>';

        return $this->xmlResponse($xml);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function openUrlset(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    }

    private function urlEntry(string $loc, string $lastmod, string $changefreq = 'weekly', string $priority = '0.5'): string
    {
        return "  <url>\n"
            . "    <loc>" . e($loc) . "</loc>\n"
            . "    <lastmod>{$lastmod}</lastmod>\n"
            . "    <changefreq>{$changefreq}</changefreq>\n"
            . "    <priority>{$priority}</priority>\n"
            . "  </url>\n";
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type'  => 'application/xml',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}
