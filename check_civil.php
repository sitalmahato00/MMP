<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Department;
use App\Models\Teacher;

$d = Department::where('slug', 'civil-engineering')->first();
if (!$d) {
    echo "Dept not found\n";
    // List all department slugs
    Department::all(['id','name','slug'])->each(function($dept) {
        echo "  id:{$dept->id} slug:{$dept->slug} name:{$dept->name}\n";
    });
    exit;
}

echo "Dept ID: {$d->id}\n";
echo "All teachers (including soft-deleted): " . Teacher::withTrashed()->where('department_id', $d->id)->count() . "\n";
echo "All teachers (no soft-delete filter): " . Teacher::where('department_id', $d->id)->count() . "\n";
echo "Active (is_active=true): " . Teacher::where('department_id', $d->id)->where('is_active', true)->count() . "\n";
echo "Inactive/null: " . Teacher::where('department_id', $d->id)->where(function($q) {
    $q->where('is_active', false)->orWhereNull('is_active');
})->count() . "\n";

echo "\nAll teachers for this dept:\n";
Teacher::where('department_id', $d->id)->withTrashed()->get(['id','user_id','designation','is_active','deleted_at'])->each(function($t) {
    $active = $t->is_active ? 'true' : 'false';
    $deleted = $t->deleted_at ? 'DELETED' : 'ok';
    echo "  T:{$t->id} user:{$t->user_id} active:{$active} desig:{$t->designation} status:{$deleted}\n";
});
