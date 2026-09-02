<div align="center">

# 🎓 MMP Academic Management Portal

### Manmohan Memorial Polytechnic — College Management System

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Android](https://img.shields.io/badge/Android-Kotlin-3DDC84?style=for-the-badge&logo=android&logoColor=white)](https://developer.android.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](./LICENSE)

**A comprehensive web-based Academic Management System built for Manmohan Memorial Polytechnic, Budhiganga Rural Municipality-04, Koshi Province**

---

> **Submitted By:**  
> Sital Mahato (Reg. No: DIT-00174-022) · Priti Dev (Reg. No: DIT-00161-023)  
> Prem Singh (Reg. No: DIT-00160-023) · Rabin Sardar (Reg. No: DIT-00163-023)  
>
> **Project Supervisor:** Mohan Tabdar | **Project Coordinator:** Yubraj Chaudhary  
> **Department of Information Technology** | April 2026

</div>

---

## 📑 Table of Contents

1. [Project Overview](#-project-overview)
2. [Key Features](#-key-features)
3. [Technology Stack](#-technology-stack)
4. [System Architecture](#-system-architecture)
5. [User Roles & Portals](#-user-roles--portals)
6. [Laravel Backend](#-laravel-backend)
   - [Application Structure](#application-structure)
   - [Database Schema](#database-schema)
   - [API Endpoints](#api-endpoints)
   - [Authentication & Security](#authentication--security)
   - [Notifications & Email Flows](#notifications--email-flows)
7. [Android Mobile App](#-android-mobile-app)
   - [Build Configuration](#build-configuration)
   - [Retrofit Setup](#retrofit-setup)
   - [API Service Interface](#api-service-interface)
   - [Role-Based Navigation](#role-based-navigation)
8. [Local Development Setup](#-local-development-setup)
9. [Production Deployment](#-production-deployment)
   - [cPanel Deployment](#cpanel-deployment)
   - [Environment Configuration](#environment-configuration)
   - [Security Hardening](#security-hardening)
10. [Testing & Results](#-testing--results)
11. [Project Screenshots & Demo](#-project-screenshots--demo)
12. [Feasibility & Cost Analysis](#-feasibility--cost-analysis)
13. [Future Enhancements](#-future-enhancements)
14. [References & Acknowledgment](#-references--acknowledgment)

---

## 🎯 Project Overview

The **MMP Academic Management Portal** is a comprehensive web-based application that revolutionizes academic and administrative management at Manmohan Memorial Polytechnic. It replaces inefficient paper-based systems with a unified digital platform connecting all stakeholders.

### Problem Statement

Manmohan Memorial Polytechnic faced several challenges in its traditional manual system:

| Problem | Impact |
|---------|--------|
| Manual attendance records | 20-30% of teacher time wasted on admin tasks |
| No real-time result access | Students & parents must physically visit institution |
| Paper notice distribution | Announcements fail to reach all stakeholders |
| Data redundancy across departments | Inconsistencies and update errors |
| No parent engagement system | Parents only informed during report card day |
| Alumni disconnection after graduation | No tracking of achievements or network |

### Solution: MMP Portal

A **six-role unified platform** that automates and streamlines:
- ✅ Attendance tracking with session-based recording
- ✅ Examination management with auto-grade calculation
- ✅ Real-time notice board with role-based targeting
- ✅ Parent monitoring portal with live performance data
- ✅ Alumni engagement with achievement and career tracking
- ✅ REST API backend supporting the Android mobile app

### Live Deployment
- **Web Portal:** `https://mmp.sital00.com.np`
- **API Base URL:** `https://mmp.sital00.com.np/api`
- **API Version:** `v1`

---

## ✨ Key Features

### 🔐 Advanced Two-Factor Authentication (2FA)
- Email-based OTP verification for all user accounts
- Configurable 2FA per user account
- Rate limiting to prevent brute-force attacks
- OTP expiry & resend functionality
- Secure session management with timeout

### 📊 Multi-Role Dashboard System
- **6 customized dashboards** tailored to each user role
- Role-aware login redirect
- Real-time data widgets and counters
- Responsive design across all screen sizes

### 📋 Attendance Management
- Session-based attendance recording
- Quick "Mark All Present/Absent" bulk options
- Real-time attendance percentage display
- Low attendance alert system
- Exportable attendance reports (PDF/Excel)

### 📝 Examination & Marks Management
- Complete exam lifecycle: setup → mark entry → publication
- Auto-grade assignment with marking scheme
- Mark sheet PDF generation (via DomPDF)
- Performance analytics and trend visualization
- CTEVT-compatible result formats

### 📢 Notice Board & Communication
- Rich text editor for notice content
- File attachment support (documents, images)
- Role-based targeting: all users / department / program / semester
- Priority levels: Normal, Important, Urgent
- Expiry date settings with automatic archiving

### 📱 Progressive Web App (PWA)
- Installable on Windows, Mac, Linux, Android, iOS
- Offline access to critical features via Service Worker
- Custom app icon and standalone window mode
- Touch-friendly responsive interface

### 🌐 Public Website
- Dynamic homepage with banners, notices, departments
- Public result checker (without login)
- Department and program pages
- Alumni directory
- CMS-managed custom pages

### 🔗 REST API for Android
- 200+ protected endpoints across 6 user roles
- Bearer token authentication (Laravel Sanctum)
- Consistent JSON response format
- Rate limiting and CORS configured

---

## 🛠 Technology Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Backend Framework** | Laravel | 12.0 | Core web application framework |
| **Language** | PHP | 8.2+ | Server-side scripting |
| **Database** | MySQL | 8.0+ | Primary data storage |
| **Frontend Build** | Vite + Node.js | 18.0+ | Asset bundling & HMR |
| **CSS Framework** | Tailwind CSS + Bootstrap | 3.x / 5.x | Responsive UI design |
| **API Auth** | Laravel Sanctum | 4.x | Token-based API authentication |
| **RBAC** | Spatie Laravel Permission | 6.x | Role-based access control |
| **PDF Generation** | barryvdh/laravel-dompdf | 3.1 | Mark sheets, reports |
| **Nepali Dates** | anuzpandey/laravel-nepali-date | 3.2 | BS/AD date conversion |
| **Web Server** | Apache 2.4+ / Nginx 1.18+ | - | HTTP server |
| **Cache / Queue** | Redis / File | - | Performance & async jobs |
| **Android** | Kotlin + Retrofit 2 | - | Mobile API client |
| **Version Control** | Git | 2.30+ | Source code management |
| **Testing** | PHPUnit | 11.x | Automated testing |

---

## 🏗 System Architecture

The MMP Portal follows a **Three-Tier Architecture**:

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                          │
│   Web Browser (Public + Portal)    Android Mobile App          │
│   HTML5 · Tailwind CSS · Bootstrap · JavaScript · PWA          │
└────────────────────────┬───────────────────────────────────────┘
                         │ HTTP / HTTPS (REST API)
┌────────────────────────▼───────────────────────────────────────┐
│                   APPLICATION LAYER                            │
│            Laravel 12  (MVC + Service Pattern)                 │
│                                                                │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────────────┐  │
│  │ Routes   │ │Controllers│ │ Models   │ │ Services         │  │
│  │ web.php  │ │ Admin    │ │ User     │ │ OtpService       │  │
│  │ admin.php│ │ HOD      │ │ Student  │ │ NotificationSvc  │  │
│  │ hod.php  │ │ Teacher  │ │ Teacher  │ │ AttendanceSvc    │  │
│  │ api.php  │ │ Student  │ │ Alumni   │ │ PublicDataSvc    │  │
│  │   etc.   │ │ Api/*    │ │   etc.   │ │ ExportService    │  │
│  └──────────┘ └──────────┘ └──────────┘ └──────────────────┘  │
│                                                                │
│  ┌────────────────────────────────────────────────────────┐    │
│  │              Middleware Stack                          │    │
│  │  Auth · RoleMiddleware · CSRF · CORS · RateLimiter    │    │
│  └────────────────────────────────────────────────────────┘    │
└────────────────────────┬───────────────────────────────────────┘
                         │ Eloquent ORM / Query Builder
┌────────────────────────▼───────────────────────────────────────┐
│                       DATA LAYER                               │
│              MySQL 8.0+ (InnoDB · ACID Compliant)              │
│              30+ Tables · 3NF Normalized Schema                │
└─────────────────────────────────────────────────────────────────┘
```

### Security Architecture

```
Presentation Layer  →  HTTPS/SSL · CSRF Tokens · XSS Prevention · CSP Headers
Application Layer   →  2FA/OTP · bcrypt Passwords · RBAC · Input Validation · Rate Limiting
Data Layer          →  DB User Permissions · Encrypted Connections · Audit Logs · Backups
```

---

## 👥 User Roles & Portals

| Role | Scope | Key Capabilities |
|------|-------|-----------------|
| **Principal / Admin** | Whole System | Full system config, user management, CMS, audit logs, site branding, exam management |
| **HOD** | Department Only | Department students/teachers/subjects, timetable, attendance, exams, notices, resources |
| **Teacher** | Assigned Subjects | Record attendance, fill marks, manage assignments, timetable, study materials |
| **Student** | Self-Service | View attendance, marks, assignments, notices, timetable, downloads, results |
| **Parent** | Child Monitoring | Monitor child's attendance, marks, assignments, notices, performance trends |
| **Alumni** | Alumni Community | Profile, projects, achievements, career history, alumni notices |
| **Guest** | Public Access | Browse site, check results, apply for admission, download resources |

### Demo Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Principal/Admin | `principal@mmp.edu.np` | `password` |
| HOD | `hod.it@mmp.edu.np` | `password` |
| Teacher | `teacher.it@mmp.edu.np` | `password` |
| Student | `student01@mmp.edu.np` | `password` |
| Parent | `parent01@mmp.edu.np` | `password` |
| Alumni | `alumni01@mmp.edu.np` | `password` |

---

## ⚙️ Laravel Backend

### Application Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # 20+ Admin portal controllers
│   │   ├── HOD/            # Department management controllers
│   │   ├── Teacher/        # Teacher portal controllers
│   │   ├── Student/        # Student portal controllers
│   │   ├── Parent/         # Parent portal controllers
│   │   ├── Alumni/         # Alumni portal controllers
│   │   ├── Api/            # REST API controllers (Android)
│   │   │   ├── AuthController.php
│   │   │   ├── StudentController.php   # 25 action methods
│   │   │   ├── TeacherController.php   # 22 action methods
│   │   │   ├── ParentController.php    # 20 action methods
│   │   │   ├── HodController.php       # 18 action methods
│   │   │   ├── AlumniController.php    # 15 action methods
│   │   │   ├── AdminController.php
│   │   │   └── ManagementController.php
│   │   ├── Public/         # Public website controllers
│   │   └── Auth/           # Login & password-reset controllers
│   └── Middleware/
│       ├── RoleMiddleware.php         # API/Web role enforcement
│       └── SecurityHeaders.php
├── Models/                 # 30+ Eloquent models
├── Notifications/          # Email & database notification classes
├── Services/
│   ├── OtpService.php
│   ├── NotificationService.php
│   ├── AttendanceService.php
│   ├── PublicDataService.php
│   └── ExportService.php
└── Helpers/
    ├── helpers.php
    └── NepaliDateHelper.php

routes/
├── web.php          # Public website routes
├── admin.php        # Admin/Principal portal
├── hod.php          # HOD portal
├── teacher.php      # Teacher portal
├── student.php      # Student portal
├── parent.php       # Parent portal
├── alumni.php       # Alumni portal
└── api.php          # REST API (Android + public)

resources/
├── views/
│   ├── public/      # Public website views
│   ├── admin/       # Admin portal views
│   ├── hod/         # HOD views
│   ├── teacher/     # Teacher views
│   ├── student/     # Student views
│   ├── parent/      # Parent views
│   ├── alumni/      # Alumni views
│   └── emails/      # Styled email templates
```

### Database Schema

The system uses a **MySQL 8.0+ relational database** with **Third Normal Form (3NF)** normalization, consisting of **30+ tables** across 8 functional groups.

#### 1. Identity, Authentication & Access Control
```
users                    personal_access_tokens
password_reset_tokens    roles
sessions                 permissions
notifications            model_has_roles
otps                     model_has_permissions
                         role_has_permissions
```

#### 2. Academic Structure
```
departments              subjects
academic_sessions        subject_teacher (pivot)
academic_session_semesters  timetables
programs                 timetable_slots
```

#### 3. People Tables
```
students                 alumni
teachers                 staff
parents                  executives
parent_student (pivot)
```

#### 4. Attendance & Academic Activity
```
attendance_sessions      exams
attendances              exam_program (pivot)
assignments              marks
assignment_submissions   exam_subject_marking_schemes
                         staff_attendances
```

#### 5. Alumni Portfolio
```
alumni_projects          alumni_employments
alumni_achievements
```

#### 6. Public Content & CMS
```
notices                  banners
notice_attachments       downloads
pages                    media
facilities               site_settings
communications
```

#### 7. Operations & Governance
```
applications             staff_documents
audit_logs               ctevt_sync_logs
```

#### 8. Framework Runtime
```
cache                    jobs
cache_locks              job_batches
                         failed_jobs
```

#### Key Table Structures

**`users` table:**
```
id, name, email, phone, avatar, gender, dob, address,
preferences (JSON), notification_preferences (JSON),
is_active, email_verified_at, password,
two_factor_enabled, two_factor_method,
remember_token, created_at, updated_at
```

**`students` table:**
```
id, user_id (FK→users), department_id (FK), program_id (FK),
academic_session_id (FK), student_no (unique),
registration_number (unique), roll_number,
current_semester, section, batch, admission_date,
guardian_name, guardian_phone, blood_group, status, timestamps
```

**`attendances` table:**
```
id, student_id (FK), attendance_session_id (FK),
status (present/absent/late/excused), remarks, timestamps
UNIQUE: (student_id, attendance_session_id)
```

**`marks` table:**
```
id, student_id (FK), subject_id (FK), exam_id (FK),
teacher_id (FK), theory_marks, practical_marks,
internal_marks, total_marks, grade, remarks,
is_published, timestamps
```

---

### API Endpoints

#### Public Endpoints (No Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/public/homepage` | Homepage payload |
| GET | `/api/v1/public/notices` | Public notices listing |
| GET | `/api/v1/public/departments` | Departments listing |
| GET | `/api/v1/public/departments/{slug}` | Department detail |
| GET | `/api/v1/public/alumni` | Featured alumni |
| GET | `/api/v1/public/downloads` | Public downloads |
| GET | `/api/v1/public/facilities` | Facilities list |
| GET | `/api/v1/public/staff` | Staff listing |
| GET | `/api/v1/public/leadership` | Leadership listing |
| GET | `/api/v1/public/site-settings` | Branding & site config |
| GET | `/api/v1/public/pages/{slug}` | CMS page content |

#### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login — returns Bearer token |
| POST | `/api/auth/logout` | Logout — revokes token |
| POST | `/api/auth/verify-otp` | Verify 2FA OTP |
| POST | `/api/auth/resend-otp` | Resend OTP |
| POST | `/api/auth/refresh-token` | Refresh Bearer token |

#### Student Endpoints (Role: `student`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/student/dashboard` | Student dashboard |
| GET | `/api/v1/student/attendance/summary` | Attendance summary |
| GET | `/api/v1/student/attendance/detail` | Detailed attendance |
| GET | `/api/v1/student/attendance/by-subject/{subject}` | Attendance by subject |
| GET | `/api/v1/student/marks/summary` | Marks summary |
| GET | `/api/v1/student/marks/exam/{exam}` | Marks for exam |
| GET | `/api/v1/student/subjects` | Enrolled subjects |
| GET | `/api/v1/student/assignments` | Assignments list |
| POST | `/api/v1/student/assignments/{id}/submit` | Submit assignment |
| GET | `/api/v1/student/timetable` | Class timetable |
| GET | `/api/v1/student/downloads` | Study materials |
| GET | `/api/v1/student/notices` | Notices |
| GET | `/api/v1/student/profile` | Student profile |
| PUT | `/api/v1/student/profile` | Update profile |

#### Teacher Endpoints (Role: `teacher`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/teacher/dashboard` | Teacher dashboard |
| GET | `/api/v1/teacher/today-schedule` | Today's timetable |
| GET | `/api/v1/teacher/classes` | Assigned classes |
| POST | `/api/v1/teacher/attendance/mark` | Mark attendance |
| POST | `/api/v1/teacher/attendance/bulk-mark` | Bulk mark attendance |
| GET | `/api/v1/teacher/marks/components/{subject}` | Marks components |
| POST | `/api/v1/teacher/marks/submit` | Submit marks |
| GET | `/api/v1/teacher/assignments` | Assignments |
| POST | `/api/v1/teacher/assignments/create` | Create assignment |
| PUT | `/api/v1/teacher/assignments/{id}` | Update assignment |
| DELETE | `/api/v1/teacher/assignments/{id}` | Delete assignment |
| GET | `/api/v1/teacher/students` | Students list |
| GET | `/api/v1/teacher/reports/attendance` | Attendance reports |
| GET | `/api/v1/teacher/reports/marks` | Marks reports |
| GET/PUT | `/api/v1/teacher/profile` | View/update profile |

#### Parent Endpoints (Role: `parent`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/parent/dashboard` | Parent dashboard |
| GET | `/api/v1/parent/children` | Children list |
| GET | `/api/v1/parent/child/{id}/attendance` | Child attendance |
| GET | `/api/v1/parent/child/{id}/marks` | Child marks |
| GET | `/api/v1/parent/child/{id}/assignments` | Child assignments |
| GET | `/api/v1/parent/child/{id}/timetable` | Child timetable |
| GET | `/api/v1/parent/notices` | Notices |
| GET/PUT | `/api/v1/parent/profile` | View/update profile |

#### HOD Endpoints (Role: `hod`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/hod/dashboard` | HOD dashboard |
| GET | `/api/v1/hod/department` | Department overview |
| GET | `/api/v1/hod/students` | Department students |
| GET | `/api/v1/hod/teachers` | Department teachers |
| GET | `/api/v1/hod/subjects` | Department subjects |
| GET | `/api/v1/hod/reports/attendance` | Attendance reports |
| GET | `/api/v1/hod/reports/marks` | Marks reports |
| GET | `/api/v1/hod/sessions` | Academic sessions |

#### Admin Management Endpoints (Role: `admin`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/dashboard` | Admin dashboard |
| GET | `/api/v1/admin/users` | All users |
| GET | `/api/v1/admin/audit-logs` | Audit logs |
| CRUD | `/api/v1/admin/teachers` | Teacher management |
| CRUD | `/api/v1/admin/students` | Student management |
| CRUD | `/api/v1/admin/parents` | Parent management |

#### API Response Format

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 17,
      "name": "Student One",
      "email": "student1@mmp.edu.np",
      "phone": "9810000001",
      "role": "student",
      "panel_type": "student",
      "avatar_url": "https://ui-avatars.com/api/?name=Student+One&background=4f46e5&color=fff"
    },
    "token": "121|2Reh3moGRGqmzlKW5KiBUGLL44Y7Qn...",
    "token_type": "Bearer"
  }
}
```

---

### Authentication & Security

#### Login Flow with 2FA
```
1. POST /api/auth/login   → { email, password }
2. If 2FA enabled:
   → Status 202, OTP sent to email
   → POST /api/auth/verify-otp  → { email, otp }
3. Response: Bearer token + user info + role
4. All protected requests: Authorization: Bearer <token>
```

#### Rate Limits
| Endpoint | Limit |
|----------|-------|
| `login` | 5 attempts / minute / email+IP |
| `apply` | 10 attempts / hour / email+IP |
| `result-check` | 30 requests / minute / IP |
| `public-api` | 120 requests / minute / IP |
| `auth API` | 3 requests / minute |

#### Security Features
- ✅ **Sanctum Bearer Tokens** — API authentication
- ✅ **Spatie RBAC** — Role-based access control
- ✅ **CSRF Protection** — Web portal
- ✅ **XSS Prevention** — Blade auto-escaping
- ✅ **SQL Injection Prevention** — Eloquent ORM parameterization
- ✅ **bcrypt Password Hashing**
- ✅ **Security Headers** — X-Frame-Options, X-Content-Type-Options, etc.
- ✅ **HTTPS/SSL** — Enforced in production
- ✅ **Audit Logging** — Full activity trail in `audit_logs`
- ✅ **Session Management** — Timeout, secure cookies

---

### Notifications & Email Flows

#### Notification Channels
- **In-app** — database `notifications` table with bell UI
- **Email** — styled Blade email templates via SMTP

#### Notification Triggers
| Event | Recipients |
|-------|-----------|
| New user account created | That user (credentials email) |
| Password reset | That user |
| Internal notice published | Targeted role/department/semester |
| Exam results published | Relevant students & parents |
| CTEVT general/result notice | All or department users |

#### Notification Targeting Rules
- All portal users
- Department-specific delivery
- Program-specific delivery  
- Semester-specific delivery
- Role-aware deep-link routing when opened

#### Account Credential Emails
When creating these roles, users receive an email with login credentials:
- HOD, Teacher, Student, Parent, Alumni, Admin-created users
- Students also auto-create linked Parent account — both receive credentials

---

## 📱 Android Mobile App

The Android app consumes the MMP REST API. Here is the complete integration guide.

### Build Configuration

**`app/build.gradle` or `build.gradle.kts`:**

```gradle
buildTypes {
    debug {
        buildConfigField("String", "API_BASE_URL", 
            "\"http://10.0.2.2:8000/api\"")  // Android emulator → local dev
        buildConfigField("String", "API_TIMEOUT", "\"30\"")
    }
    
    release {
        buildConfigField("String", "API_BASE_URL", 
            "\"https://mmp.sital00.com.np/api\"")  // Production
        buildConfigField("String", "API_TIMEOUT", "\"30\"")
        minifyEnabled true
        proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
    }
}
```

> **Note:** For physical device testing on local network, replace `10.0.2.2` with your machine's local IP address (e.g., `192.168.1.x`).

---

### Retrofit Setup

**`RetrofitClient.kt`:**

```kotlin
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import java.util.concurrent.TimeUnit

object RetrofitClient {
    
    private val logging = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }
    
    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor(logging)
        .addInterceptor(AuthInterceptor())
        .connectTimeout(30, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .writeTimeout(30, TimeUnit.SECONDS)
        .retryOnConnectionFailure(true)
        .build()
    
    val retrofit: Retrofit by lazy {
        Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
    }
    
    val apiService: ApiService by lazy {
        retrofit.create(ApiService::class.java)
    }
}
```

**`AuthInterceptor.kt`:**

```kotlin
import okhttp3.Interceptor
import okhttp3.Response
import android.content.SharedPreferences

class AuthInterceptor(private val prefs: SharedPreferences) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val originalRequest = chain.request()
        val token = prefs.getString("auth_token", null)
        
        val requestWithAuth = if (!token.isNullOrEmpty()) {
            originalRequest.newBuilder()
                .header("Authorization", "Bearer $token")
                .header("Accept", "application/json")
                .header("Content-Type", "application/json")
                .build()
        } else {
            originalRequest.newBuilder()
                .header("Accept", "application/json")
                .header("Content-Type", "application/json")
                .build()
        }
        return chain.proceed(requestWithAuth)
    }
}
```

---

### API Service Interface

**`ApiService.kt`** — Key endpoint definitions:

```kotlin
interface ApiService {

    // ── Public ───────────────────────────────────────────────
    @GET("v1/public/homepage")
    suspend fun getHomepage(): Response<HomepageResponse>

    @GET("v1/public/notices")
    suspend fun getPublicNotices(): Response<NoticesResponse>

    @GET("v1/public/departments")
    suspend fun getDepartments(): Response<DepartmentsResponse>

    // ── Authentication ───────────────────────────────────────
    @POST("auth/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("auth/logout")
    suspend fun logout(): Response<LogoutResponse>

    @POST("auth/verify-otp")
    suspend fun verifyOtp(@Body request: OtpRequest): Response<LoginResponse>

    @POST("auth/refresh-token")
    suspend fun refreshToken(): Response<TokenResponse>

    // ── Current User ─────────────────────────────────────────
    @GET("v1/user")
    suspend fun getCurrentUser(): Response<UserResponse>

    // ── Student ───────────────────────────────────────────────
    @GET("v1/student/dashboard")
    suspend fun getStudentDashboard(): Response<StudentDashboardResponse>

    @GET("v1/student/attendance/summary")
    suspend fun getAttendanceSummary(): Response<AttendanceResponse>

    @GET("v1/student/marks/summary")
    suspend fun getMarksSummary(): Response<MarksResponse>

    @GET("v1/student/subjects")
    suspend fun getStudentSubjects(): Response<SubjectsResponse>

    @GET("v1/student/assignments")
    suspend fun getAssignments(): Response<AssignmentsResponse>

    @POST("v1/student/assignments/{assignment}/submit")
    suspend fun submitAssignment(
        @Path("assignment") assignmentId: Int,
        @Body request: SubmitAssignmentRequest
    ): Response<MessageResponse>

    @GET("v1/student/timetable")
    suspend fun getTimetable(): Response<TimetableResponse>

    @GET("v1/student/notices")
    suspend fun getStudentNotices(): Response<NoticesResponse>

    @GET("v1/student/profile")
    suspend fun getStudentProfile(): Response<UserResponse>

    @PUT("v1/student/profile")
    suspend fun updateStudentProfile(@Body request: UpdateProfileRequest): Response<UserResponse>

    // ── Teacher ───────────────────────────────────────────────
    @GET("v1/teacher/dashboard")
    suspend fun getTeacherDashboard(): Response<TeacherDashboardResponse>

    @GET("v1/teacher/classes")
    suspend fun getTeacherClasses(): Response<ClassesResponse>

    @POST("v1/teacher/attendance/mark")
    suspend fun markAttendance(@Body request: MarkAttendanceRequest): Response<MessageResponse>

    @POST("v1/teacher/attendance/bulk-mark")
    suspend fun bulkMarkAttendance(@Body request: BulkAttendanceRequest): Response<MessageResponse>

    @POST("v1/teacher/marks/submit")
    suspend fun submitMarks(@Body request: MarksSubmitRequest): Response<MessageResponse>

    // ── Parent ────────────────────────────────────────────────
    @GET("v1/parent/dashboard")
    suspend fun getParentDashboard(): Response<ParentDashboardResponse>

    @GET("v1/parent/children")
    suspend fun getChildren(): Response<ChildrenResponse>

    @GET("v1/parent/child/{child}/attendance")
    suspend fun getChildAttendance(@Path("child") childId: Int): Response<AttendanceResponse>

    @GET("v1/parent/child/{child}/marks")
    suspend fun getChildMarks(@Path("child") childId: Int): Response<MarksResponse>

    // ── Admin Management ─────────────────────────────────────
    @GET("v1/admin/teachers")
    suspend fun getTeachers(): Response<TeacherListResponse>

    @POST("v1/admin/teachers")
    suspend fun createTeacher(@Body request: CreateTeacherRequest): Response<MessageResponse>

    @PUT("v1/admin/teachers/{id}")
    suspend fun updateTeacher(@Path("id") id: Int, @Body request: UpdateTeacherRequest): Response<MessageResponse>

    @DELETE("v1/admin/teachers/{id}")
    suspend fun deleteTeacher(@Path("id") id: Int): Response<MessageResponse>

    @GET("v1/admin/students")
    suspend fun getStudents(): Response<StudentListResponse>

    @POST("v1/admin/students")
    suspend fun createStudent(@Body request: CreateStudentRequest): Response<MessageResponse>
}
```

---

### Role-Based Navigation

After login, route users based on the `role` field in the response:

```kotlin
fun handleLoginSuccess(user: UserInfo, navController: NavController) {
    when (user.role) {
        "admin"   -> navController.navigate(Screen.AdminDashboard.route)
        "hod"     -> navController.navigate(Screen.HodDashboard.route)
        "teacher" -> navController.navigate(Screen.TeacherDashboard.route)
        "student" -> navController.navigate(Screen.StudentDashboard.route)
        "parent"  -> navController.navigate(Screen.ParentDashboard.route)
        "alumni"  -> navController.navigate(Screen.AlumniDashboard.route)
        else      -> navController.navigate(Screen.Login.route)
    }
}
```

### Key Data Models (Kotlin)

```kotlin
data class LoginRequest(val email: String, val password: String, val otp: String? = null)

data class LoginResponse(val success: Boolean, val message: String, val data: LoginData)

data class LoginData(val user: UserInfo, val token: String, val token_type: String)

data class UserInfo(
    val id: Int, val name: String, val email: String,
    val phone: String, val role: String,
    val panel_type: String, val avatar_url: String?
)

data class AttendanceData(val present: Int = 0, val absent: Int = 0, val percentage: Float = 0f)

data class Mark(val subject: String, val theory: Float?, val practical: Float?, val total: Float?, val grade: String?)

data class Notice(val id: Int, val title: String, val description: String, val created_at: String)

data class Assignment(val id: Int, val title: String, val description: String, val due_date: String, val status: String)
```

### Android Required Dependencies (`build.gradle`)

```gradle
dependencies {
    // Networking
    implementation 'com.squareup.retrofit2:retrofit:2.9.0'
    implementation 'com.squareup.retrofit2:converter-gson:2.9.0'
    implementation 'com.squareup.okhttp3:logging-interceptor:4.11.0'

    // Coroutines
    implementation 'org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3'

    // ViewModel + LiveData
    implementation 'androidx.lifecycle:lifecycle-viewmodel-ktx:2.7.0'
    implementation 'androidx.lifecycle:lifecycle-livedata-ktx:2.7.0'

    // Secure storage for token
    implementation 'androidx.security:security-crypto:1.1.0-alpha06'

    // Image loading
    implementation 'io.coil-kt:coil:2.5.0'

    // Room (offline caching)
    implementation 'androidx.room:room-runtime:2.6.0'
    kapt 'androidx.room:room-compiler:2.6.0'
    implementation 'androidx.room:room-ktx:2.6.0'
}
```

---

## 🚀 Local Development Setup

### Quick Start

```bash
composer run setup
```

> On **Windows**, use `composer run dev:windows` (avoids `pcntl` extension requirement).

### Manual Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Run database migrations and seed demo data
php artisan migrate --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Install Node.js dependencies & start frontend
npm install
npm run dev

# 7. Start development server
php artisan serve
```

### Seeding Demo Data

```bash
# Seed roles, permissions, and basic config
php artisan db:seed

# Seed full demo data (users, students, attendance, marks)
php artisan db:seed --class=DemoDataSeeder
```

### Useful Development Commands

```bash
php artisan test                          # Run all tests
php artisan route:list                    # List all routes
php artisan route:list --path=api         # List API routes
php artisan tinker                        # Laravel REPL
php artisan optimize:clear                # Clear all caches (after config changes)
php artisan queue:work                    # Start queue worker
php artisan storage:link                  # Create public storage symlink
```

---

## 🌐 Production Deployment

### Prerequisites

- PHP 8.2+, MySQL 8.0+, Apache/Nginx
- SSL/TLS certificate (Let's Encrypt via cPanel AutoSSL)
- SMTP email service (Gmail / SendGrid)
- Domain configured: `https://mmp.sital00.com.np`

### cPanel Deployment

#### Step 1: Upload & Configure

```bash
# SSH into production server
ssh username@mmp.sital00.com.np

cd /home/username/public_html

# Pull latest code
git pull origin main

# Or upload via FTP (FileZilla) and extract
```

#### Step 2: Install Dependencies

```bash
# Production composer install (no dev packages)
composer install --optimize-autoloader --no-dev --no-interaction

# Build frontend assets
npm ci
npm run build
```

#### Step 3: Environment Configuration

```bash
# Copy production environment
cp .env.production .env

# Edit environment values
nano .env

# Generate fresh application key
php artisan key:generate --force
```

#### Step 4: Database Setup

```bash
# Create database via cPanel → MySQL Databases
# Then run migrations
php artisan migrate --force

# Seed initial roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder
```

#### Step 5: Optimize for Production

```bash
php artisan config:cache     # Cache configuration
php artisan route:cache      # Cache routes
php artisan view:cache       # Cache Blade views
php artisan storage:link     # Create public storage link
php artisan queue:restart    # Restart queue workers

# Set file permissions
chmod -R 755 /home/username/public_html
chmod -R 775 storage bootstrap/cache
```

#### Step 6: Web Server Configuration (Apache `.htaccess`)

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header for Sanctum tokens
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Hide PHP version
Header always unset "X-Powered-By"
Options -Indexes
```

#### Step 7: Configure Queue Workers (Supervisor)

```ini
# /etc/supervisor/conf.d/mmp-worker.conf
[program:mmp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/public_html/artisan queue:work redis --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/home/username/public_html/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mmp-worker:*
```

### Environment Configuration

**Production `.env` key variables:**

```env
APP_NAME="Manmohan Memorial Polytechnic"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://mmp.sital00.com.np

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mmp_production
DB_USERNAME=mmp_db_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Queue (Redis recommended for production)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=cookie
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mmp.edu.np
MAIL_FROM_NAME="${APP_NAME}"

# API & Security
SANCTUM_STATEFUL_DOMAINS=mmp.sital00.com.np
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
CORS_ALLOWED_ORIGINS=https://mmp.sital00.com.np

# Feature Flags
FEATURE_TWO_FACTOR_AUTH=true
FEATURE_OTP_LOGIN=true
FEATURE_MOBILE_APP=true

# CTEVT External Sync (optional)
CTEVT_SYNC_EXTERNAL_URL=https://your-external-service.com/sync-endpoint.php
CTEVT_SYNC_API_TOKEN=your-secret-token

# SEO
SEO_SITE_NAME="Manmohan Memorial Polytechnic"
CONTACT_EMAIL=info@mmp.edu.np
GOOGLE_SITE_VERIFICATION=PASTE_FROM_SEARCH_CONSOLE
```

### Security Hardening

#### Force HTTPS in Production

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if ($this->app->environment('production')) {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

#### Security Headers Middleware

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Referrer-Policy', 'no-referrer');
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        return $response;
    }
}
```

### Production Checklist

**Before Deployment:**
- [ ] All tests passing locally
- [ ] Database migrations tested
- [ ] SSL certificate obtained
- [ ] Email service configured and tested
- [ ] Database backup created
- [ ] `.env` configured for production

**After Deployment:**
- [ ] `php artisan config:cache` executed
- [ ] `php artisan route:cache` executed
- [ ] `php artisan view:cache` executed
- [ ] Storage symlink created
- [ ] Queue workers running
- [ ] Test login for all 6 roles
- [ ] Test API endpoints (`/api/v1/public/site-settings`)
- [ ] Test email delivery (create a test account)
- [ ] Verify brand logo and media files
- [ ] Confirm CTEVT sync button works (if applicable)
- [ ] Monitor error logs: `storage/logs/laravel.log`

### Post-Deployment Verification

```bash
# Health check endpoints
curl -X GET https://mmp.sital00.com.np/api/v1/public/site-settings
curl -X GET https://mmp.sital00.com.np/api/v1/public/notices

# Test login
curl -X POST https://mmp.sital00.com.np/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"principal@mmp.edu.np","password":"password"}'

# Test protected endpoint
curl -X GET https://mmp.sital00.com.np/api/v1/student/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"

# Check logs
tail -f storage/logs/laravel.log
```

### Rollback Procedure

```bash
# Git-based rollback
git log --oneline
git revert HEAD

# Cache clear after rollback
php artisan cache:clear
php artisan config:clear

# Database rollback (1 migration step)
php artisan migrate:rollback --step=1

# Database restore from backup
mysql -u mmp_user -p mmp_production < backup_YYYYMMDD.sql
```

---

## 🧪 Testing & Results

### Running Tests

```bash
php artisan test
php artisan test --filter=AuthenticationTest
php artisan test --coverage
```

### Functional Testing Results — 100% Pass Rate

| Module | Tests | Passed | Failed | Pass Rate |
|--------|-------|--------|--------|-----------|
| Authentication & Authorization | 10 | 10 | 0 | **100%** |
| User Management | 8 | 8 | 0 | **100%** |
| Student Management | 10 | 10 | 0 | **100%** |
| Attendance Management | 10 | 10 | 0 | **100%** |
| Marks & Examination | 10 | 10 | 0 | **100%** |
| Notice Board | 10 | 10 | 0 | **100%** |
| Study Materials | 8 | 8 | 0 | **100%** |
| Parent Portal | 8 | 8 | 0 | **100%** |
| Alumni Portal | 7 | 7 | 0 | **100%** |
| PWA Features | 7 | 7 | 0 | **100%** |
| **TOTAL** | **88** | **88** | **0** | **100%** |

### API Testing Results (29/29 Passed)

| Category | Tests | Status |
|----------|-------|--------|
| Public endpoints | 5 | ✅ All Pass |
| Authentication (Login) | 2 | ✅ All Pass |
| Protected endpoints | 1 | ✅ Pass |
| Student profile CRUD | 2 | ✅ Pass |
| Teacher profile CRUD | 2 | ✅ Pass |
| Parent profile CRUD | 2 | ✅ Pass |
| Admin Teacher CRUD | 5 | ✅ All Pass |
| Admin Student CRUD | 5 | ✅ All Pass |
| Admin Parent CRUD | 5 | ✅ All Pass |
| Authorization enforcement | 5 | ✅ All Pass (403s correct) |

### Performance Testing Results

| Metric | Target | Achieved | Status |
|--------|--------|----------|--------|
| Concurrent users | 200 | **250** | ✅ Exceeds |
| Page load (Homepage) | < 3s | **1.2s** | ✅ Pass |
| Page load (Dashboard) | < 3s | **1.8s** | ✅ Pass |
| Database query time | < 100ms | **45ms avg** | ✅ Pass |
| API response time | < 500ms | **180ms avg** | ✅ Pass |
| File upload (10 MB) | < 30s | **12s** | ✅ Pass |
| PDF report generation | < 10s | **6s** | ✅ Pass |

### Security Testing Results

| Vulnerability | Tests | Issues Found | Status |
|--------------|-------|-------------|--------|
| SQL Injection | 50 | 0 | ✅ Secure |
| XSS (Cross-Site Scripting) | 40 | 0 | ✅ Secure |
| CSRF | 30 | 0 | ✅ Secure |
| Authentication Bypass | 25 | 0 | ✅ Secure |
| Authorization Bypass | 35 | 0 | ✅ Secure |
| Session Hijacking | 20 | 0 | ✅ Secure |
| File Upload Vulnerabilities | 15 | 0 | ✅ Secure |
| Information Disclosure | 30 | 0 | ✅ Secure |

### Usability Testing (5-point scale)

| Aspect | Admin | HOD | Teacher | Student | Parent | **Average** |
|--------|-------|-----|---------|---------|--------|-------------|
| Ease of Use | 4.5 | 4.3 | 4.6 | 4.7 | 4.5 | **4.52** |
| Navigation | 4.4 | 4.2 | 4.5 | 4.6 | 4.4 | **4.42** |
| Visual Design | 4.3 | 4.1 | 4.4 | 4.5 | 4.3 | **4.32** |
| Responsiveness | 4.6 | 4.5 | 4.7 | 4.8 | 4.6 | **4.64** |
| Feature Completeness | 4.4 | 4.3 | 4.5 | 4.4 | 4.2 | **4.36** |
| **Overall Satisfaction** | **4.5** | **4.3** | **4.6** | **4.7** | **4.4** | **4.50** |

---

## 📸 Project Screenshots & Demo

> **Live Demo:** https://mmp.sital00.com.np

The system includes the following UI sections:

| Area | Description |
|------|-------------|
| **Public Homepage** | Banners, notices, departments, alumni preview, facilities |
| **Admin Dashboard** | System stats, user counts, pending approvals, audit trail |
| **HOD Dashboard** | Department metrics, attendance overview, exam results |
| **Teacher Dashboard** | Class list, pending marks, assignment management |
| **Student Dashboard** | Attendance %, marks summary, upcoming assignments |
| **Parent Dashboard** | Child performance at a glance, real-time attendance |
| **Alumni Dashboard** | Profile, projects, achievements, career history |
| **Public Result Checker** | Search results without login |
| **Notice Board** | Role-targeted notices with attachments |
| **Timetable** | Weekly schedule per class/section |

---

## 💰 Feasibility & Cost Analysis

### Economic Feasibility

| Cost Component | Minimum | Maximum |
|---------------|---------|---------|
| Web Hosting | NPR 5,000/yr | NPR 15,000/yr |
| Domain Registration | NPR 1,000/yr | NPR 2,000/yr |
| Initial Staff Training | NPR 10,000 (one-time) | NPR 20,000 (one-time) |
| System Maintenance | NPR 5,000/yr | NPR 10,000/yr |
| Miscellaneous | NPR 2,000 | NPR 5,000 |
| **Total (Year 1)** | **NPR 23,000** | **NPR 52,000** |

### Estimated Savings

| Savings Category | Annual Savings |
|-----------------|---------------|
| Paper & printing costs | NPR 40,000 – 80,000 |
| Manual labor reduction | NPR 60,000 – 120,000 |
| Administrative efficiency | NPR 50,000 – 100,000 |
| Communication costs | NPR 30,000 – 60,000 |
| **Total Annual Savings** | **NPR 180,000 – 360,000** |

> **ROI:** Investment recovered within **< 6 months**

### Development Schedule (Agile - 8 Sprints)

| Sprint | Duration | Focus Area | Deliverables |
|--------|----------|-----------|-------------|
| Sprint 0 | 2 weeks | Planning & Setup | Requirements, project plan, dev environment |
| Sprint 1 | 2 weeks | Foundation | Database schema, authentication, user management |
| Sprint 2 | 2 weeks | Core Features | Student/teacher management, department setup |
| Sprint 3 | 2 weeks | Attendance | Recording, viewing, reporting |
| Sprint 4 | 2 weeks | Examinations | Exam setup, mark entry, result calculation |
| Sprint 5 | 2 weeks | Communication | Notice board, notifications, messaging |
| Sprint 6 | 2 weeks | Additional Features | Study materials, parent portal, alumni portal |
| Sprint 7 | 2 weeks | PWA & Polish | PWA, UI refinements, bug fixes |
| Sprint 8 | 2 weeks | Testing & Deployment | Testing, deployment, training |
| **Total** | **31 weeks** | | **~7-8 months** |

---

## 🔮 Future Enhancements

| Feature | Priority | Timeline |
|---------|----------|----------|
| **SMS Integration for 2FA** | High | 3-6 months |
| **Native Android App (Kotlin)** | High | 3-6 months |
| **AI-Powered Analytics & Insights** | Medium | 6-12 months |
| **Online Examination Module** | Medium | 6-12 months |
| **Fee Management & Online Payment** | High | 6-12 months |
| **Biometric Attendance Integration** | Low | 12+ months |
| **Library Management System** | Low | 12+ months |
| **Video Conferencing Integration** | Low | 12+ months |
| **Hostel & Transportation Management** | Low | 12+ months |

---

## 📚 References & Acknowledgment

### References

1. Laravel Documentation — https://laravel.com/docs
2. Spatie Laravel Permission — https://spatie.be/docs/laravel-permission
3. Laravel Sanctum — https://laravel.com/docs/sanctum
4. Retrofit for Android — https://square.github.io/retrofit
5. CTEVT Official Website — https://ctevt.org.np
6. PHP 8.2 Documentation — https://www.php.net/docs.php
7. MySQL 8.0 Reference Manual — https://dev.mysql.com/doc
8. MDN Web Docs (PWA) — https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps

### Acknowledgment

We express our sincere gratitude to:

- **Mohan Tabdar** — Project Supervisor, for invaluable guidance and continuous support
- **Yubraj Chaudhary** — Project Coordinator
- **Er. Sudip Adhikary** — External Examiner
- **Department of Information Technology, MMP** — for resources and facilities
- All **faculty members** of the IT Department for encouragement and technical assistance
- **Administrative staff, teachers, students, and parents** of MMP who participated in testing
- The **open-source community** — especially the Laravel ecosystem

---

## 📄 License

This project is licensed under the **MIT License**.

---

<div align="center">

**MMP Academic Management Portal**  
Manmohan Memorial Polytechnic · Budhiganga Rural Municipality-04, Koshi Province  
Department of Information Technology · Diploma in Information Technology (CTEVT)  
Academic Year 2079–2082 (BS) | April 2026 (AD)

*Built with ❤️ by Sital Mahato, Priti Dev, Prem Singh & Rabin Sardar*

</div>
