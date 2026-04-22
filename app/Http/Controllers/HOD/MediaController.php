<?php

namespace App\Http\Controllers\HOD;

use App\Models\Media;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * HOD media management (department-scoped).
 * 
 * HODs can manage media files for their department only.
 */
class MediaController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get media files for department
        $query = Media::where('department_id', $deptId)
            ->with(['uploader:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('file_name', 'like', "%{$term}%");
            })
            ->when($request->date_from, function ($q) use ($request) {
                $from = adDate($request->date_from);
                if ($from) {
                    $q->whereDate('created_at', '>=', $from->toDateString());
                }
            })
            ->when($request->date_to, function ($q) use ($request) {
                $to = adDate($request->date_to);
                if ($to) {
                    $q->whereDate('created_at', '<=', $to->toDateString());
                }
            })
            ->when($request->type, function ($q) use ($request) {
                if ($request->type === 'image') {
                    $q->where('mime_type', 'like', 'image/%');
                } elseif ($request->type === 'document') {
                    $q->whereNotIn('mime_type', ['image/%'])
                      ->where('mime_type', 'like', 'application/%');
                } elseif ($request->type === 'video') {
                    $q->where('mime_type', 'like', 'video/%');
                }
            });

        $mediaFiles = (clone $query)
            ->latest('created_at')
            ->paginate(24)
            ->withQueryString();

        // Stats
        $totalFiles = (clone $query)->count();
        $totalSize = (clone $query)->sum('size');
        $imageFiles = (clone $query)->where('mime_type', 'like', 'image/%')->count();
        $documentFiles = (clone $query)->where('mime_type', 'like', 'application/%')->count();

        return view('hod.media.index', compact(
            'mediaFiles', 'department',
            'totalFiles', 'totalSize', 'imageFiles', 'documentFiles'
        ));
    }

    // ── Upload ─────────────────────────────────────────────────────────────
    public function upload(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:10240', // 10MB per file
            'title' => 'nullable|string|max:255',
        ]);

        $uploadedFiles = [];

        foreach ($data['files'] as $index => $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('media/' . date('Y/m'), 'public');
            
            $media = Media::create([
                'title' => $data['title'] ?? pathinfo($fileName, PATHINFO_FILENAME),
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'department_id' => $deptId,
                'uploaded_by' => auth()->id(),
            ]);

            $uploadedFiles[] = $media;
        }

        PublicDataService::invalidate('gallery');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully.',
                'files' => $uploadedFiles
            ]);
        }

        return redirect()
            ->route('hod.media.index')
            ->with('success', count($uploadedFiles) . ' file(s) uploaded successfully.');
    }

    // ── Gallery ────────────────────────────────────────────────────────────
    public function gallery(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get only image files for gallery view
        $images = Media::where('department_id', $deptId)
            ->where('mime_type', 'like', 'image/%')
            ->with(['uploader:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('file_name', 'like', "%{$term}%");
            })
            ->when($request->date_from, function ($q) use ($request) {
                $from = adDate($request->date_from);
                if ($from) {
                    $q->whereDate('created_at', '>=', $from->toDateString());
                }
            })
            ->when($request->date_to, function ($q) use ($request) {
                $to = adDate($request->date_to);
                if ($to) {
                    $q->whereDate('created_at', '<=', $to->toDateString());
                }
            })
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('hod.media.gallery', compact('images', 'department'));
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, Media $media)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify media belongs to department
        if ($media->department_id !== $deptId) {
            abort(403, 'Unauthorized access to media file.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();
        PublicDataService::invalidate('gallery');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Media file deleted successfully.'
            ]);
        }

        return redirect()
            ->route('hod.media.index')
            ->with('success', 'Media file deleted successfully.');
    }
}