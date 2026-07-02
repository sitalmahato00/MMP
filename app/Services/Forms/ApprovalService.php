<?php

namespace App\Services\Forms;

use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\MaterialRequest;
use App\Models\RepairOrder;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function getPendingForUser()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray();

        $materialQuery = MaterialRequest::with(['user', 'department'])
            ->where('status', 'submitted');

        $repairQuery = RepairOrder::with(['user', 'department'])
            ->where('status', 'submitted');

        if (in_array('recommendation-officer', $roles)) {
            $materialQuery->where('status', 'submitted');
            $repairQuery->where('status', 'submitted');
        }

        if (in_array('approval-officer', $roles)) {
            $materialQuery->where('status', 'recommended');
            $repairQuery->where('status', 'recommended');
        }

        $materials = $materialQuery->get()->map(fn($m) => [
            'type' => 'material',
            'id' => $m->id,
            'number' => $m->request_number,
            'applicant' => $m->user?->name,
            'department' => $m->department?->name,
            'status' => $m->status,
            'date_bs' => $m->date_bs,
        ]);

        $repairs = $repairQuery->get()->map(fn($r) => [
            'type' => 'repair',
            'id' => $r->id,
            'number' => $r->repair_number,
            'applicant' => $r->user?->name,
            'department' => $r->department?->name,
            'status' => $r->status,
            'date_bs' => $r->date_bs,
        ]);

        $submissions = collect($materials)->merge($repairs)->sortByDesc('date_bs')->values();

        return [
            'submissions' => [
                'data' => $submissions,
                'total' => $submissions->count(),
            ],
        ];
    }

    public function getHistory()
    {
        return Approval::with(['user'])->orderBy('created_at', 'desc')->paginate(20);
    }

    public function recommend(string $type, int $id, array $data): Approval
    {
        return DB::transaction(function () use ($type, $id, $data) {
            $model = $type === 'material'
                ? MaterialRequest::findOrFail($id)
                : RepairOrder::findOrFail($id);

            $model->update(['status' => 'recommended']);

            $approval = Approval::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'user_id' => auth()->id(),
                'role' => auth()->user()->primaryRole(),
                'status' => 'recommended',
                'remarks' => $data['remarks'] ?? null,
                'signature' => $data['signature'] ?? null,
                'date_bs' => bsDate(now()),
                'time' => now()->format('H:i:s'),
            ]);

            AuditLog::log("{$type}_request_recommended", $model);
            return $approval->load('user');
        });
    }

    public function approve(string $type, int $id, array $data): Approval
    {
        return DB::transaction(function () use ($type, $id, $data) {
            $model = $type === 'material'
                ? MaterialRequest::findOrFail($id)
                : RepairOrder::findOrFail($id);

            $model->update(['status' => 'approved']);

            $approval = Approval::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'user_id' => auth()->id(),
                'role' => auth()->user()->primaryRole(),
                'status' => 'approved',
                'remarks' => $data['remarks'] ?? null,
                'signature' => $data['signature'] ?? null,
                'date_bs' => bsDate(now()),
                'time' => now()->format('H:i:s'),
            ]);

            AuditLog::log("{$type}_request_approved", $model);
            return $approval->load('user');
        });
    }

    public function reject(string $type, int $id, array $data): Approval
    {
        return DB::transaction(function () use ($type, $id, $data) {
            $model = $type === 'material'
                ? MaterialRequest::findOrFail($id)
                : RepairOrder::findOrFail($id);

            $model->update(['status' => 'rejected']);

            $approval = Approval::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'user_id' => auth()->id(),
                'role' => auth()->user()->primaryRole(),
                'status' => 'rejected',
                'remarks' => $data['remarks'] ?? null,
                'signature' => null,
                'date_bs' => bsDate(now()),
                'time' => now()->format('H:i:s'),
            ]);

            AuditLog::log("{$type}_request_rejected", $model);
            return $approval->load('user');
        });
    }
}
