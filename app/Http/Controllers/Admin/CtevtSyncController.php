<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CtevtNotice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CtevtSyncController extends Controller
{
    private const SYNC_RATE_LIMIT_KEY = 'ctevt_sync_rate_limit';
    private const SYNC_RATE_LIMIT_MINUTES = 5;
    private const SYNC_TIMEOUT_SECONDS = 60;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-notices');
    }

    /**
     * Trigger CTEVT notices sync via external service
     */
    public function sync(Request $request): JsonResponse
    {
        // Check rate limiting
        if ($this->isRateLimited()) {
            return response()->json([
                'success' => false,
                'message' => 'Sync already in progress or recently completed. Please wait ' . self::SYNC_RATE_LIMIT_MINUTES . ' minutes.',
                'retry_after' => self::SYNC_RATE_LIMIT_MINUTES * 60,
            ], 429);
        }

        // Set rate limit
        $this->setRateLimit();

        try {
            $result = $this->callExternalSyncService($request);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Sync failed',
                    'error' => $result['error'] ?? null,
                ], 400);
            }

            // Clear CTEVT notice caches
            Cache::forget('public:ctevt_notices:general:6');
            Cache::forget('public:ctevt_notices:result:6');

            return response()->json([
                'success' => true,
                'message' => 'CTEVT notices synced successfully',
                'data' => [
                    'notices_added' => $result['notices_added'] ?? 0,
                    'notices_updated' => $result['notices_updated'] ?? 0,
                    'notices_total' => $result['notices_total'] ?? 0,
                    'synced_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('CTEVT sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync service error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync status and last sync time
     */
    public function status(): JsonResponse
    {
        $lastSync = \App\Models\CtevtSyncLog::latest()->first();
        $generalCount = CtevtNotice::where('type', 'general')->count();
        $resultCount = CtevtNotice::where('type', 'result')->count();

        return response()->json([
            'last_sync' => $lastSync ? [
                'status' => $lastSync->status,
                'synced_at' => $lastSync->created_at->toIso8601String(),
                'notices_added' => $lastSync->notices_added,
                'notices_updated' => $lastSync->notices_updated,
                'notices_total' => $lastSync->notices_total,
                'error_message' => $lastSync->error_message,
            ] : null,
            'current_counts' => [
                'general' => $generalCount,
                'result' => $resultCount,
                'total' => $generalCount + $resultCount,
            ],
            'is_rate_limited' => $this->isRateLimited(),
        ]);
    }

    /**
     * Call external sync service
     */
    private function callExternalSyncService(Request $request): array
    {
        $externalServiceUrl = config('services.ctevt_sync.external_url');
        $apiToken = config('services.ctevt_sync.api_token');

        if (!$externalServiceUrl || !$apiToken) {
            throw new \Exception('External sync service not configured');
        }

        try {
            $response = Http::timeout(self::SYNC_TIMEOUT_SECONDS)
                ->retry(2, 1000)
                ->withToken($apiToken)
                ->post($externalServiceUrl, [
                    'action' => 'sync_notices',
                    'timestamp' => now()->timestamp,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'External service returned error',
                    'error' => "HTTP {$response->status()}",
                ];
            }

            $data = $response->json();

            // Log sync result
            \App\Models\CtevtSyncLog::create([
                'status' => $data['success'] ? 'success' : 'failed',
                'notices_added' => $data['notices_added'] ?? 0,
                'notices_updated' => $data['notices_updated'] ?? 0,
                'notices_total' => $data['notices_total'] ?? 0,
                'error_message' => $data['error'] ?? null,
                'triggered_by' => 'manual',
                'external_service_ip' => $request->ip() ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'metadata' => [
                    'service_url' => $externalServiceUrl,
                    'response_code' => $response->status(),
                ],
            ]);

            return [
                'success' => $data['success'] ?? false,
                'message' => $data['message'] ?? 'Sync completed',
                'notices_added' => $data['notices_added'] ?? 0,
                'notices_updated' => $data['notices_updated'] ?? 0,
                'notices_total' => $data['notices_total'] ?? 0,
            ];
        } catch (\Exception $e) {
            // Log failed sync
            \App\Models\CtevtSyncLog::create([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'triggered_by' => 'manual',
                'external_service_ip' => $request->ip() ?? null,
                'metadata' => [
                    'service_url' => $externalServiceUrl,
                    'error_type' => class_basename($e),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Check if sync is rate limited
     */
    private function isRateLimited(): bool
    {
        return Cache::has(self::SYNC_RATE_LIMIT_KEY);
    }

    /**
     * Set rate limit
     */
    private function setRateLimit(): void
    {
        Cache::put(
            self::SYNC_RATE_LIMIT_KEY,
            true,
            now()->addMinutes(self::SYNC_RATE_LIMIT_MINUTES)
        );
    }
}
