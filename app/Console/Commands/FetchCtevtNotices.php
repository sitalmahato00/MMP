<?php

namespace App\Console\Commands;

use App\Models\CtevtNotice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchCtevtNotices extends Command
{
    protected $signature = 'ctevt:fetch-notices {--type=all : Type of notices to fetch (general, result, or all)}';
    protected $description = 'Fetch CTEVT notices from external API and store in database';

    public function handle(): int
    {
        $type = $this->option('type');
        
        if ($type === 'all' || $type === 'general') {
            $this->info('Fetching CTEVT general notices...');
            $this->fetchNotices('general');
        }
        
        if ($type === 'all' || $type === 'result') {
            $this->info('Fetching CTEVT result notices...');
            $this->fetchNotices('result');
        }
        
        $this->info('✓ CTEVT notices fetch completed!');
        
        return Command::SUCCESS;
    }

    private function fetchNotices(string $type): void
    {
        $isResult = $type === 'result';
        $feedUrl = config('services.ctevt_notice.feed_url', 'https://itms.ctevt.org.np:5580/notices/get-ajax-notices');
        
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->withoutVerifying()
                ->accept('application/json,text/javascript,*/*;q=0.1')
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->get($feedUrl, $this->buildParams($isResult));

            if (!$response->successful()) {
                $this->error("Failed to fetch {$type} notices: HTTP {$response->status()}");
                Log::warning("CTEVT fetch failed for {$type}", ['status' => $response->status()]);
                return;
            }

            $payload = $response->json();

            if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
                $this->warn("No data found for {$type} notices");
                return;
            }

            $count = 0;
            foreach ($payload['data'] as $item) {
                $noticeData = $this->mapNoticeData($item, $type);
                
                if (!$noticeData) {
                    continue;
                }

                CtevtNotice::updateOrCreate(
                    [
                        'type' => $type,
                        'external_id' => $noticeData['external_id'],
                    ],
                    $noticeData
                );
                
                $count++;
            }

            $this->info("  → Stored {$count} {$type} notices");

        } catch (\Exception $e) {
            $this->error("Error fetching {$type} notices: " . $e->getMessage());
            Log::error("CTEVT fetch error for {$type}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function buildParams(bool $isResult): array
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
            'length' => 20,
            'search' => [
                'value' => '',
                'regex' => 'false',
            ],
            'tab_id' => 'tab-0',
            'is_result' => $isResult ? '1' : '0',
        ];
    }

    private function mapNoticeData(array $item, string $type): ?array
    {
        // Extract title from HTML
        $titleHtml = trim((string) ($item['notice_title'] ?? ''));
        
        if ($titleHtml === '') {
            return null;
        }

        // Extract URL and text from HTML link
        [$titleUrl, $titleText] = $this->extractFirstHtmlLink($titleHtml);
        
        // Generate ID from notice_cd or hash
        $externalId = $item['notice_cd'] ?? null;
        
        if (!$externalId) {
            $externalId = md5($titleText . ($item['updated_date'] ?? ''));
        }

        return [
            'type' => $type,
            'external_id' => (string) $externalId,
            'title' => $titleText ?: strip_tags($titleHtml),
            'url' => $titleUrl,
            'updated_date' => trim((string) ($item['updated_date'] ?? '')),
            'publisher' => trim((string) ($item['publisher'] ?? '')),
            'files_count' => $this->countFilesInHtml((string) ($item['notice_files'] ?? '')),
            'raw_data' => $item,
            'fetched_at' => now(),
        ];
    }

    private function extractFirstHtmlLink(string $html): array
    {
        if (preg_match('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches)) {
            $url = html_entity_decode(trim($matches[1]));
            $text = html_entity_decode(strip_tags($matches[2]));
            return [$url, $text];
        }

        return [null, strip_tags($html)];
    }

    private function countFilesInHtml(string $html): int
    {
        return substr_count($html, '<a ');
    }
}
