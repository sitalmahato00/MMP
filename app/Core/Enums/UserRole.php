<?php

namespace App\Core\Enums;

/**
 * UserRole — all roles recognized by the ERP system.
 *
 * Existing roles are active now. Future roles are marked with @future.
 * When adding a new role:
 *   1. Add the case here.
 *   2. Seed it in RolePermissionSeeder.
 *   3. Add its middleware/route group in bootstrap/app.php.
 */
enum UserRole: string
{
    // ── Active roles ──────────────────────────────────────
    case Principal   = 'principal';
    case Hod         = 'hod';
    case Teacher     = 'teacher';
    case Student     = 'student';
    case Parent      = 'parent';
    case Alumni      = 'alumni';
    case Staff       = 'staff';

    // ── Future roles (add when module is ready) ───────────
    case Accountant      = 'accountant';       // @future Accounts module
    case Librarian       = 'librarian';        // @future Library module
    case HostelWarden    = 'hostel_warden';    // @future Hostel module
    case InventoryMgr    = 'inventory_manager'; // @future Inventory module
    case HrManager       = 'hr_manager';       // @future Payroll module
    case FinanceOfficer  = 'finance_officer';  // @future Finance module
    case ExamController  = 'exam_controller';  // @future Exam module

    /** Return the display label for this role. */
    public function label(): string
    {
        return match ($this) {
            self::Principal      => 'Principal / Admin',
            self::Hod            => 'Head of Department',
            self::Teacher        => 'Teacher',
            self::Student        => 'Student',
            self::Parent         => 'Parent',
            self::Alumni         => 'Alumni',
            self::Staff          => 'Staff',
            self::Accountant     => 'Accountant',
            self::Librarian      => 'Librarian',
            self::HostelWarden   => 'Hostel Warden',
            self::InventoryMgr   => 'Inventory Manager',
            self::HrManager      => 'HR Manager',
            self::FinanceOfficer => 'Finance Officer',
            self::ExamController => 'Exam Controller',
        };
    }

    /** Whether this role is currently active in the system. */
    public function isActive(): bool
    {
        return in_array($this, [
            self::Principal,
            self::Hod,
            self::Teacher,
            self::Student,
            self::Parent,
            self::Alumni,
            self::Staff,
        ]);
    }

    public static function activeRoles(): array
    {
        return array_filter(self::cases(), fn ($r) => $r->isActive());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
