<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Mark;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlumniController extends Controller
{
    /**
     * Alumni Dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'alumni_id' => $alumni->id,
                    'name' => $user->name,
                    'graduation_year' => $alumni->graduation_year,
                    'program' => $alumni->program?->name,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Marksheets
     */
    public function marksheets(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            // Get all marks for this alumni
            $marks = Mark::where('alumni_id', $alumni->id)
                ->with('exam', 'subject')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $marks->map(fn($m) => [
                    'id' => $m->id,
                    'exam' => $m->exam?->name,
                    'obtained_marks' => $m->obtained_marks,
                    'total_marks' => $m->exam?->total_marks,
                    'date' => $m->created_at->toDateString(),
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marksheets: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Marksheet Detail
     */
    public function marksheetDetail(Request $request, $marksheet): JsonResponse
    {
        try {
            // Placeholder - would fetch actual marksheet
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $marksheet,
                    'subjects' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marksheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Transcripts
     */
    public function transcripts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'transcripts' => [
                        [
                            'id' => 1,
                            'type' => 'Official Transcript',
                            'issued_date' => now()->toDateString(),
                        ]
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transcripts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Transcript Detail
     */
    public function transcriptDetail(Request $request, $transcript): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transcript,
                    'type' => 'Official Transcript',
                    'details' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transcript: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Documents
     */
    public function documents(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'documents' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Document Detail
     */
    public function documentDetail(Request $request, $document): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $document,
                    'title' => 'Document Title',
                    'url' => null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download Document
     */
    public function downloadDocument(Request $request, $document): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Download link generated',
                'data' => [
                    'download_url' => '/api/v1/alumni/document/' . $document . '/file',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Alumni Notices
     */
    public function notices(Request $request): JsonResponse
    {
        try {
            $notices = Notice::where('category', 'alumni')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $notices->map(fn($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'description' => $n->description,
                    'published_at' => $n->created_at,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notices: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notice Detail
     */
    public function noticeDetail(Request $request, Notice $notice): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'description' => $notice->description,
                    'published_at' => $notice->created_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'program' => $alumni->program?->name,
                    'graduation_year' => $alumni->graduation_year,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'nullable|string',
                'current_company' => 'nullable|string',
                'current_position' => 'nullable|string',
            ]);

            $user = $request->user();
            $alumni = Alumni::where('user_id', $user->id)->firstOrFail();

            $user->update([
                'phone' => $validated['phone'] ?? $user->phone,
            ]);

            $alumni->update([
                'current_company' => $validated['current_company'] ?? $alumni->current_company,
                'current_position' => $validated['current_position'] ?? $alumni->current_position,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Alumni List
     */
    public function alumniList(Request $request): JsonResponse
    {
        try {
            $alumni = Alumni::with('user', 'program')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $alumni->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->user?->name,
                    'program' => $a->program?->name,
                    'graduation_year' => $a->graduation_year,
                    'current_company' => $a->current_company,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alumni list: ' . $e->getMessage(),
            ], 500);
        }
    }
}
