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
     * Activate a session. Deactivates all others first.
     * Only ONE session can be active at a time.
     */
    public function activate(AcademicSession $session): void
    {
        DB::transaction(function () use ($session) {
            // Deactivate all other sessions
            AcademicSession::where('id', '!=', $session->id)
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
        $result = DB::transaction(function () use ($session) {
            $session->update([
                'is_active' => false,
                'status' => 'ended',
                'is_locked' => true,
                'ended_at' => now(),
            ]);

            AcademicSession::clearSessionCache();
            AuditLog::log('session_ended', $session);

            // Trigger alumni automation
            return $this->alumniService->convertFinalYearStudents($session);
        });

        return $result;
    }
}
