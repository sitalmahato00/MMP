<?php

namespace App\Modules\Settings\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\Page;
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function clearCtevtCache()
    {
        $cacheKeys = [
            'public:ctevt_notices:general:5',
            'public:ctevt_notices:result:5',
            'public:ctevt_notices:general:6',
            'public:ctevt_notices:result:6',
            'public:ctevt_notices:general:10',
            'public:ctevt_notices:result:10',
            'public:ctevt_result_form',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        return back()->with('success', 'CTEVT notices cache cleared successfully! Fresh notices will be fetched on next page load.');
    }

    public function clearAllCache()
    {
        PublicDataService::invalidate('*');
        
        return back()->with('success', 'All public caches cleared successfully!');
    }
}
