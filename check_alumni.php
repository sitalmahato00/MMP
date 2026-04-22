<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Alumni Debug Info ===\n\n";

echo "Total students: " . \App\Models\Student::count() . "\n";
echo "Active students: " . \App\Models\Student::where('is_active', true)->count() . "\n";
echo "Total alumni: " . \App\Models\Alumni::count() . "\n\n";

$student = \App\Models\Student::with('program')->first();
if ($student) {
    echo "Sample student:\n";
    echo "  - Semester: " . $student->current_semester . "\n";
    echo "  - Program total semesters: " . ($student->program->total_semesters ?? 'N/A') . "\n";
    echo "  - Is in final semester: " . ($student->current_semester >= $student->program->total_semesters ? 'YES' : 'NO') . "\n\n";
}

echo "Students in semester 6: " . \App\Models\Student::where('current_semester', 6)->count() . "\n";
echo "Students in semester >= 6: " . \App\Models\Student::where('current_semester', '>=', 6)->count() . "\n\n";

$finalSemStudents = \App\Models\Student::whereHas('program', function($q) {
    $q->whereRaw('students.current_semester >= programs.total_semesters');
})->count();
echo "Students in final semester (query): " . $finalSemStudents . "\n";
