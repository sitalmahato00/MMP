# MMP Academic Management Portal - Mobile App Development Blueprint

**Version:** 1.0  
**Date:** April 28, 2026  
**Project:** Android Mobile Application for College Management System  
**Backend:** Laravel 11 + MySQL + Laravel Sanctum  
**Mobile Platform:** Android (Kotlin)

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [System Architecture Overview](#system-architecture-overview)
3. [Backend Analysis](#backend-analysis)
4. [Database Schema](#database-schema)
5. [Authentication & Security](#authentication--security)
6. [Role-Based Access Control](#role-based-access-control)
7. [API Endpoints Specification](#api-endpoints-specification)
8. [Mobile App Architecture](#mobile-app-architecture)
9. [Screen Specifications](#screen-specifications)
10. [Data Flow Diagrams](#data-flow-diagrams)
11. [Implementation Roadmap](#implementation-roadmap)
12. [Code Examples](#code-examples)

---

## 1. Executive Summary

### Project Overview
This document provides a complete blueprint for developing an Android mobile application for the MMP Academic Management Portal. The system currently operates as a Laravel-based web application serving students, teachers, parents, HODs, and alumni.

### Key Features
- **Multi-role Support**: Student, Teacher, Parent, HOD, Alumni
- **OTP-based Authentication**: Phone number + OTP (2FA ready)
- **Real-time Data**: Attendance, Marks, Notices, Assignments
- **Offline Capability**: Local caching with sync
- **Push Notifications**: Firebase Cloud Messaging
- **File Management**: Downloads, Assignments, Notices

### Technology Stack

**Backend (Existing)**
- Laravel 11.x
- MySQL 8.0+
- Laravel Sanctum (API Authentication)
- Spatie Laravel Permission (Role Management)

**Mobile App (To Build)**
- Language: Kotlin
- Min SDK: 24 (Android 7.0)
- Target SDK: 34 (Android 14)
- Architecture: MVVM + Clean Architecture
- Networking: Retrofit 2 + OkHttp
- Local Storage: Room Database
- Dependency Injection: Hilt
- Async: Kotlin Coroutines + Flow
- Image Loading: Coil
- Push Notifications: Firebase Cloud Messaging

---

## 2. System Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     MOBILE APPLICATION                       │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │ Presentation│  │  Domain    │  │    Data    │            │
│  │   Layer     │  │   Layer    │  │   Layer    │            │
│  │  (UI/VM)    │  │ (Use Cases)│  │(Repository)│            │
│  └────────────┘  └────────────┘  └────────────┘            │
│         │               │                │                   │
│         └───────────────┴────────────────┘                   │
│                         │                                    │
└─────────────────────────┼────────────────────────────────────┘
                          │
                          │ HTTPS/REST API
                          │ (Laravel Sanctum Token)
                          │
┌─────────────────────────┼────────────────────────────────────┐
│                    LARAVEL BACKEND                           │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐            │
│  │   Routes   │  │Controllers │  │  Services  │            │
│  │  (API)     │  │  (API)     │  │            │            │
│  └────────────┘  └────────────┘  └────────────┘            │
│         │               │                │                   │
│         └───────────────┴────────────────┘                   │
│                         │                                    │
│                  ┌──────┴──────┐                            │
│                  │   Models    │                            │
│                  │  (Eloquent) │                            │
│                  └──────┬──────┘                            │
│                         │                                    │
└─────────────────────────┼────────────────────────────────────┘
                          │
                  ┌───────┴────────┐
                  │  MySQL Database │
                  └────────────────┘
```

### Communication Flow

```
Mobile App → API Request (Bearer Token) → Laravel Middleware (auth:sanctum)
→ Controller → Service → Model → Database → Response (JSON) → Mobile App
```

---

## 3. Backend Analysis

### 3.1 Existing Modules

Based on codebase analysis, the system has the following modules:

| Module | Description | Models Involved |
|--------|-------------|-----------------|
| **Authentication** | OTP-based login, 2FA | User, Otp |
| **User Management** | Multi-role users | User, Student, Teacher, ParentModel, Alumni |
| **Academic Sessions** | Year/semester management | AcademicSession, AcademicSessionSemester |
| **Departments** | Department structure | Department, Program |
| **Students** | Student records | Student, Mark, Attendance, Assignment |
| **Teachers** | Teacher management | Teacher, Subject, AttendanceSession |
| **Attendance** | Daily attendance tracking | Attendance, AttendanceSession |
| **Marks/Exams** | Exam results, CTEVT marks | Mark, Exam, ExamSubjectMarkingScheme |
| **Notices** | Announcements, news | Notice, NoticeAttachment |
| **Assignments** | Homework, submissions | Assignment, AssignmentSubmission |
| **Downloads** | Study materials | Download |
| **Timetable** | Class schedules | TimetableSlot |
| **Communications** | Internal messaging | Communication |
| **Media** | Gallery, photos | Media |
| **Audit Logs** | Activity tracking | AuditLog |

### 3.2 Core Models & Relationships

#### User Model
```php
// Relationships
- hasOne: Student, Teacher, ParentModel, Alumni
- hasMany: AuditLog, Notice (created), Communication (sent/received)
- hasRole: Uses Spatie Permission (principal, hod, teacher, student, parent, alumni)
```

#### Student Model
```php
// Relationships
- belongsTo: User, Department, Program, AcademicSession
- belongsToMany: ParentModel (pivot: parent_student)
- hasMany: Mark, Attendance, AssignmentSubmission
- hasOne: Alumni

// Key Fields
- student_no, registration_number, current_semester, section, batch
- guardian_name, guardian_phone, blood_group
- status: active, inactive, graduated, dropped, suspended
```

#### Teacher Model
```php
// Relationships
- belongsTo: User, Department
- belongsToMany: Subject (pivot: subject_teacher with academic_session_id, section, role)
- hasMany: AttendanceSession, Mark, Assignment, TimetableSlot

// Key Fields
- employee_id, designation, qualification, specialization
- join_date, employment_type, is_active
```

#### Attendance Model
```php
// Relationships
- belongsTo: AttendanceSession, Student

// Key Fields
- status: present, absent, late
- remarks
```

#### Mark Model
```php
// Relationships
- belongsTo: Exam, Student, Subject, Program, Teacher

// Key Fields (CTEVT Structure)
- internal_theory_marks, external_theory_marks
- internal_practical_marks, external_practical_marks
- assessment_full_marks, assessment_obtained_marks
- status: draft, submitted, approved, published
- is_absent, is_withheld, is_delayed
```

#### Notice Model
```php
// Relationships
- belongsTo: User (created_by), Department, Program
- hasMany: NoticeAttachment

// Key Fields
- title, slug, content, attachment
- type: general, department, program, teachers, exam, news, event, ctevt
- is_published, published_at
```

### 3.3 Existing API Endpoints

Currently implemented in `routes/api.php`:

```php
// Authentication
POST   /api/auth/send-otp          // Send OTP to phone
POST   /api/auth/verify-otp        // Verify OTP and get token
POST   /api/auth/login             // Login with password or OTP
POST   /api/auth/logout            // Logout (revoke token)

// Public API
GET    /api/v1/public/homepage
GET    /api/v1/public/notices
GET    /api/v1/public/departments
GET    /api/v1/public/departments/{slug}
GET    /api/v1/public/alumni
GET    /api/v1/public/downloads
GET    /api/v1/public/facilities
GET    /api/v1/public/staff
GET    /api/v1/public/leadership
GET    /api/v1/public/site-settings

// Authenticated
GET    /api/v1/user                // Get authenticated user
GET    /api/v1/subjects/{subject}/students  // Get students in subject
```

### 3.4 Missing API Endpoints (Need to Create)

The following endpoints need to be created for mobile app:

**Student APIs**
```
GET    /api/v1/student/dashboard
GET    /api/v1/student/attendance
GET    /api/v1/student/attendance/{id}
GET    /api/v1/student/marks
GET    /api/v1/student/marks/{exam_id}
GET    /api/v1/student/subjects
GET    /api/v1/student/assignments
GET    /api/v1/student/assignments/{id}
POST   /api/v1/student/assignments/{id}/submit
GET    /api/v1/student/timetable
GET    /api/v1/student/downloads
GET    /api/v1/student/notices
GET    /api/v1/student/notices/{id}
GET    /api/v1/student/profile
PATCH  /api/v1/student/profile
PATCH  /api/v1/student/password
PATCH  /api/v1/student/settings
```

**Teacher APIs**
```
GET    /api/v1/teacher/dashboard
GET    /api/v1/teacher/classes
GET    /api/v1/teacher/attendance
POST   /api/v1/teacher/attendance
GET    /api/v1/teacher/students
GET    /api/v1/teacher/students/{id}
GET    /api/v1/teacher/exams
POST   /api/v1/teacher/marks
GET    /api/v1/teacher/assignments
POST   /api/v1/teacher/assignments
GET    /api/v1/teacher/timetable
GET    /api/v1/teacher/notices
```

**Parent APIs**
```
GET    /api/v1/parent/dashboard
GET    /api/v1/parent/children
GET    /api/v1/parent/children/{student_id}/attendance
GET    /api/v1/parent/children/{student_id}/marks
GET    /api/v1/parent/children/{student_id}/assignments
GET    /api/v1/parent/children/{student_id}/subjects
GET    /api/v1/parent/notices
```

**HOD APIs**
```
GET    /api/v1/hod/dashboard
GET    /api/v1/hod/students
GET    /api/v1/hod/teachers
GET    /api/v1/hod/attendance
GET    /api/v1/hod/exams
GET    /api/v1/hod/reports
POST   /api/v1/hod/notices
```

**Common APIs**
```
GET    /api/v1/notifications
POST   /api/v1/notifications/read-all
DELETE /api/v1/notifications/{id}
GET    /api/v1/settings
PATCH  /api/v1/settings/profile
PATCH  /api/v1/settings/password
PATCH  /api/v1/settings/notifications
```

---

## 4. Database Schema

### 4.1 Core Tables

#### users
```sql
id, name, email, phone, avatar, gender, dob, address
is_active, password, preferences, notification_preferences
two_factor_enabled, two_factor_method
email_verified_at, remember_token
created_at, updated_at, deleted_at
```

#### students
```sql
id, user_id, department_id, program_id, academic_session_id
student_no, registration_number, current_semester, section, batch
admission_date, guardian_name, guardian_phone, blood_group
status (active, inactive, graduated, dropped, suspended)
is_active, is_archived, roll_number
created_at, updated_at, deleted_at
```

#### teachers
```sql
id, user_id, department_id, employee_id, designation
qualification, specialization, join_date, employment_type
is_active
created_at, updated_at, deleted_at
```

#### attendances
```sql
id, attendance_session_id, student_id
status (present, absent, late), remarks
created_at, updated_at
```

#### attendance_sessions
```sql
id, teacher_id, subject_id, date, period, section
remarks
created_at, updated_at
```

#### marks
```sql
id, exam_id, student_id, subject_id, program_id, teacher_id, semester
internal_theory_marks, external_theory_marks
internal_practical_marks, external_practical_marks
assessment_attendance_percent, assessment_full_marks
assessment_pass_marks, assessment_obtained_marks
marks_obtained, total_marks, pass_marks
status (draft, submitted, approved, published)
is_absent, is_withheld, is_delayed
remarks
created_at, updated_at
```

#### notices
```sql
id, title, slug, content, attachment
type (general, department, program, teachers, exam, news, event, ctevt)
department_id, program_id, semester, created_by
is_published, published_at
created_at, updated_at, deleted_at
```

#### assignments
```sql
id, teacher_id, subject_id, program_id, semester, section
title, description, attachment, due_date, max_marks
is_published
created_at, updated_at
```

#### assignment_submissions
```sql
id, assignment_id, student_id
submission_file, submission_text, submitted_at
marks_obtained, feedback, graded_at, graded_by
status (pending, submitted, graded, late)
created_at, updated_at
```

### 4.2 Entity Relationship Diagram

```
User ──┬── Student ──┬── Attendance
       │             ├── Mark
       │             ├── AssignmentSubmission
       │             └── ParentModel (many-to-many)
       │
       ├── Teacher ──┬── AttendanceSession
       │             ├── Assignment
       │             ├── Mark
       │             └── Subject (many-to-many)
       │
       ├── ParentModel ── Student (many-to-many)
       │
       └── Alumni

Department ──┬── Student
             ├── Teacher
             ├── Program
             └── Notice

Program ──┬── Student
          ├── Subject
          ├── Assignment
          └── Mark

Subject ──┬── Teacher (many-to-many)
          ├── AttendanceSession
          ├── Assignment
          └── Mark

Exam ──── Mark

Notice ──── NoticeAttachment
```

---

## 5. Authentication & Security

### 5.1 Authentication Flow

#### Current Implementation (OTP-based)

```
1. User enters phone number
2. Backend validates phone exists and user is active
3. Backend generates 6-digit OTP, stores hashed in database
4. OTP sent via SMS (currently logs in debug mode)
5. User enters OTP
6. Backend verifies OTP (max 5 attempts, 1-minute expiry)
7. Backend issues Laravel Sanctum token
8. Mobile app stores token securely
9. All subsequent requests include: Authorization: Bearer {token}
```

#### OTP Service Details

**File**: `app/Services/OtpService.php`

```php
- OTP Expiry: 1 minute
- Max Attempts: 5
- Rate Limiting: 1 OTP per minute per phone
- Storage: Hashed in `otps` table
- Delivery: SMS (TODO: integrate Twilio/SNS)
```

### 5.2 Laravel Sanctum Token Management

**Token Creation**
```php
$token = $user->createToken('mobile-app', ['*'])->plainTextToken;
```

**Token Format**
```
{token_id}|{plain_text_token}
Example: 1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890
```

**Token Storage in Mobile**
- Use Android EncryptedSharedPreferences
- Never log tokens
- Clear on logout

**Token Validation**
```php
// Middleware: auth:sanctum
// Automatically validates token and loads user
```

### 5.3 API Security Headers

All API requests must include:

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

### 5.4 Rate Limiting

**Current Limits** (from `routes/api.php`):
```php
- OTP endpoints: 3 requests per minute
- Public API: Custom throttle (check config)
- Authenticated: 60 requests per minute (default)
```

### 5.5 Security Best Practices

**Backend**
- All API routes protected with `auth:sanctum` middleware
- Role-based access via Spatie Permission
- Input validation via Form Requests
- SQL injection prevention (Eloquent ORM)
- XSS prevention (Laravel escaping)
- CSRF not needed for API (stateless)

**Mobile App**
- Store tokens in EncryptedSharedPreferences
- Use HTTPS only (no HTTP fallback)
- Certificate pinning (optional but recommended)
- Obfuscate code with ProGuard/R8
- No sensitive data in logs
- Biometric authentication (optional)

---

## 6. Role-Based Access Control

### 6.1 Roles & Permissions

The system uses **Spatie Laravel Permission** package.

**Available Roles**:
1. `principal` - Full system access
2. `hod` - Head of Department
3. `teacher` - Teaching staff
4. `student` - Students
5. `parent` - Parents/Guardians
6. `alumni` - Alumni members

### 6.2 Role Detection

**Backend** (`app/Models/User.php`):
```php
$user->isPrincipal()  // bool
$user->isHod()        // bool
$user->isTeacher()    // bool
$user->isStudent()    // bool
$user->isParent()     // bool
$user->isAlumni()     // bool
$user->primaryRole()  // string|null
```

**API Response** (after login):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "9876543210",
      "avatar": "https://...",
      "roles": ["student"],
      "permissions": []
    },
    "token": "1|AbCdEf...",
    "token_type": "Bearer"
  }
}
```

### 6.3 Role-Based UI/UX

**Mobile App Navigation** (based on role):

```kotlin
when (userRole) {
    "student" -> {
        // Show: Dashboard, Attendance, Marks, Subjects, 
        //       Assignments, Timetable, Downloads, Notices, Profile
    }
    "teacher" -> {
        // Show: Dashboard, Classes, Attendance Entry, Students,
        //       Exams/Marks, Assignments, Timetable, Notices, Profile
    }
    "parent" -> {
        // Show: Dashboard, Children, Attendance, Results,
        //       Assignments, Subjects, Notices, Profile
    }
    "hod" -> {
        // Show: Dashboard, Students, Teachers, Attendance,
        //       Exams, Reports, Notices, Profile
    }
    "alumni" -> {
        // Show: Dashboard, Profile, Achievements, Projects,
        //       Career, Notices
    }
}
```

### 6.4 API Access Control

**Middleware Protection**:
```php
// In routes/api.php
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);
});

Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard']);
});
```

**Mobile App Handling**:
```kotlin
// If API returns 403 Forbidden
if (response.code() == 403) {
    // Show "Access Denied" message
    // Log out user if role mismatch
}
```

---


## 7. API Endpoints Specification

### 7.1 Standard API Response Format

All APIs should return consistent JSON structure:

**Success Response**:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data here
  },
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 15
  }
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### 7.2 Authentication APIs

#### POST /api/auth/send-otp
**Purpose**: Send OTP to phone number

**Request**:
```json
{
  "phone": "9876543210"
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "OTP sent successfully",
  "expires_in": 60,
  "otp": "123456"  // Only in debug mode
}
```

**Errors**:
- 404: No account found with this phone number
- 403: Account is inactive
- 429: Rate limit exceeded (wait before requesting another OTP)

---

#### POST /api/auth/verify-otp
**Purpose**: Verify OTP and get authentication token

**Request**:
```json
{
  "phone": "9876543210",
  "otp": "123456"
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "9876543210",
      "avatar": "https://example.com/storage/avatars/user1.jpg",
      "roles": ["student"],
      "student": {
        "id": 1,
        "student_no": "STU001",
        "current_semester": 3,
        "department": {
          "id": 1,
          "name": "Computer Engineering"
        },
        "program": {
          "id": 1,
          "name": "Diploma in Computer Engineering"
        }
      }
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz",
    "token_type": "Bearer"
  }
}
```

**Errors**:
- 400: Invalid OTP or OTP expired
- 404: User not found
- 403: Account inactive

---

#### POST /api/auth/login
**Purpose**: Login with password or OTP

**Request**:
```json
{
  "phone": "9876543210",
  "password": "password123",  // Optional
  "otp": "123456"             // Optional (if password not provided)
}
```

**Response**: Same as verify-otp

---

#### POST /api/auth/logout
**Purpose**: Logout and revoke current token

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### 7.3 Student APIs

#### GET /api/v1/student/dashboard
**Purpose**: Get student dashboard data

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_no": "STU001",
      "current_semester": 3,
      "section": "A",
      "department": "Computer Engineering",
      "program": "Diploma in Computer Engineering"
    },
    "kpi": {
      "attendance_rate": 85.5,
      "pending_assignments": 3,
      "percentage_rate": 78.2,
      "published_assessments": 5,
      "total_subjects": 6
    },
    "recent_notices": [
      {
        "id": 1,
        "title": "Mid-term Exam Schedule",
        "type": "exam",
        "published_at": "2026-04-20T10:00:00Z"
      }
    ],
    "upcoming_assignments": [
      {
        "id": 1,
        "title": "Data Structure Assignment",
        "subject": "Data Structure and Algorithm",
        "due_date": "2026-05-05",
        "max_marks": 20
      }
    ],
    "attendance_chart": [
      {
        "date": "2026-04-22",
        "rate": 100.0
      },
      {
        "date": "2026-04-23",
        "rate": 75.0
      }
    ]
  }
}
```

---

#### GET /api/v1/student/attendance
**Purpose**: Get attendance records with filters

**Query Parameters**:
- `subject_id` (optional): Filter by subject
- `from_date` (optional): Start date (YYYY-MM-DD)
- `to_date` (optional): End date (YYYY-MM-DD)

**Response** (200):
```json
{
  "success": true,
  "data": {
    "summary": {
      "total": 120,
      "present": 102,
      "absent": 15,
      "late": 3,
      "rate": 85.0
    },
    "subject_wise": [
      {
        "subject": {
          "id": 1,
          "name": "Data Structure and Algorithm",
          "code": "CT301"
        },
        "total": 20,
        "present": 18,
        "absent": 2,
        "late": 0,
        "rate": 90.0
      }
    ],
    "records": [
      {
        "id": 1,
        "date": "2026-04-28",
        "subject": "Data Structure and Algorithm",
        "teacher": "Prof. Ram Kumar",
        "status": "present",
        "period": "1st Period",
        "remarks": null
      }
    ]
  }
}
```

---

#### GET /api/v1/student/marks
**Purpose**: Get marks/results

**Query Parameters**:
- `exam_type` (optional): Filter by exam type
- `category` (optional): Filter by category
- `semester` (optional): Filter by semester

**Response** (200):
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_assessments": 5,
      "average_percentage": 78.2,
      "total_subjects": 6,
      "pass_percentage": 100.0
    },
    "assessments": [
      {
        "exam": {
          "id": 1,
          "name": "First Terminal Exam",
          "type": "terminal",
          "category": "terminal_exam",
          "published_at": "2026-04-15T10:00:00Z"
        },
        "marks_count": 6,
        "total_obtained": 420.0,
        "total_full": 600.0,
        "percentage": 70.0,
        "passed": true,
        "division": "First Division"
      }
    ]
  }
}
```

---

#### GET /api/v1/student/marks/{exam_id}
**Purpose**: Get detailed marksheet for specific exam

**Response** (200):
```json
{
  "success": true,
  "data": {
    "exam": {
      "id": 1,
      "name": "First Terminal Exam",
      "type": "terminal",
      "semester": 3
    },
    "student": {
      "name": "John Doe",
      "student_no": "STU001",
      "roll_number": "001",
      "program": "Diploma in Computer Engineering"
    },
    "marks": [
      {
        "subject": {
          "id": 1,
          "name": "Data Structure and Algorithm",
          "code": "CT301"
        },
        "internal_theory": 18.0,
        "external_theory": 52.0,
        "internal_practical": 20.0,
        "external_practical": 48.0,
        "total_obtained": 138.0,
        "total_full": 200.0,
        "percentage": 69.0,
        "passed": true,
        "grade": "First Division"
      }
    ],
    "summary": {
      "total_obtained": 420.0,
      "total_full": 600.0,
      "percentage": 70.0,
      "all_passed": true,
      "division": "First Division"
    }
  }
}
```

---

#### GET /api/v1/student/subjects
**Purpose**: Get enrolled subjects

**Response** (200):
```json
{
  "success": true,
  "data": {
    "current_semester": 3,
    "subjects": [
      {
        "id": 1,
        "name": "Data Structure and Algorithm",
        "code": "CT301",
        "credit_hours": 4,
        "type": "theory_practical",
        "teachers": [
          {
            "id": 1,
            "name": "Prof. Ram Kumar",
            "role": "Theory Teacher"
          }
        ],
        "full_marks": 200,
        "pass_marks": 80
      }
    ]
  }
}
```

---

#### GET /api/v1/student/assignments
**Purpose**: Get assignments list

**Query Parameters**:
- `status` (optional): pending, submitted, graded
- `subject_id` (optional): Filter by subject

**Response** (200):
```json
{
  "success": true,
  "data": {
    "assignments": [
      {
        "id": 1,
        "title": "Implement Binary Search Tree",
        "subject": {
          "id": 1,
          "name": "Data Structure and Algorithm"
        },
        "teacher": "Prof. Ram Kumar",
        "due_date": "2026-05-05",
        "max_marks": 20,
        "description": "Implement BST with insert, delete, search operations",
        "attachment": "https://example.com/storage/assignments/assignment1.pdf",
        "submission": {
          "id": 1,
          "submitted_at": "2026-05-03T14:30:00Z",
          "status": "graded",
          "marks_obtained": 18,
          "feedback": "Good work!"
        }
      }
    ]
  }
}
```

---

#### POST /api/v1/student/assignments/{id}/submit
**Purpose**: Submit assignment

**Request** (multipart/form-data):
```
submission_text: "I have completed the assignment..."
submission_file: [File]
```

**Response** (200):
```json
{
  "success": true,
  "message": "Assignment submitted successfully",
  "data": {
    "submission": {
      "id": 1,
      "submitted_at": "2026-05-03T14:30:00Z",
      "status": "submitted"
    }
  }
}
```

---

#### GET /api/v1/student/timetable
**Purpose**: Get class timetable

**Response** (200):
```json
{
  "success": true,
  "data": {
    "timetable": [
      {
        "day": "Sunday",
        "slots": [
          {
            "period": "1st Period",
            "time": "07:00 - 07:45",
            "subject": "Data Structure and Algorithm",
            "teacher": "Prof. Ram Kumar",
            "room": "Lab 1"
          }
        ]
      }
    ]
  }
}
```

---

#### GET /api/v1/student/downloads
**Purpose**: Get study materials/downloads

**Query Parameters**:
- `type` (optional): syllabus, notes, question_bank, etc.
- `subject_id` (optional): Filter by subject

**Response** (200):
```json
{
  "success": true,
  "data": {
    "downloads": [
      {
        "id": 1,
        "title": "Data Structure Notes - Unit 1",
        "type": "notes",
        "subject": "Data Structure and Algorithm",
        "file_url": "https://example.com/storage/downloads/ds-unit1.pdf",
        "file_size": 2048576,
        "uploaded_at": "2026-04-01T10:00:00Z"
      }
    ]
  }
}
```

---

#### GET /api/v1/student/notices
**Purpose**: Get notices/announcements

**Query Parameters**:
- `type` (optional): general, exam, news, event
- `page` (optional): Pagination

**Response** (200):
```json
{
  "success": true,
  "data": {
    "notices": [
      {
        "id": 1,
        "title": "Mid-term Exam Schedule",
        "slug": "mid-term-exam-schedule",
        "type": "exam",
        "content": "The mid-term examination will be held from...",
        "published_at": "2026-04-20T10:00:00Z",
        "attachments": [
          {
            "file_name": "exam-schedule.pdf",
            "file_url": "https://example.com/storage/notices/exam-schedule.pdf"
          }
        ]
      }
    ]
  },
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

---

#### GET /api/v1/student/profile
**Purpose**: Get student profile

**Response** (200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "9876543210",
      "avatar": "https://example.com/storage/avatars/user1.jpg",
      "gender": "male",
      "dob": "2005-01-15",
      "address": "Kathmandu, Nepal"
    },
    "student": {
      "student_no": "STU001",
      "registration_number": "REG2023001",
      "current_semester": 3,
      "section": "A",
      "batch": "2023",
      "admission_date": "2023-07-15",
      "guardian_name": "Jane Doe",
      "guardian_phone": "9876543211",
      "blood_group": "O+",
      "status": "active"
    },
    "department": {
      "id": 1,
      "name": "Computer Engineering",
      "code": "CE"
    },
    "program": {
      "id": 1,
      "name": "Diploma in Computer Engineering",
      "total_semesters": 6
    }
  }
}
```

---

### 7.4 Teacher APIs

#### GET /api/v1/teacher/dashboard
**Purpose**: Get teacher dashboard data

**Response** (200):
```json
{
  "success": true,
  "data": {
    "teacher": {
      "id": 1,
      "name": "Prof. Ram Kumar",
      "employee_id": "EMP001",
      "designation": "Lecturer",
      "department": "Computer Engineering"
    },
    "kpi": {
      "total_classes": 5,
      "total_students": 120,
      "pending_marks": 15,
      "pending_assignments": 8
    },
    "today_classes": [
      {
        "subject": "Data Structure and Algorithm",
        "time": "07:00 - 07:45",
        "section": "A",
        "room": "Lab 1"
      }
    ],
    "recent_notices": []
  }
}
```

---

#### GET /api/v1/teacher/classes
**Purpose**: Get assigned classes/subjects

**Response** (200):
```json
{
  "success": true,
  "data": {
    "classes": [
      {
        "subject": {
          "id": 1,
          "name": "Data Structure and Algorithm",
          "code": "CT301"
        },
        "program": "Diploma in Computer Engineering",
        "semester": 3,
        "section": "A",
        "total_students": 40
      }
    ]
  }
}
```

---

#### POST /api/v1/teacher/attendance
**Purpose**: Mark attendance for a class

**Request**:
```json
{
  "subject_id": 1,
  "date": "2026-04-28",
  "period": "1st Period",
  "section": "A",
  "attendance": [
    {
      "student_id": 1,
      "status": "present"
    },
    {
      "student_id": 2,
      "status": "absent",
      "remarks": "Sick leave"
    }
  ]
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Attendance marked successfully",
  "data": {
    "total_students": 40,
    "present": 38,
    "absent": 2
  }
}
```

---

#### GET /api/v1/teacher/students
**Purpose**: Get students list

**Query Parameters**:
- `subject_id` (optional): Filter by subject
- `section` (optional): Filter by section

**Response** (200):
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": 1,
        "name": "John Doe",
        "student_no": "STU001",
        "roll_number": "001",
        "section": "A",
        "phone": "9876543210",
        "email": "john@example.com"
      }
    ]
  }
}
```

---

### 7.5 Parent APIs

#### GET /api/v1/parent/dashboard
**Purpose**: Get parent dashboard

**Response** (200):
```json
{
  "success": true,
  "data": {
    "parent": {
      "id": 1,
      "name": "Jane Doe"
    },
    "children": [
      {
        "id": 1,
        "name": "John Doe",
        "student_no": "STU001",
        "program": "Diploma in Computer Engineering",
        "semester": 3,
        "attendance_rate": 85.5,
        "average_percentage": 78.2
      }
    ],
    "recent_notices": []
  }
}
```

---

#### GET /api/v1/parent/children/{student_id}/attendance
**Purpose**: Get child's attendance

**Response**: Same structure as student attendance API

---

#### GET /api/v1/parent/children/{student_id}/marks
**Purpose**: Get child's marks

**Response**: Same structure as student marks API

---

### 7.6 Common APIs

#### GET /api/v1/notifications
**Purpose**: Get user notifications

**Response** (200):
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "uuid-1",
        "type": "notice",
        "title": "New Notice Published",
        "message": "Mid-term Exam Schedule has been published",
        "data": {
          "notice_id": 1
        },
        "read_at": null,
        "created_at": "2026-04-28T10:00:00Z"
      }
    ],
    "unread_count": 5
  }
}
```

---

#### POST /api/v1/notifications/read-all
**Purpose**: Mark all notifications as read

**Response** (200):
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

---

