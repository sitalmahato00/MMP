<?php

namespace App\Core\Permissions;

/**
 * Permission constants for the ERP system.
 *
 * Naming convention:  {module}.{action}
 * All strings must match seeded permission names in the database.
 *
 * How to use in a Policy:
 *   $user->can(Permission::STUDENT_VIEW)
 *
 * How to use in a controller:
 *   $this->authorize(Permission::STUDENT_CREATE)
 */
final class Permission
{
    // ── Dashboard ─────────────────────────────────────────
    const DASHBOARD_VIEW = 'dashboard.view';

    // ── User Management ───────────────────────────────────
    const USER_VIEW   = 'user.view';
    const USER_CREATE = 'user.create';
    const USER_EDIT   = 'user.edit';
    const USER_DELETE = 'user.delete';

    // ── Role / Permission Management ──────────────────────
    const ROLE_VIEW   = 'role.view';
    const ROLE_MANAGE = 'role.manage';

    // ── Department ────────────────────────────────────────
    const DEPARTMENT_VIEW   = 'department.view';
    const DEPARTMENT_MANAGE = 'department.manage';

    // ── Student ───────────────────────────────────────────
    const STUDENT_VIEW    = 'student.view';
    const STUDENT_CREATE  = 'student.create';
    const STUDENT_EDIT    = 'student.edit';
    const STUDENT_DELETE  = 'student.delete';
    const STUDENT_PROMOTE = 'student.promote';
    const STUDENT_EXPORT  = 'student.export';

    // ── Teacher ───────────────────────────────────────────
    const TEACHER_VIEW   = 'teacher.view';
    const TEACHER_CREATE = 'teacher.create';
    const TEACHER_EDIT   = 'teacher.edit';
    const TEACHER_DELETE = 'teacher.delete';
    const TEACHER_EXPORT = 'teacher.export';

    // ── Staff ─────────────────────────────────────────────
    const STAFF_VIEW   = 'staff.view';
    const STAFF_CREATE = 'staff.create';
    const STAFF_EDIT   = 'staff.edit';
    const STAFF_DELETE = 'staff.delete';
    const STAFF_EXPORT = 'staff.export';

    // ── Attendance ────────────────────────────────────────
    const ATTENDANCE_VIEW   = 'attendance.view';
    const ATTENDANCE_MARK   = 'attendance.mark';
    const ATTENDANCE_EDIT   = 'attendance.edit';
    const ATTENDANCE_REPORT = 'attendance.report';

    // ── Exam ──────────────────────────────────────────────
    const EXAM_VIEW    = 'exam.view';
    const EXAM_CREATE  = 'exam.create';
    const EXAM_EDIT    = 'exam.edit';
    const EXAM_DELETE  = 'exam.delete';
    const EXAM_PUBLISH = 'exam.publish';

    // ── Result / Marks ────────────────────────────────────
    const RESULT_VIEW   = 'result.view';
    const RESULT_ENTER  = 'result.enter';
    const RESULT_EDIT   = 'result.edit';
    const RESULT_REPORT = 'result.report';

    // ── Notice ────────────────────────────────────────────
    const NOTICE_VIEW    = 'notice.view';
    const NOTICE_CREATE  = 'notice.create';
    const NOTICE_EDIT    = 'notice.edit';
    const NOTICE_DELETE  = 'notice.delete';
    const NOTICE_PUBLISH = 'notice.publish';

    // ── Academic Session ──────────────────────────────────
    const ACADEMIC_VIEW   = 'academic.view';
    const ACADEMIC_MANAGE = 'academic.manage';

    // ── Settings ──────────────────────────────────────────
    const SETTINGS_VIEW   = 'settings.view';
    const SETTINGS_MANAGE = 'settings.manage';

    // ── Audit Log ─────────────────────────────────────────
    const AUDIT_VIEW = 'audit.view';

    // ── Alumni ────────────────────────────────────────────
    const ALUMNI_VIEW   = 'alumni.view';
    const ALUMNI_MANAGE = 'alumni.manage';

    // ── Reports ───────────────────────────────────────────
    const REPORT_VIEW   = 'report.view';
    const REPORT_EXPORT = 'report.export';

    // ── CMS (Site content) ────────────────────────────────
    const CMS_VIEW   = 'cms.view';
    const CMS_MANAGE = 'cms.manage';

    // ── Future: Accounts ─────────────────────────────────
    const ACCOUNTS_VIEW    = 'accounts.view';
    const ACCOUNTS_COLLECT = 'accounts.collect';
    const ACCOUNTS_WAIVE   = 'accounts.waive';
    const ACCOUNTS_REPORT  = 'accounts.report';

    // ── Future: Library ──────────────────────────────────
    const LIBRARY_VIEW   = 'library.view';
    const LIBRARY_MANAGE = 'library.manage';
    const LIBRARY_ISSUE  = 'library.issue';
    const LIBRARY_REPORT = 'library.report';

    // ── Future: Hostel ───────────────────────────────────
    const HOSTEL_VIEW     = 'hostel.view';
    const HOSTEL_MANAGE   = 'hostel.manage';
    const HOSTEL_ALLOCATE = 'hostel.allocate';

    // ── Future: Payroll ──────────────────────────────────
    const PAYROLL_VIEW    = 'payroll.view';
    const PAYROLL_PROCESS = 'payroll.process';
    const PAYROLL_APPROVE = 'payroll.approve';
    const PAYROLL_REPORT  = 'payroll.report';

    // ── Future: Inventory ────────────────────────────────
    const INVENTORY_VIEW   = 'inventory.view';
    const INVENTORY_MANAGE = 'inventory.manage';
    const INVENTORY_REPORT = 'inventory.report';
}
