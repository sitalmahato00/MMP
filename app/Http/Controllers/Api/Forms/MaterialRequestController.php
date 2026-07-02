<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Services\Forms\MaterialRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function __construct(
        protected MaterialRequestService $materialRequestService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->materialRequestService->getAll($request->all());
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $request = $this->materialRequestService->findById($id);
            return response()->json(['success' => true, 'data' => $request]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date_bs' => 'required|string',
                'department_id' => 'required|exists:departments,id',
                'remarks' => 'nullable|string',
                'status' => 'nullable|string|in:draft,submitted',
                'items' => 'required|array|min:1',
                'items.*.item_name' => 'required|string',
                'items.*.specification' => 'nullable|string',
                'items.*.unit' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.remarks' => 'nullable|string',
            ]);

            $result = $this->materialRequestService->create($validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date_bs' => 'sometimes|string',
                'department_id' => 'sometimes|exists:departments,id',
                'remarks' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.item_name' => 'required_with:items|string',
                'items.*.specification' => 'nullable|string',
                'items.*.unit' => 'required_with:items|string',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'items.*.remarks' => 'nullable|string',
            ]);

            $result = $this->materialRequestService->update($id, $validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->materialRequestService->delete($id);
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function submit(int $id): JsonResponse
    {
        try {
            $result = $this->materialRequestService->submit($id);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Submitted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function duplicate(int $id): JsonResponse
    {
        try {
            $result = $this->materialRequestService->duplicate($id);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Duplicated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function pdf(int $id)
    {
        try {
            $request = $this->materialRequestService->findById($id);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.material-request', [
                'request' => $request,
                'college' => \App\Models\SiteSetting::pluck('value', 'key'),
            ]);
            return $pdf->download("material-request-{$request->request_number}.pdf");
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
