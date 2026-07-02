<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\Controller;
use App\Services\Forms\RepairOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepairOrderController extends Controller
{
    public function __construct(
        protected RepairOrderService $repairOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->repairOrderService->getAll($request->all());
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->repairOrderService->findById($id);
            return response()->json(['success' => true, 'data' => $order]);
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
                'equipment_name' => 'required|string|max:255',
                'problem_description' => 'required|string',
                'estimated_cost' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string',
                'status' => 'nullable|string|in:draft,submitted',
            ]);

            $result = $this->repairOrderService->create($validated);
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
                'equipment_name' => 'sometimes|string|max:255',
                'problem_description' => 'sometimes|string',
                'estimated_cost' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string',
            ]);

            $result = $this->repairOrderService->update($id, $validated);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->repairOrderService->delete($id);
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function submit(int $id): JsonResponse
    {
        try {
            $result = $this->repairOrderService->submit($id);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Submitted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function pdf(int $id)
    {
        try {
            $order = $this->repairOrderService->findById($id);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.repair-order', [
                'order' => $order,
                'college' => \App\Models\SiteSetting::pluck('value', 'key'),
            ]);
            return $pdf->download("repair-order-{$order->repair_number}.pdf");
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
