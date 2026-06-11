<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\SiteSetting;

class SeoHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        $siteSettings = Cache::remember('public:site_settings', 600, function () {
            SiteSetting::ensureDefaults();
            return SiteSetting::all()->pluck('value', 'key')->toArray();
        });

        $defaults = [
            'site_name' => $siteSettings['college_name'] ?? 'Manmohan Memorial Polytechnic',
            'description' => $siteSettings['meta_description'] ?? "Manmohan Memorial Polytechnic (MMP) — Best Technical College in Koshi Province, Nepal.",
            'keywords' => $siteSettings['meta_keywords'] ?? 'Manmohan Memorial Polytechnic, MMP, technical college Nepal, diploma engineering, CTEVT, Koshi Province, Morang',
            'author' => $siteSettings['meta_author'] ?? 'Manmohan Memorial Polytechnic',
            'image' => $this->resolveImage($siteSettings['site_logo'] ?? ''),
            'url' => url()->current(),
            'locale' => app()->getLocale(),
            'phone' => $siteSettings['contact_phone'] ?? '+977-21-590696',
            'address' => $siteSettings['contact_address'] ?? 'Budhiganga-4, Morang, Nepal',
        ];

        $meta = [
            'title' => $defaults['site_name'],
            'description' => $defaults['description'],
            'keywords' => $defaults['keywords'],
            'robots' => 'index, follow',
            'author' => $defaults['author'],
            'canonical' => $defaults['url'],
            'og:title' => $defaults['site_name'],
            'og:description' => $defaults['description'],
            'og:image' => $defaults['image'],
            'og:url' => $defaults['url'],
            'og:type' => 'website',
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $defaults['site_name'],
            'twitter:description' => $defaults['description'],
            'twitter:image' => $defaults['image'],
        ];

        if ($routeName === 'home') {
            $meta['title'] = $defaults['site_name'] . ' | Official Polytechnic College in Nepal';
            $meta['description'] = 'Manmohan Memorial Polytechnic (MMP) is the leading technical college in Morang, Nepal offering CTEVT diploma programs in engineering and technology.';
        }

        if ($response->headers->has('X-Robots-Tag') === false) {
            $response->headers->set('X-Robots-Tag', $meta['robots']);
        }

        foreach ($meta as $name => $content) {
            if (! $content) {
                continue;
            }

            if (in_array($name, ['og:title', 'og:description', 'og:image', 'og:url', 'og:type'], true)) {
                $response->headers->set("og:$name", $content);
                continue;
            }

            if (str_starts_with($name, 'twitter:')) {
                $response->headers->set($name, $content);
                continue;
            }

            switch ($name) {
                case 'canonical':
                    $response->headers->set('Link', '<' . $content . '>; rel="canonical"');
                    break;
                default:
                    $response->headers->set('X-Meta-' . ucfirst(str_replace(['_', ':'], ['', '-'], $name)), $content);
            }
        }

        return $response;
    }

    private function resolveImage(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if ($path !== '' && asset('storage/' . ltrim($path, '/')) !== null) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset('images/seo-default.jpg');
    }
}
