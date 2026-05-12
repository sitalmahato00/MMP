<?php

/*
|--------------------------------------------------------------------------
| ERP Configuration
|--------------------------------------------------------------------------
|
| Central configuration file for the MMP ERP system.
|
| This file controls which modules are active, global ERP settings,
| feature flags, and institution details used throughout the application.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Institution Details
    |--------------------------------------------------------------------------
    */
    'institution' => [
        'name'     => env('ERP_INSTITUTION_NAME', 'My College'),
        'short'    => env('ERP_INSTITUTION_SHORT', 'MC'),
        'address'  => env('ERP_INSTITUTION_ADDRESS', ''),
        'phone'    => env('ERP_INSTITUTION_PHONE', ''),
        'email'    => env('ERP_INSTITUTION_EMAIL', ''),
        'website'  => env('ERP_INSTITUTION_WEBSITE', ''),
        'timezone' => env('APP_TIMEZONE', 'Asia/Kathmandu'),
        'locale'   => env('APP_LOCALE', 'en'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Registry
    |--------------------------------------------------------------------------
    | Lists every ERP module and whether it is currently enabled.
    | Future modules start as disabled; flip to true when implemented.
    |
    | 'module_key' => [
    |     'enabled' => bool,
    |     'label'   => string,
    | ]
    */
    'modules' => [
        'user'         => ['enabled' => true,  'label' => 'User Management'],
        'role'         => ['enabled' => true,  'label' => 'Role & Permission'],
        'student'      => ['enabled' => true,  'label' => 'Student'],
        'teacher'      => ['enabled' => true,  'label' => 'Teacher'],
        'staff'        => ['enabled' => true,  'label' => 'Staff'],
        'parent'       => ['enabled' => true,  'label' => 'Parent'],
        'hod'          => ['enabled' => true,  'label' => 'HOD'],
        'department'   => ['enabled' => true,  'label' => 'Department'],
        'alumni'       => ['enabled' => true,  'label' => 'Alumni'],
        'academic'     => ['enabled' => true,  'label' => 'Academic Session'],
        'routine'      => ['enabled' => true,  'label' => 'Timetable / Routine'],
        'attendance'   => ['enabled' => true,  'label' => 'Attendance'],
        'exam'         => ['enabled' => true,  'label' => 'Exam'],
        'result'       => ['enabled' => true,  'label' => 'Result / Marks'],
        'notice'       => ['enabled' => true,  'label' => 'Notice'],
        'dashboard'    => ['enabled' => true,  'label' => 'Dashboard'],
        'settings'     => ['enabled' => true,  'label' => 'Settings'],
        'audit_log'    => ['enabled' => true,  'label' => 'Audit Log'],
        'cms'          => ['enabled' => true,  'label' => 'CMS'],
        'notification' => ['enabled' => true,  'label' => 'Notifications'],
        'report'       => ['enabled' => true,  'label' => 'Reports'],
        'file_manager' => ['enabled' => true,  'label' => 'File Manager'],

        // Future modules — disabled until implemented
        'accounts'     => ['enabled' => false, 'label' => 'Accounts & Fees'],
        'finance'      => ['enabled' => false, 'label' => 'Finance'],
        'payroll'      => ['enabled' => false, 'label' => 'Payroll'],
        'library'      => ['enabled' => false, 'label' => 'Library'],
        'hostel'       => ['enabled' => false, 'label' => 'Hostel'],
        'inventory'    => ['enabled' => false, 'label' => 'Inventory'],
        'transport'    => ['enabled' => false, 'label' => 'Transport'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Active Roles
    |--------------------------------------------------------------------------
    | Roles that are currently active in the system.
    | Future roles listed below should be enabled together with their module.
    */
    'active_roles' => [
        'principal',
        'hod',
        'teacher',
        'student',
        'parent',
        'alumni',
        'staff',
    ],

    'future_roles' => [
        'accountant',
        'librarian',
        'hostel_warden',
        'inventory_manager',
        'hr_manager',
        'finance_officer',
        'exam_controller',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'nepali_date'         => env('FEATURE_NEPALI_DATE', true),
        'sms_notifications'   => env('FEATURE_SMS', false),
        'email_notifications' => env('FEATURE_EMAIL', true),
        'push_notifications'  => env('FEATURE_PUSH', false),
        'pwa'                 => env('FEATURE_PWA', true),
        'api'                 => env('FEATURE_API', true),
        'export_pdf'          => env('FEATURE_EXPORT_PDF', true),
        'export_excel'        => env('FEATURE_EXPORT_EXCEL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Academic Settings
    |--------------------------------------------------------------------------
    */
    'academic' => [
        'attendance_minimum_percentage' => env('ERP_ATTENDANCE_MIN', 75),
        'pass_mark_percentage'          => env('ERP_PASS_MARK', 40),
        'calendar'                      => env('ERP_CALENDAR', 'nepali'), // 'nepali' or 'english'
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => 15,
        'api_per_page'     => 20,
        'max_per_page'     => 100,
    ],

];
