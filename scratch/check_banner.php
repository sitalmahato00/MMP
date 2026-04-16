<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$banners = \App\Models\Banner::all();
foreach ($banners as $b) {
    echo "ID={$b->id} | title=[{$b->title}] | subtitle=[{$b->subtitle}] | link=[{$b->link}] | is_active={$b->is_active} | image={$b->image}\n";
}
echo "Total: " . $banners->count() . "\n";
