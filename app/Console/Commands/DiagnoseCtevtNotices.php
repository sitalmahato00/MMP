<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiagnoseCtevtNotices extends Command
{
    protected $signature = 'diagnose:ctevt-notices {--clear-cache : Clear CTEVT notice cache}';
    protected $description = 'Diagnose CTEVT notice API connectivity and cache status';

    public function handle(): int
    {
        $this->info('🔍 CTEVT Notice API Diagnostics');
        $this->newLine();

        if ($this->option('clear-cache')) {
            $this->clearCtevtCache();
            $this->newLine();
        }

        $this->checkConfiguration();
        $this->newLine();

        $this->checkDatabaseStatus();
        $this->newLine();

        $this->checkCacheStatus();
        $this->newLine();

        $this->testApiConnectivity('general');
        $this->newLine();

        $this->testApiConnectivity('result');
        $this->newLine();

        return Command::SUCCESS;
    }

    private function clearCtevtCache(): void
    {
        $this->info('🗑️  Clearing CTEVT notice cache...');

        $cacheKeys = [
            'public:ctevt_notices:general:5',
            'public:ctevt_notices:result:5',
            'public:ctevt_notices:general:6',
            'public:ctevt_notices:result:6',
            'public:ctevt_notices:general:10',
            'public:ctevt_notices:result:10',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
            $this->line("  ✓ Cleared: {$key}");
        }

        $this->info('✅ Cache cleared successfully');
    }

    private function checkConfiguration(): void
    {
        $this->info('⚙️  Configuration Check:');

        $feedUrl = config('services.ctevt_notice.feed_url');
        $generalUrl = config('services.ctevt_notice.general_url');
        $resultUrl = config('services.ctevt_notice.result_url');

        $this->line("  Feed URL: {$feedUrl}");
        $this->line("  General URL: {$generalUrl}");
        $this->line("  Result URL: {$resultUrl}");
    }

    private function checkDatabaseStatus(): void
    {
        $this->info('🗄️  Database Fallback Status:');

        $generalCount = \App\Models\CtevtNotice::where('type', 'general')->count();
        $resultCount = \App\Models\CtevtNotice::where('type', 'result')->count();

        $this->line("  General Notices: {$generalCount} stored");
        $this->line("  Result Notices: {$resultCount} stored");

        if ($generalCount > 0) {
            $latestGeneral = \App\Models\CtevtNotice::where('type', 'general')->latest('fetched_at')->first();
            $this->line("  Latest General: " . $latestGeneral->fetched_at->diffForHumans());
        }

        if ($resultCount > 0) {
            $latestResult = \App\Models\CtevtNotice::where('type', 'result')->latest('fetched_at')->first();
            $this->line("  Latest Result: " . $latestResult->fetched_at->diffForHumans());
        }

        if ($generalCount === 0 && $resultCount === 0) {
            $this->warn("  ⚠️  No notices in database. Run 'php artisan ctevt:fetch-notices' to populate.");
        }
    }

    private function checkCacheStatus(): void
    {
        $this->info('💾 Cache Status:');

        $cacheKeys = [
            'public:ctevt_notices:general:5' => 'General Notices (5)',
            'public:ctevt_notices:result:5' => 'Result Notices (5)',
        ];

        foreach ($cacheKeys as $key => $label) {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                $itemCount = count($data['items'] ?? []);
                $state = $data['source_state'] ?? 'unknown';
                $this->line("  ✓ {$label}: Cached ({$state}, {$itemCount} items)");
            } else {
                $this->line("  ✗ {$label}: Not cached");
            }
        }
    }

    private function testApiConnectivity(string $type): void
    {
        $isResult = $type === 'result';
        $label = $isResult ? 'Result Notices' : 'General Notices';

        $this->info("🌐 Testing {$label} API:");

        $feedUrl = config('services.ctevt_notice.feed_url', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices');

        $params = $this->buildCtevtNoticeFeedParams($isResult, 5);

        $this->line("  URL: {$feedUrl}");
        $this->line("  Params: is_result=" . ($isResult ? '1' : '0'));

        try {
            $startTime = microtime(true);

            $response = Http::timeout(10)
                ->retry(2, 500)
                ->withoutVerifying()
                ->accept('application/json,text/javascript,*/*;q=0.1')
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->get($feedUrl, $params);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $payload = $response->json();
                $itemCount = count($payload['data'] ?? []);
                $recordsTotal = $payload['recordsTotal'] ?? 0;

                $this->info("  ✅ Success ({$duration}ms)");
                $this->line("  Status: {$response->status()}");
                $this->line("  Items: {$itemCount}");
                $this->line("  Total Records: {$recordsTotal}");

                if ($itemCount > 0) {
                    $this->line("  Sample: " . ($payload['data'][0]['notice_title'] ?? 'N/A'));
                }
            } else {
                $this->error("  ❌ Failed ({$duration}ms)");
                $this->line("  Status: {$response->status()}");
                $this->line("  Body: " . substr($response->body(), 0, 200));
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Exception: " . $e->getMessage());
            $this->line("  Type: " . get_class($e));
        }
    }

    private function buildCtevtNoticeFeedParams(bool $isResult, int $limit): array
    {
        return [
            'draw' => 1,
            'columns' => [
                ['data' => 'serial_no', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'updated_date', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'notice_title', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'notice_files', 'name' => '', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'publisher', 'name' => '', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [
                ['column' => 0, 'dir' => 'asc'],
            ],
            'start' => 0,
            'length' => $limit,
            'search' => [
                'value' => '',
                'regex' => 'false',
            ],
            'tab_id' => 'tab-0',
            'is_result' => $isResult ? '1' : '0',
        ];
    }
}
