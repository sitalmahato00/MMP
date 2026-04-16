<?php
// Fix double-encoded google_maps_iframe value
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$setting = \App\Models\SiteSetting::where('key', 'google_maps_iframe')->first();
if (!$setting) {
    echo "No google_maps_iframe setting found.\n";
    exit;
}

$before = $setting->value;
// Decode up to 3 levels of HTML entity encoding
$decoded = $before;
for ($i = 0; $i < 3; $i++) {
    $once = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($once === $decoded) break;
    $decoded = $once;
}

echo "BEFORE: " . substr($before, 0, 120) . "\n";
echo "AFTER:  " . substr($decoded, 0, 120) . "\n";

$setting->update(['value' => $decoded]);
echo "Updated successfully.\n";
