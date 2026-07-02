<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Services\Forms\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    public function pending(): JsonResponse
    {
        try {
            $data = $this->approvalService->getPendingForUser();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function history(): JsonResponse
    {
        try {
            $data = $this->approvalService->getHistory();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recommend(string $type, int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'remarks' => 'nullable|string',
                'signature' => 'nullable|string',
            ]);
            $result = $this->approvalService->recommend($type, $id, $validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Recommended successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(string $type, int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'remarks' => 'nullable|string',
                'signature' => 'nullable|string',
            ]);
            $result = $this->approvalService->approve($type, $id, $validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(string $type, int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'remarks' => 'required|string',
            ]);
            $result = $this->approvalService->reject($type, $id, $validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
