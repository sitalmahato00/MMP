<?php

namespace App\Services;

use App\Models\{AcademicSession, AuditLog};
use Illuminate\Support\Facades\DB;

/**
 * SessionService — Manages academic session lifecycle.
 * Central engine controlling the entire system state.
 */
class SessionService
{
    public function __construct(private AlumniService $alumniService) {}

    /**
     * End the current active session and activate the next one.
     */
    public function switchTo(AcademicSession $session): array
    {
        $currentSession = AcademicSession::where('is_active', true)->first();

        if ($currentSession && $currentSession->id === $session->id) {
            return ['converted' => 0, 'failed' => 0, 'errors' => []];
        }

        $result = ['converted' => 0, 'failed' => 0, 'errors' => []];

        if ($currentSession) {
            $result = $this->end($currentSession);

            if (($result['failed'] ?? 0) > 0) {
                return $result;
            }
        }

        $this->activate($session);

        return $result;
    }

    /**
     * Activate a session. Deactivates all others first.
     * Only ONE session can be active at a time.
     */
    public function activate(AcademicSession $session): void
    {
        DB::transaction(function () use ($session) {
            // Deactivate all other sessions
            AcademicSession::where('id', '!=', $session->id)
                ->where('status', '!=', 'ended')
                ->update(['is_active' => false, 'status' => 'upcoming']);

            // Activate this session
            $session->update([
                'is_active' => true,
                'status' => 'active',
                'activated_at' => now(),
            ]);

            // Clear cache
            AcademicSession::clearSessionCache();

            AuditLog::log('session_activated', $session);
        });
    }

    /**
     * End a session:
     * 1. Mark session as ended + locked
     * 2. Auto-convert final-year students to alumni
     * 3. Clear session cache
     * 4. Return conversion results
     */
    public function end(AcademicSession $session): array
    {
        try {
            return DB::transaction(function () use ($session) {
                $session->update([
                    'is_active' => false,
                    'status' => 'ended',
                    'is_locked' => true,
                    'ended_at' => now(),
                ]);

                AcademicSession::clearSessionCache();
                AuditLog::log('session_ended', $session);

                $result = $this->alumniService->convertFinalYearStudents($session);

                if (($result['failed'] ?? 0) > 0) {
                    throw new \RuntimeException(implode('; ', $result['errors'] ?? ['Unable to convert final-semester students.']));
                }

                return $result;
            });
        } catch (\Throwable $exception) {
            \Log::error('SessionService::end failed', [
                'session_id' => $session->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'converted' => 0,
                'failed' => 1,
                'errors' => [$exception->getMessage()],
            ];
        }
    }
}
