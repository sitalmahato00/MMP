<?php

namespace App\Services\Forms;

use App\Models\MaterialRequest;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    public function getAll(array $filters = [])
    {
        $query = MaterialRequest::with(['user', 'department', 'items', 'approvals.user'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) $query->search($filters['search']);
        if (!empty($filters['status'])) $query->byStatus($filters['status']);
        if (!empty($filters['department_id'])) $query->byDepartment($filters['department_id']);
        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): MaterialRequest
    {
        return MaterialRequest::with(['user', 'department', 'items', 'approvals.user'])->findOrFail($id);
    }

    public function create(array $data): MaterialRequest
    {
        return DB::transaction(function () use ($data) {
            $request = MaterialRequest::create([
                'date_bs' => $data['date_bs'],
                'user_id' => auth()->id(),
                'department_id' => $data['department_id'],
                'status' => $data['status'] ?? 'draft',
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $request->items()->create([
                    'item_name' => $item['item_name'],
                    'specification' => $item['specification'] ?? null,
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            AuditLog::log('material_request_created', $request);
            return $request->load(['user', 'department', 'items', 'approvals.user']);
        });
    }

    public function update(int $id, array $data): MaterialRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $request = $this->findById($id);
            $request->update([
                'date_bs' => $data['date_bs'] ?? $request->date_bs,
                'department_id' => $data['department_id'] ?? $request->department_id,
                'remarks' => $data['remarks'] ?? $request->remarks,
            ]);

            if (isset($data['items'])) {
                $request->items()->delete();
                foreach ($data['items'] as $item) {
                    $request->items()->create([
                        'item_name' => $item['item_name'],
                        'specification' => $item['specification'] ?? null,
                        'unit' => $item['unit'],
                        'quantity' => $item['quantity'],
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            }

            AuditLog::log('material_request_updated', $request);
            return $request->load(['user', 'department', 'items', 'approvals.user']);
        });
    }

    public function delete(int $id): void
    {
        $request = $this->findById($id);
        $request->delete();
        AuditLog::log('material_request_deleted', $request);
    }

    public function submit(int $id): MaterialRequest
    {
        return DB::transaction(function () use ($id) {
            $request = $this->findById($id);
            $request->update(['status' => 'submitted']);
            AuditLog::log('material_request_submitted', $request);
            return $request->load(['user', 'department', 'items', 'approvals.user']);
        });
    }

    public function duplicate(int $id): MaterialRequest
    {
        return DB::transaction(function () use ($id) {
            $original = $this->findById($id);
            $copy = MaterialRequest::create([
                'date_bs' => bsDate(now()),
                'user_id' => auth()->id(),
                'department_id' => $original->department_id,
                'status' => 'draft',
            ]);

            foreach ($original->items as $item) {
                $copy->items()->create($item->only(['item_name', 'specification', 'unit', 'quantity', 'remarks']));
            }

            AuditLog::log('material_request_duplicated', $copy);
            return $copy->load(['user', 'department', 'items']);
        });
    }
}
