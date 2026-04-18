<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sessions = App\Models\AcademicSession::orderBy('start_date')->get();
foreach ($sessions as $session) {
    echo "\n=== {$session->name} (ID:{$session->id}) status:{$session->status} active:" . ($session->is_active ? 'Y' : 'N') . " locked:" . ($session->is_locked ? 'Y' : 'N') . " ===\n";
    $semesters = App\Models\AcademicSessionSemester::where('academic_session_id', $session->id)->orderBy('semester_number')->get();
    if ($semesters->isEmpty()) {
        echo "  (no semesters)\n";
    }
    foreach ($semesters as $s) {
        echo "  Sem {$s->semester_number}: status={$s->status} is_active=" . ($s->is_active ? 'Y' : 'N') . " dates={$s->start_date} to {$s->end_date}\n";
    }
}
echo "\nTotal semesters: " . App\Models\AcademicSessionSemester::count() . "\n";
