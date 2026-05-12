<?php

namespace App\Modules\CMS\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Download;
use App\Models\Executive;
use App\Models\Facility;
use App\Models\Media;
use App\Models\Notice;
use App\Models\SiteSetting;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WebControlController extends Controller
{
    public function index()
    {
        SiteSetting::ensureDefaults();

        $settings   = SiteSetting::all()->groupBy('group');
        $facilities = Facility::with(['department', 'program'])->latest()->paginate(8, ['*'], 'facilities_page')->withQueryString();
        $executives = Executive::orderBy('order')->paginate(8, ['*'], 'executives_page')->withQueryString();
        $banners    = Banner::orderBy('order')->get();
        $media      = Media::latest()->get();
        $downloads  = Download::latest()->paginate(10, ['*'], 'downloads_page')->withQueryString();
        $notices    = Notice::with('author')
            ->whereIn('type', ['news', 'event'])
            ->latest()
            ->paginate(10, ['*'], 'notices_page')
            ->withQueryString();

        return view('admin.web-control.index', compact(
            'settings', 'facilities', 'executives',
            'banners', 'media', 'downloads', 'notices'
        ));
    }

    public function update(Request $request)
    {
        SiteSetting::ensureDefaults();

        $settings = SiteSetting::query()->get(['key', 'type'])->keyBy('key');
        $allowedKeys = $settings->keys()->all();
        $imageKeys = $settings->filter(fn ($setting) => $setting->type === 'image')->keys()->all();
        $fileKeys  = $settings->filter(fn ($setting) => $setting->type === 'file')->keys()->all();
        $uploadKeys = array_merge($imageKeys, $fileKeys);

        $imageRules = collect($imageKeys)->mapWithKeys(fn ($key) => [$key => ['nullable', 'image', 'max:4096']])->all();
        $fileRules  = collect($fileKeys)->mapWithKeys(fn ($key) => [$key => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,pdf', 'max:102400']])->all();
        $allRules   = array_merge($imageRules, $fileRules);
        if ($allRules !== []) {
            $request->validate($allRules);
        }

        $settings_data = Arr::except($request->all(), array_merge(['_token', '_method'], $uploadKeys));
        
        foreach ($settings_data as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        foreach ($uploadKeys as $uploadKey) {
            if (!$request->hasFile($uploadKey)) {
                continue;
            }

            $file = $request->file($uploadKey);
            if (!$file || !$file->isValid()) {
                continue;
            }

            // Delete old file if exists
            $oldSetting = SiteSetting::where('key', $uploadKey)->first();
            if ($oldSetting?->value && Storage::disk('public')->exists($oldSetting->value)) {
                Storage::disk('public')->delete($oldSetting->value);
            }

            // Store new file
            $path = $file->store('site-settings', 'public');
            
            // Update database
            SiteSetting::where('key', $uploadKey)->update(['value' => $path]);
            
            // Clear all related caches immediately
            if ($uploadKey === 'site_logo') {
                Cache::forget('brand:site_logo');
                Cache::forget('brand:logo_version');
                // Also clear any Laravel cache tags if using Redis/Memcached
                if (method_exists(Cache::getStore(), 'tags')) {
                    Cache::tags(['site_settings', 'branding'])->flush();
                }
            }
        }

        // Final cache clear
        PublicDataService::invalidate('*');
        Cache::forget('brand:site_logo');
        Cache::forget('brand:logo_version');
        
        // Clear application cache to ensure fresh data
        \Artisan::call('cache:clear');

        return back()->with('success', 'Web content updated successfully.');
    }

    public function clearFile(string $key)
    {
        $allowed = ['site_logo', 'principal_photo', 'principal_message_media'];
        if (!in_array($key, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting?->value && Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
            SiteSetting::where('key', $key)->update(['value' => null]);

            PublicDataService::invalidate('*');
            Cache::forget('brand:site_logo');
            Cache::forget('brand:logo_version');

            return response()->json(['success' => true, 'message' => 'File removed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to remove the file: ' . $e->getMessage()], 500);
        }
    }
}
