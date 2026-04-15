<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\SiteSetting;
use App\Services\PublicDataService;
use Illuminate\Http\Request;

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

        $settings_data = $request->except(['_token', '_method']);
        $allowedKeys = SiteSetting::query()->pluck('key')->all();
        
        foreach ($settings_data as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }

            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        PublicDataService::invalidate('*');

        return back()->with('success', 'Web content updated successfully.');
    }
}
