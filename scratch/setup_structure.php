<?php

$directories = [
    'app/Domain/Auth',
    'app/Domain/Users',
    'app/Domain/Academic',
    'app/Domain/Attendance',
    'app/Domain/Exams',
    'app/Domain/Results',
    'app/Domain/Departments',
    'app/Domain/Notices',
    'app/Domain/Media',
    'app/Domain/Alumni',
    'app/Domain/Sessions',
    'app/Domain/Reports',
    'app/Http/Controllers/Admin',
    'app/Http/Controllers/HOD',
    'app/Http/Controllers/Teacher',
    'app/Http/Controllers/Student',
    'app/Http/Controllers/Parent',
    'app/Http/Controllers/Alumni',
    'app/Http/Controllers/Public',
    'app/Services',
    'app/Repositories',
    'app/Actions',
    'app/Policies',
    'app/Middleware',
    'app/Models',
    'resources/views/layouts',
    'resources/views/components',
    'resources/views/admin',
    'resources/views/hod',
    'resources/views/teacher',
    'resources/views/student',
    'resources/views/parent',
    'resources/views/alumni',
    'resources/views/public',
    'resources/views/auth',
    'public/assets',
    'public/uploads',
    'routes/admin.php',
    'routes/hod.php',
    'routes/teacher.php',
    'routes/student.php',
    'routes/parent.php',
    'routes/alumni.php',
];

foreach ($directories as $dir) {
    if (strpos($dir, '.php') !== false) {
        $path = dirname($dir);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        if (!file_exists($dir)) {
            file_put_contents($dir, "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nRoute::middleware(['auth'])->group(function () {\n    // " . basename($dir, '.php') . " routes\n});\n");
        }
    } else {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "Created directory: $dir\n";
        }
    }
}
