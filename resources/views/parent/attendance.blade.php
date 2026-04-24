@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Attendance</h1>
    <p class="text-sm text-slate-500 mt-1">Track your children's attendance records.</p>
</div>

@forelse($childrenData as $childData)
@php
    $s = $childData['student'];
    $pct = $childData['pct'];
    $attColor = $pct === null ? 'slate' : ($pct >= 75 ? 'emerald' : ($pct >= 50 ? 'amber' : 'red'));
@endphp
<div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between border-b border-slate-100 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-bold text-white">
                @if($s->user?->avatar)
                    <img src="{{ Storage::url($s->user->avatar) }}" alt="{{ $s->user->name }}" class="h-10 w-10 rounded-xl object-cover">
                @else
                    {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
                @endif
            </div>
            <div>
                <h3 class="font-bold text-slate-900">{{ $s->user?->name }}</h3>
                <p class="text-xs text-slate-500">{{ $s->department?->name }} · {{ $s->program?->name }}</p>
            </div>
        </div>
        <div class="flex gap-4 text-xs mt-2 sm:mt-0">
            <span class="font-bold text-emerald-600">Present: {{ $childData['present'] }}</span>
            <span class="font-bold text-red-600">Absent: {{ $childData['absent'] }}</span>
            <span class="font-bold text-amber-600">Late: {{ $childData['late'] }}</span>
            <span class="font-bold text-{{ $attColor }}-700">{{ $pct !== null ? $pct.'%' : '—' }}</span>
        </div>
    </div>
    
    {{-- Subject-wise Attendance Chart --}}
    @if($childData['subjectAttendance']->isNotEmpty())
        <div class="border-b border-slate-100 p-5">
            <h4 class="text-sm font-semibold text-slate-900 mb-4">Subject-wise Attendance Overview</h4>
            <div class="h-80">
                <canvas id="subjectAttendanceChart_{{ $s->id }}"></canvas>
            </div>
        </div>
    @endif
    
    {{-- Subject-wise Detailed Breakdown --}}
    @if($childData['subjectAttendance']->isNotEmpty())
        <div class="p-5">
            <h4 class="text-sm font-semibold text-slate-900 mb-4">Subject-wise Attendance Details</h4>
            <div class="space-y-3">
                @foreach($childData['subjectAttendance'] as $subject)
                    <div class="rounded-lg border border-slate-200 p-4 hover:bg-slate-50 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h5 class="text-sm font-semibold text-slate-900">{{ $subject['subject_name'] }}</h5>
                                <p class="text-xs text-slate-500">{{ $subject['subject_code'] }} · {{ ucfirst($subject['subject_type']) }}</p>
                            </div>
                        </div>
                        
                        <div class="grid gap-3 sm:grid-cols-2">
                            {{-- Class Attendance --}}
                            <div class="rounded-lg bg-blue-50 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-blue-700">Class Sessions</span>
                                    <span class="text-lg font-bold text-blue-900">{{ $subject['class_percentage'] }}%</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-blue-600">
                                    <span>Present: {{ $subject['class_present'] }}</span>
                                    <span>Total: {{ $subject['class_total'] }}</span>
                                </div>
                                {{-- Progress Bar --}}
                                <div class="mt-2 h-2 w-full rounded-full bg-blue-200">
                                    <div class="h-2 rounded-full bg-blue-600 transition-all" style="width: {{ $subject['class_percentage'] }}%"></div>
                                </div>
                            </div>
                            
                            {{-- Lab Attendance --}}
                            @if($subject['has_lab'])
                                <div class="rounded-lg bg-purple-50 p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-medium text-purple-700">Lab Sessions</span>
                                        <span class="text-lg font-bold text-purple-900">{{ $subject['lab_percentage'] }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-purple-600">
                                        <span>Present: {{ $subject['lab_present'] }}</span>
                                        <span>Total: {{ $subject['lab_total'] }}</span>
                                    </div>
                                    {{-- Progress Bar --}}
                                    <div class="mt-2 h-2 w-full rounded-full bg-purple-200">
                                        <div class="h-2 rounded-full bg-purple-600 transition-all" style="width: {{ $subject['lab_percentage'] }}%"></div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-lg bg-slate-50 p-3 flex items-center justify-center">
                                    <p class="text-xs text-slate-400 italic">No lab sessions for this subject</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="p-5">
            <p class="text-sm text-slate-500 italic text-center py-4">No attendance records yet.</p>
        </div>
    @endif
</div>
@empty
<div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
    <p class="text-sm text-slate-500">No children linked to your account.</p>
</div>
@endforelse

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    
    // Subject-wise Attendance Charts
    @foreach($childrenData as $childData)
        @if($childData['subjectAttendance']->isNotEmpty())
            (function() {
                const ctx = document.getElementById('subjectAttendanceChart_{{ $childData['student']->id }}');
                if (!ctx) return;
                
                const data = @json($childData['subjectAttendance']);
                
                const labels = data.map(item => item.subject_code);
                const classData = data.map(item => item.class_percentage);
                const labData = data.map(item => item.has_lab ? item.lab_percentage : 0);
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Class',
                                data: classData,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1
                            },
                            {
                                label: 'Lab',
                                data: labData,
                                backgroundColor: 'rgba(168, 85, 247, 0.8)',
                                borderColor: 'rgb(168, 85, 247)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const dataIndex = context.dataIndex;
                                        const item = data[dataIndex];
                                        const isLab = context.dataset.label === 'Lab';
                                        
                                        if (isLab && !item.has_lab) {
                                            return null;
                                        }
                                        
                                        const present = isLab ? item.lab_present : item.class_present;
                                        const total = isLab ? item.lab_total : item.class_total;
                                        const percentage = context.parsed.y;
                                        
                                        return `${context.dataset.label}: ${percentage}% (${present}/${total})`;
                                    },
                                    title: function(context) {
                                        const dataIndex = context[0].dataIndex;
                                        const item = data[dataIndex];
                                        return item.subject_name;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    },
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: '#f1f5f9'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            })();
        @endif
    @endforeach
});
</script>
@endpush
@endsection
