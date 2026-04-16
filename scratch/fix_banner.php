<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\App\Models\Banner::where('id', 1)->update([
    'title' => 'Manmohan Memorial Polytechnic',
    'subtitle' => 'Best Technical College in Nepal',
    'link' => '/departments',
]);

echo "Banner updated.\n";
$b = \App\Models\Banner::find(1);
echo "title=[{$b->title}] subtitle=[{$b->subtitle}] link=[{$b->link}]\n";
