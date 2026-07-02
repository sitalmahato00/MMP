<?php

namespace App\Services\Forms;

use App\Models\MaterialRequest;
use App\Models\RepairOrder;
use App\Models\AuditLog;

class DashboardService
{
    public function getStats(): array
    {
        $materialCounts = MaterialRequest::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'recommended' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'printed' THEN 1 ELSE 0 END) as printed
        ")->first();

        $repairCounts = RepairOrder::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'recommended' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'printed' THEN 1 ELSE 0 END) as printed
        ")->first();

        $recentMaterials = MaterialRequest::with(['user', 'department'])
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'number' => $m->request_number,
                'type' => 'material',
                'applicant' => $m->user?->name,
                'department' => $m->department?->name,
                'status' => $m->status,
                'date_bs' => $m->date_bs,
            ]);

        $recentRepairs = RepairOrder::with(['user', 'department'])
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'number' => $r->repair_number,
                'type' => 'repair',
                'applicant' => $r->user?->name,
                'department' => $r->department?->name,
                'status' => $r->status,
                'date_bs' => $r->date_bs,
            ]);

        $recentForms = collect($recentMaterials)->merge($recentRepairs)
            ->sortByDesc('date_bs')->take(10)->values();

        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')->limit(10)->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user' => $log->user?->only(['id', 'name']),
                'action' => $log->action,
                'created_at' => $log->created_at->diffForHumans(),
            ]);

        return [
            'total_requests' => ($materialCounts->total ?? 0) + ($repairCounts->total ?? 0),
            'draft' => ($materialCounts->draft ?? 0) + ($repairCounts->draft ?? 0),
            'pending' => ($materialCounts->submitted ?? 0) + ($repairCounts->submitted ?? 0),
            'recommended' => ($materialCounts->recommended ?? 0) + ($repairCounts->recommended ?? 0),
            'approved' => ($materialCounts->approved ?? 0) + ($repairCounts->approved ?? 0),
            'rejected' => ($materialCounts->rejected ?? 0) + ($repairCounts->rejected ?? 0),
            'printed' => ($materialCounts->printed ?? 0) + ($repairCounts->printed ?? 0),
            'recent_forms' => $recentForms,
            'recent_activities' => $recentActivities,
        ];
    }
}
