<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('order')->latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'required|image|max:5120', // 5MB max
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|url|max:255',
        ]);

        unset($data['image']);
        $data['image']     = $request->file('image')->store('banners', 'public');
        $data['is_active'] = $request->has('is_active');
        $data['order']     = $request->order ?? 0;

        Banner::create($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.banners.index')->with('success', 'Banner added.');
    }

    public function show(Banner $banner)
    {
        return view('admin.banners.show', compact('banner'));
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:5120',
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|url|max:255',
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        } else {
            unset($data['image']);
        }

        $data['is_active'] = $request->has('is_active');
        $data['order']     = $request->order ?? 0;

        $banner->update($data);
        PublicDataService::invalidate('*');

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }
}
