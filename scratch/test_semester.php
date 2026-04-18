<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\{AcademicSession, AcademicSessionSemester};

$s = AcademicSession::first();
echo "Session: {$s->id} {$s->name}\n";

try {
    $sem = AcademicSessionSemester::create([
        'academic_session_id' => $s->id,
        'semester_number' => 99,
        'start_date' => now(),
        'end_date' => now()->addMonths(6),
        'status' => 'upcoming',
        'is_active' => false,
    ]);
    echo "Created semester ID: {$sem->id}\n";
    $sem->delete();
    echo "Deleted test semester.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
