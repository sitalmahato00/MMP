<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class WebControlController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->groupBy('group');
        return view('admin.web-control.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings_data = $request->except(['_token', '_method']);
        
        foreach ($settings_data as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Web content updated successfully.');
    }
}
