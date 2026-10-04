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
        $banners = Banner::orderBy('order')->latest()->paginate(12)->withQueryString();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        // Prevent duplicate concurrent / rapid double submissions within 10 seconds
        $lockKey = 'banner_create_' . (auth()->id() ?? 'guest') . '_' . md5(($request->title ?? '') . '_' . ($request->order ?? 0));
        if (!cache()->add($lockKey, true, 10)) {
            return redirect()->route('admin.banners.index')->with('success', 'Banner added.');
        }

        $serverUploadMax = ini_get('upload_max_filesize') ?: '2M';
        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            $errCode = $request->file('image')->getError();
            if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
                return back()->withInput()->withErrors([
                    'image' => "The uploaded banner file exceeds the server upload limit ({$serverUploadMax}). Please increase it in cPanel -> Select PHP Version -> Options.",
                ]);
            }
        }

        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,m4v,avi|max:102400', // 100MB max
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|url|max:255',
        ], [
            'image.uploaded' => "The banner file failed to upload. It may exceed your server's upload_max_filesize ({$serverUploadMax}).",
            'image.max'      => 'The banner file may not be greater than 100MB.',
            'image.mimes'    => 'The banner must be an image (JPG, PNG, WebP) or video (MP4, WebM, MOV, M4V, AVI).',
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
        $serverUploadMax = ini_get('upload_max_filesize') ?: '2M';
        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            $errCode = $request->file('image')->getError();
            if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
                return back()->withInput()->withErrors([
                    'image' => "The uploaded banner file exceeds the server upload limit ({$serverUploadMax}). Please increase it in cPanel -> Select PHP Version -> Options.",
                ]);
            }
        }

        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov,m4v,avi|max:102400',
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
            'button_text' => 'nullable|string|max:50',
            'button_link' => 'nullable|url|max:255',
        ], [
            'image.uploaded' => "The banner file failed to upload. It may exceed your server's upload_max_filesize ({$serverUploadMax}).",
            'image.max'      => 'The banner file may not be greater than 100MB.',
            'image.mimes'    => 'The banner must be an image (JPG, PNG, WebP) or video (MP4, WebM, MOV, M4V, AVI).',
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
