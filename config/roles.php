<?php

/*
|--------------------------------------------------------------------------
| Role Configuration
|--------------------------------------------------------------------------
|
| Maps each system role to its dashboard route, guard, middleware group,
| and route prefix.
|
| 'role_name' => [
|     'label'      => Human-readable label
|     'guard'      => Auth guard
|     'middleware' => Middleware alias(es)
|     'prefix'     => Route prefix
|     'dashboard'  => Named route for role's home page
|     'active'     => Whether the role's routes are registered
| ]
|
*/

return [

    'principal' => [
        'label'      => 'Principal',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:principal'],
        'prefix'     => 'admin',
        'dashboard'  => 'admin.dashboard',
        'active'     => true,
    ],

    'hod' => [
        'label'      => 'Head of Department',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:hod'],
        'prefix'     => 'hod',
        'dashboard'  => 'hod.dashboard',
        'active'     => true,
    ],

    'teacher' => [
        'label'      => 'Teacher',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:teacher'],
        'prefix'     => 'teacher',
        'dashboard'  => 'teacher.dashboard',
        'active'     => true,
    ],

    'student' => [
        'label'      => 'Student',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:student'],
        'prefix'     => 'student',
        'dashboard'  => 'student.dashboard',
        'active'     => true,
    ],

    'parent' => [
        'label'      => 'Parent / Guardian',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:parent'],
        'prefix'     => 'parent',
        'dashboard'  => 'parent.dashboard',
        'active'     => true,
    ],

    'alumni' => [
        'label'      => 'Alumni',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:alumni'],
        'prefix'     => 'alumni',
        'dashboard'  => 'alumni.dashboard',
        'active'     => true,
    ],

    'staff' => [
        'label'      => 'Staff',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:staff'],
        'prefix'     => 'staff',
        'dashboard'  => 'staff.dashboard',
        'active'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Future Roles (not yet active — set 'active' => true when ready)
    |--------------------------------------------------------------------------
    */

    'accountant' => [
        'label'      => 'Accountant',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:accountant'],
        'prefix'     => 'accounts',
        'dashboard'  => 'accounts.dashboard',
        'active'     => false,
    ],

    'librarian' => [
        'label'      => 'Librarian',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:librarian'],
        'prefix'     => 'library',
        'dashboard'  => 'library.dashboard',
        'active'     => false,
    ],

    'hostel_warden' => [
        'label'      => 'Hostel Warden',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:hostel_warden'],
        'prefix'     => 'hostel',
        'dashboard'  => 'hostel.dashboard',
        'active'     => false,
    ],

    'inventory_manager' => [
        'label'      => 'Inventory Manager',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:inventory_manager'],
        'prefix'     => 'inventory',
        'dashboard'  => 'inventory.dashboard',
        'active'     => false,
    ],

    'hr_manager' => [
        'label'      => 'HR Manager',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:hr_manager'],
        'prefix'     => 'hr',
        'dashboard'  => 'hr.dashboard',
        'active'     => false,
    ],

    'finance_officer' => [
        'label'      => 'Finance Officer',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:finance_officer'],
        'prefix'     => 'finance',
        'dashboard'  => 'finance.dashboard',
        'active'     => false,
    ],

    'exam_controller' => [
        'label'      => 'Exam Controller',
        'guard'      => 'web',
        'middleware' => ['auth', 'role:exam_controller'],
        'prefix'     => 'exam-control',
        'dashboard'  => 'exam_control.dashboard',
        'active'     => false,
    ],

];
