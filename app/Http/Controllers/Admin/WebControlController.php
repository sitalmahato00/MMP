<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\SiteSetting;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class WebControlController extends Controller
{
    public function index()
    {
        SiteSetting::ensureDefaults();

        $settings = SiteSetting::all()->groupBy('group');
        $facilities = Facility::with(['department', 'program'])->latest()->get();
        $executives = \App\Models\Executive::orderBy('order')->get();
        return view('admin.web-control.index', compact('settings', 'facilities', 'executives'));
    }

    public function update(Request $request)
    {
        SiteSetting::ensureDefaults();

        $settings = SiteSetting::query()->get(['key', 'type'])->keyBy('key');
        $allowedKeys = $settings->keys()->all();
        $imageKeys = $settings->filter(fn ($setting) => $setting->type === 'image')->keys()->all();
        $imageRules = collect($imageKeys)->mapWithKeys(fn ($key) => [$key => ['nullable', 'image', 'max:4096']])->all();
        if ($imageRules !== []) {
            $request->validate($imageRules);
        }

        $settings_data = Arr::except($request->all(), array_merge(['_token', '_method'], $imageKeys));
        
        foreach ($settings_data as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        foreach ($imageKeys as $imageKey) {
            if (!$request->hasFile($imageKey)) {
                continue;
            }

            $file = $request->file($imageKey);
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store('site-settings', 'public');
            SiteSetting::where('key', $imageKey)->update(['value' => $path]);
        }

        PublicDataService::invalidate('*');
        Cache::forget('brand:site_logo');

        return back()->with('success', 'Web content updated successfully.');
    }
}
