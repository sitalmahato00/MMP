<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\StudentController;
use App\Models\AttendanceSession;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function profile_call(string $label, callable $callback): void
{
    DB::flushQueryLog();
    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();

    gc_collect_cycles();
    $beforePeak = memory_get_peak_usage(true);
    $start = hrtime(true);

    $callback();

    $elapsedMs = (hrtime(true) - $start) / 1_000_000;
    $queryCount = count(DB::getQueryLog());
    $peakBytes = max(0, memory_get_peak_usage(true) - $beforePeak);
    $peakMb = $peakBytes / 1024 / 1024;

    echo sprintf("%-30s  %9.2f ms  %5d queries  +%7.2f MB\n", $label, $elapsedMs, $queryCount, $peakMb);
}

echo "=== Endpoint Profiling (controller data prep) ===\n";

$exam = Exam::query()->withCount('marks')->orderByDesc('marks_count')->first();
$attendanceSession = AttendanceSession::query()->withCount('records')->orderByDesc('records_count')->first();

if (! $exam) {
    echo "No exam rows found; cannot profile exam endpoint.\n";
}

if (! $attendanceSession) {
    echo "No attendance sessions found; cannot profile attendance endpoint.\n";
}

if ($exam) {
    profile_call('Exam show (overview tab)', function () use ($exam): void {
        $request = Request::create('/admin/exams/' . $exam->id, 'GET', ['tab' => 'overview']);
        app()->instance('request', $request);
        app(ExamController::class)->show($exam->fresh());
    });

    profile_call('Exam show (marks tab)', function () use ($exam): void {
        $request = Request::create('/admin/exams/' . $exam->id, 'GET', ['tab' => 'marks']);
        app()->instance('request', $request);
        app(ExamController::class)->show($exam->fresh());
    });
}

if ($attendanceSession) {
    profile_call('Attendance session detail', function () use ($attendanceSession): void {
        $request = Request::create('/admin/attendance/sessions/' . $attendanceSession->id, 'GET');
        app()->instance('request', $request);
        app(AttendanceController::class)->session($attendanceSession->fresh());
    });
}

profile_call('Dashboard index', function (): void {
    $request = Request::create('/admin/dashboard', 'GET');
    app()->instance('request', $request);
    app(DashboardController::class)->index($request);
});

profile_call('Students index', function (): void {
    $request = Request::create('/admin/students', 'GET');
    app()->instance('request', $request);
    app(StudentController::class)->index($request);
});

if ($exam) {
    echo "\nSample exam ID: {$exam->id}, marks_count: {$exam->marks_count}\n";
}
if ($attendanceSession) {
    echo "Sample attendance session ID: {$attendanceSession->id}, records_count: {$attendanceSession->records_count}\n";
}
