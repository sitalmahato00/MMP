<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $dateFrom = $request->filled('date_from') ? Carbon::parse((string) $request->date_from)->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse((string) $request->date_to)->endOfDay() : null;

        $applications = Application::query()
            ->with('department:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->gender, fn($q) => $q->where('gender', $request->gender))
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $departments = \App\Models\Department::query()->orderBy('name')->get(['id', 'name']);

        $overviewStats = $this->buildOverviewStats();
        $insights = $this->buildInsights();

        return view('admin.applications.index', compact('applications', 'departments', 'overviewStats', 'insights'));
    }

    public function show(Application $application)
    {
        $application->load('department');
        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application)
    {
        $data = $request->validate([
            'status'      => 'required|in:pending,reviewed,contacted,accepted,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $application->update($data);

        return back()->with('success', 'Application status updated.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $rawIds = $request->input('application_ids');
        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $request->merge([
                'application_ids' => is_array($decoded)
                    ? $decoded
                    : array_values(array_filter(array_map('trim', explode(',', $rawIds)))),
            ]);
        }

        $data = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'required|integer|exists:applications,id',
            'status' => 'required|in:reviewed,contacted,accepted,rejected',
        ]);

        Application::query()
            ->whereIn('id', $data['application_ids'])
            ->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Bulk status update applied to selected applications.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
    }

    private function buildOverviewStats(): array
    {
        $currentStart = now()->subDays(29)->startOfDay();
        $currentEnd = now()->endOfDay();
        $previousStart = now()->subDays(59)->startOfDay();
        $previousEnd = now()->subDays(30)->endOfDay();

        $metrics = [
            ['key' => 'total', 'label' => 'Total Applications', 'status' => null, 'tone' => 'slate'],
            ['key' => 'pending', 'label' => 'Pending Review', 'status' => 'pending', 'tone' => 'zinc'],
            ['key' => 'accepted', 'label' => 'Accepted', 'status' => 'accepted', 'tone' => 'emerald'],
            ['key' => 'rejected', 'label' => 'Rejected', 'status' => 'rejected', 'tone' => 'rose'],
            ['key' => 'contacted', 'label' => 'Contacted', 'status' => 'contacted', 'tone' => 'violet'],
            ['key' => 'reviewed', 'label' => 'Reviewed', 'status' => 'reviewed', 'tone' => 'sky'],
        ];

        return collect($metrics)->map(function (array $metric) use ($currentStart, $currentEnd, $previousStart, $previousEnd) {
            $total = Application::query()
                ->when($metric['status'], fn($q) => $q->where('status', $metric['status']))
                ->count();

            $current = Application::query()
                ->when($metric['status'], fn($q) => $q->where('status', $metric['status']))
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->count();

            $previous = Application::query()
                ->when($metric['status'], fn($q) => $q->where('status', $metric['status']))
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->count();

            $trend = $this->buildTrend($current, $previous);

            return [
                'key' => $metric['key'],
                'label' => $metric['label'],
                'value' => $total,
                'tone' => $metric['tone'],
                'trend' => $trend,
            ];
        })->all();
    }

    private function buildInsights(): array
    {
        $deptRows = Application::query()
            ->leftJoin('departments', 'departments.id', '=', 'applications.department_id')
            ->selectRaw('COALESCE(departments.name, "Unassigned") as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->get();

        $genderRows = Application::query()
            ->selectRaw('COALESCE(gender, "unspecified") as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('gender')
            ->orderByDesc('total')
            ->get();

        $statusRows = Application::query()
            ->selectRaw('status as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $dailyRows = Application::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw("DATE(created_at) as day")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $line = collect();
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $line->push([
                'label' => bsDate($date, 'd M') ?: Carbon::parse($date)->format('d M'),
                'value' => (int) ($dailyRows[$date]->total ?? 0),
            ]);
        }

        return [
            'department' => $this->normalizeInsightRows($deptRows),
            'gender' => $this->normalizeInsightRows($genderRows),
            'status' => $this->normalizeInsightRows($statusRows),
            'daily' => $line->all(),
        ];
    }

    private function normalizeInsightRows(Collection $rows): array
    {
        $max = max(1, (int) $rows->max('total'));

        return $rows->map(function ($row) use ($max) {
            $total = (int) ($row->total ?? 0);

            return [
                'label' => (string) ($row->label ?? 'Unknown'),
                'value' => $total,
                'percent' => round(($total / $max) * 100, 1),
            ];
        })->values()->all();
    }

    private function buildTrend(int $current, int $previous): array
    {
        if ($current === $previous) {
            return ['label' => '0%', 'direction' => 'flat'];
        }

        if ($previous <= 0) {
            return ['label' => '+100%', 'direction' => 'up'];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        return [
            'label' => ($delta > 0 ? '+' : '') . $delta . '%',
            'direction' => $delta > 0 ? 'up' : 'down',
        ];
    }
}
