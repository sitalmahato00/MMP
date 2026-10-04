<?php

namespace App\Http\Controllers\Admin;

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

        // Detect any PHP-level upload errors (e.g. cPanel upload_max_filesize / post_max_size exceeded)
        $serverUploadMax = ini_get('upload_max_filesize') ?: '2M';
        $serverPostMax   = ini_get('post_max_size') ?: '8M';
        foreach ($uploadKeys as $uploadKey) {
            $rawFile = $request->file($uploadKey);
            if ($rawFile && !$rawFile->isValid()) {
                $errCode = $rawFile->getError();
                $fieldTitle = ucwords(str_replace('_', ' ', $uploadKey));

                if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
                    $msg = "The {$fieldTitle} exceeds the server's upload limit (upload_max_filesize: {$serverUploadMax}, post_max_size: {$serverPostMax}). In cPanel, go to 'Select PHP Version' -> 'Options' and set upload_max_filesize & post_max_size to 128M.";
                    return response()->json(['message' => $msg, 'errors' => [$uploadKey => [$msg]]], 422);
                }

                if ($errCode !== UPLOAD_ERR_NO_FILE) {
                    $msg = "The {$fieldTitle} failed to upload (PHP upload error code: {$errCode}). Check server temporary directory or upload permissions.";
                    return response()->json(['message' => $msg, 'errors' => [$uploadKey => [$msg]]], 422);
                }
            }
        }

        $imageRules = collect($imageKeys)->mapWithKeys(fn ($key) => [$key => ['nullable', 'image', 'max:8192']])->all();
        // 100 MB max for video/file uploads (expanded from 20 MB)
        $fileRules  = collect($fileKeys)->mapWithKeys(fn ($key) => [$key => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,m4v,avi,pdf', 'max:102400']])->all();
        $allRules   = array_merge($imageRules, $fileRules);
        if ($allRules !== []) {
            $request->validate($allRules, [
                '*.uploaded' => "The :attribute failed to upload. The file may exceed your server's upload_max_filesize ({$serverUploadMax}). Please increase it in cPanel -> Select PHP Version -> Options.",
                '*.max'      => 'The :attribute may not be greater than :max kilobytes.',
                '*.mimes'    => 'The :attribute must be a file of type: :values.',
            ]);
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
        $allowed = ['site_logo', 'principal_photo', 'principal_signature', 'principal_message_media'];
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
