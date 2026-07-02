<?php

namespace App\Services\Forms;

use App\Models\RepairOrder;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class RepairOrderService
{
    public function getAll(array $filters = [])
    {
        $query = RepairOrder::with(['user', 'department', 'approvals.user'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) $query->search($filters['search']);
        if (!empty($filters['status'])) $query->byStatus($filters['status']);
        if (!empty($filters['department_id'])) $query->byDepartment($filters['department_id']);
        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): RepairOrder
    {
        return RepairOrder::with(['user', 'department', 'approvals.user'])->findOrFail($id);
    }

    public function create(array $data): RepairOrder
    {
        return DB::transaction(function () use ($data) {
            $order = RepairOrder::create([
                'date_bs' => $data['date_bs'],
                'user_id' => auth()->id(),
                'department_id' => $data['department_id'],
                'equipment_name' => $data['equipment_name'],
                'problem_description' => $data['problem_description'],
                'estimated_cost' => $data['estimated_cost'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'remarks' => $data['remarks'] ?? null,
            ]);

            AuditLog::log('repair_order_created', $order);
            return $order->load(['user', 'department', 'approvals.user']);
        });
    }

    public function update(int $id, array $data): RepairOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->findById($id);
            $order->update([
                'date_bs' => $data['date_bs'] ?? $order->date_bs,
                'department_id' => $data['department_id'] ?? $order->department_id,
                'equipment_name' => $data['equipment_name'] ?? $order->equipment_name,
                'problem_description' => $data['problem_description'] ?? $order->problem_description,
                'estimated_cost' => $data['estimated_cost'] ?? $order->estimated_cost,
                'remarks' => $data['remarks'] ?? $order->remarks,
            ]);

            AuditLog::log('repair_order_updated', $order);
            return $order->load(['user', 'department', 'approvals.user']);
        });
    }

    public function delete(int $id): void
    {
        $order = $this->findById($id);
        $order->delete();
        AuditLog::log('repair_order_deleted', $order);
    }

    public function submit(int $id): RepairOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->findById($id);
            $order->update(['status' => 'submitted']);
            AuditLog::log('repair_order_submitted', $order);
            return $order->load(['user', 'department', 'approvals.user']);
        });
    }
}
