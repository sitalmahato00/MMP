# MMP ERP — React + Laravel API Migration Architecture

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CLIENTS                                       │
│                                                                      │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │  React SPA       │  │  Java Android    │  │  Future iOS App  │  │
│  │  (Vite + TS)     │  │  Mobile App      │  │  / Flutter       │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────┬─────────┘  │
│           │                     │                      │             │
└───────────┼─────────────────────┼──────────────────────┼────────────┘
            │              HTTPS / Bearer Token           │
            ▼                     ▼                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    LARAVEL API BACKEND                               │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  Route Layer  /api/v1/*  (routes/api.php)                    │   │
│  │  ├── throttle:api middleware                                  │   │
│  │  ├── auth:sanctum middleware                                  │   │
│  │  └── role middleware (spatie/permission)                      │   │
│  └───────────────────────┬──────────────────────────────────────┘   │
│                           │                                          │
│  ┌────────────────────────▼──────────────────────────────────────┐  │
│  │  Module API Controllers (BaseController + ApiResponse trait)  │  │
│  │  ├── StudentApiController                                      │  │
│  │  ├── TeacherApiController                                      │  │
│  │  ├── ExamApiController                                         │  │
│  │  ├── AttendanceApiController                                   │  │
│  │  ├── AcademicApiController                                     │  │
│  │  └── [all other module controllers]                            │  │
│  └───────────────────────┬──────────────────────────────────────┘   │
│                           │                                          │
│  ┌────────────────────────▼──────────────────────────────────────┐  │
│  │  Service Layer (business logic — UNCHANGED)                    │  │
│  │  ├── StudentRecordService                                      │  │
│  │  ├── MarksService                                              │  │
│  │  ├── AttendanceService                                         │  │
│  │  └── OtpService                                                │  │
│  └───────────────────────┬──────────────────────────────────────┘   │
│                           │                                          │
│  ┌────────────────────────▼──────────────────────────────────────┐  │
│  │  Eloquent Models (UNCHANGED)                                   │  │
│  │  Student | Teacher | Exam | Mark | Attendance | User | ...    │  │
│  └───────────────────────┬──────────────────────────────────────┘   │
└───────────────────────────┼─────────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────────┐
│                         DATABASE                                      │
│                     MySQL / PostgreSQL                                │
└─────────────────────────────────────────────────────────────────────┘
```

---

## React Frontend Structure

```
frontend/
├── index.html
├── vite.config.ts
├── tsconfig.json
├── tailwind.config.js
├── package.json
└── src/
    ├── main.tsx                    # App entry — Redux + QueryClient + Router
    ├── App.tsx                     # BrowserRouter + AuthInitializer
    ├── index.css                   # Tailwind base + component classes
    │
    ├── types/
    │   └── index.ts                # All global TypeScript interfaces
    │
    ├── auth/
    │   ├── AuthInitializer.tsx     # Token validation on app load
    │   ├── RequireAuth.tsx         # Route guard (redirect to /login)
    │   └── GuestOnly.tsx           # Redirect authenticated users
    │
    ├── routes/
    │   ├── AppRouter.tsx           # All routes, lazy-loaded
    │   └── navItems.ts             # Per-role sidebar navigation
    │
    ├── store/
    │   ├── index.ts                # configureStore
    │   └── slices/
    │       ├── authSlice.ts        # Token + user state
    │       └── uiSlice.ts          # Sidebar, theme
    │
    ├── hooks/
    │   ├── useRedux.ts             # Typed useAppDispatch/useAppSelector
    │   └── useAuth.ts              # Auth state helpers + role checks
    │
    ├── services/                   # Axios-based API layer
    │   ├── api.ts                  # Axios instance + interceptors
    │   ├── authService.ts
    │   ├── studentService.ts
    │   ├── teacherService.ts
    │   └── academicService.ts
    │
    ├── layouts/                    # Per-role layouts (Sidebar + Topbar)
    │   ├── AdminLayout.tsx
    │   ├── TeacherLayout.tsx
    │   ├── StudentLayout.tsx
    │   └── HodLayout.tsx
    │
    ├── components/
    │   ├── layout/
    │   │   ├── Sidebar.tsx         # Collapsible sidebar with dynamic icons
    │   │   └── Topbar.tsx          # Topbar with notifications + logout
    │   └── ui/
    │       ├── Button.tsx
    │       ├── Input.tsx
    │       ├── Select.tsx
    │       ├── Card.tsx
    │       ├── DataTable.tsx       # Generic typed data table
    │       ├── Pagination.tsx
    │       ├── Modal.tsx
    │       ├── Badge.tsx
    │       ├── StatCard.tsx
    │       └── Spinner.tsx
    │
    ├── pages/
    │   ├── auth/
    │   │   └── LoginPage.tsx       # Email+password login with Zod validation
    │   └── error/
    │       ├── ForbiddenPage.tsx
    │       └── NotFoundPage.tsx
    │
    └── modules/                    # 1:1 mirror of backend modules
        ├── admin/
        │   └── pages/
        │       └── DashboardPage.tsx
        ├── student/
        │   └── pages/
        │       ├── StudentListPage.tsx    # Full CRUD list with filters
        │       ├── StudentShowPage.tsx    # Detail view with delete
        │       ├── StudentCreatePage.tsx  # Form with Zod + react-hook-form
        │       ├── StudentEditPage.tsx
        │       └── StudentDashboardPage.tsx
        ├── teacher/
        │   └── pages/ ...
        ├── exam/
        │   └── pages/ ...
        ├── attendance/
        │   └── pages/ ...
        ├── academic/
        │   └── pages/ ...
        └── hod/
            └── pages/ ...
```

---

## Laravel API Structure (Added on top of existing)

```
app/Modules/
├── Student/
│   ├── Controllers/
│   │   ├── Admin/                        # EXISTING Blade controllers (kept)
│   │   │   └── StudentController.php
│   │   └── Api/                          # NEW — pure JSON API
│   │       └── StudentApiController.php
│   ├── Requests/                         # NEW — Form Request validation
│   │   ├── StoreStudentRequest.php
│   │   └── UpdateStudentRequest.php
│   ├── Models/Student.php                # UNCHANGED
│   └── Services/StudentRecordService.php # UNCHANGED
│
├── Teacher/
│   └── Controllers/Api/TeacherApiController.php
│
├── Exam/
│   └── Controllers/Api/ExamApiController.php
│
├── Attendance/
│   └── Controllers/Api/AttendanceApiController.php
│
└── Academic/
    └── Controllers/Api/AcademicApiController.php
```

---

## API Response Contract

Every API endpoint returns this exact structure:

```json
// Success (single resource)
{
  "success": true,
  "message": "Students retrieved successfully.",
  "data": { ... }
}

// Success (paginated list)
{
  "success": true,
  "message": "Students retrieved successfully.",
  "data": {
    "data": [ ... ],
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "per_page": 20,
      "to": 20,
      "total": 87
    },
    "links": { "first": "...", "last": "...", "next": "...", "prev": null }
  }
}

// Error (validation)
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["This email is already registered."],
    "student_no": ["This student number is already taken."]
  }
}

// Error (not found / forbidden / server)
{
  "success": false,
  "message": "Resource not found."
}
```

---

## API Endpoints Reference

| Method | Endpoint                             | Description                    | Auth Roles        |
|--------|--------------------------------------|--------------------------------|-------------------|
| POST   | /api/auth/login                      | Email+password login           | Public            |
| POST   | /api/auth/otp/send                   | Send OTP to phone              | Public            |
| POST   | /api/auth/otp/verify                 | Verify OTP, get token          | Public            |
| POST   | /api/auth/logout                     | Revoke current token           | Any authenticated |
| GET    | /api/v1/user                         | Current user profile           | Any authenticated |
| GET    | /api/v1/dashboard/stats              | Dashboard summary stats        | Admin/Staff/HOD   |
| GET    | /api/v1/students                     | List students (paginated)      | Admin/HOD         |
| POST   | /api/v1/students                     | Create student                 | Admin             |
| GET    | /api/v1/students/{id}                | Get student detail             | Admin/HOD         |
| PUT    | /api/v1/students/{id}                | Update student                 | Admin/HOD         |
| DELETE | /api/v1/students/{id}                | Soft-delete student            | Admin             |
| POST   | /api/v1/students/{id}/restore        | Restore soft-deleted student   | Admin             |
| GET    | /api/v1/students/export              | Download CSV                   | Admin             |
| GET    | /api/v1/teachers                     | List teachers                  | Admin/HOD         |
| POST   | /api/v1/teachers                     | Create teacher                 | Admin             |
| GET    | /api/v1/exams                        | List exams                     | Admin/Teacher     |
| POST   | /api/v1/exams                        | Create exam                    | Admin             |
| POST   | /api/v1/exams/{id}/publish           | Publish exam                   | Admin             |
| GET    | /api/v1/exams/{id}/marks             | Get marks for exam             | Admin/Teacher     |
| POST   | /api/v1/exams/{id}/marks             | Bulk upsert marks              | Teacher           |
| GET    | /api/v1/attendance                   | List attendance records        | Admin/Teacher/HOD |
| POST   | /api/v1/attendance                   | Mark bulk attendance           | Teacher           |
| GET    | /api/v1/attendance/summary/{student} | Student attendance summary     | Any authenticated |
| GET    | /api/v1/academic/sessions            | List all sessions              | Any authenticated |
| GET    | /api/v1/academic/programs            | List programs                  | Any authenticated |
| GET    | /api/v1/departments                  | List departments               | Any authenticated |

---

## Migration Roadmap (Phase-by-Phase)

### Phase 1 — API Layer on top of existing backend (DONE)
**Goal**: Add API controllers without breaking any existing Blade routes.

✅ Create `Controllers/Api/` folder in each module  
✅ Create `Requests/` for validation in each module  
✅ Implement `BaseController` + `ApiResponse` trait (already existed)  
✅ Expand `routes/api.php` with v1 routes  
✅ All new controllers return JSON only  
✅ Existing Blade controllers completely untouched  

---

### Phase 2 — React Frontend (Module by Module)
**Goal**: Build each frontend module in parallel with existing Blade system.

Order of priority (highest business value first):

1. **Auth** — Login page, token storage, role routing ✅
2. **Dashboard** — Stats overview ✅
3. **Student module** — Full CRUD (list, show, create, edit, delete) ✅
4. **Teacher module** — Full CRUD
5. **Exam module** — Exams + marks entry
6. **Attendance module** — Mark attendance + reports
7. **Academic module** — Sessions, programs, subjects
8. **Library module** — Book management
9. **Hostel module** — Room/bed assignment
10. **Payroll module** — Salary management
11. **Accounts module** — Fee collection
12. **Notification module** — In-app + email notifications
13. **CMS module** — Pages, notices, downloads
14. **Settings module** — System configuration

---

### Phase 3 — Gradual Blade replacement
**Goal**: Replace one Blade view at a time with React SPA route.

For each module:
1. Build React page
2. Test it against the API
3. Update Blade route to redirect to React SPA path
4. Remove Blade view file

---

### Phase 4 — Complete SPA transition
**Goal**: Laravel serves only `public/spa/index.html` for all non-API requests.

1. Run `npm run build` from `frontend/` — outputs to `public/spa/`
2. Add catch-all route in `routes/web.php`:
   ```php
   Route::get('/{any}', fn() => file_get_contents(public_path('spa/index.html')))
       ->where('any', '.*');
   ```
3. Remove all Blade view files from `resources/views/`
4. Remove all web routes except the SPA catch-all

---

### Phase 5 — Hardening for Production
**Goal**: Production-ready, mobile-friendly, enterprise-grade.

- [ ] Add API versioning header (`X-API-Version`)
- [ ] Implement refresh token rotation
- [ ] Add Redis-based rate limiting
- [ ] Enable response caching for static data (departments, programs)
- [ ] Add Sanctum token scopes per role
- [ ] Set up CORS properly in `config/cors.php`
- [ ] Enable HTTPS, HSTS headers
- [ ] Set up CDN for static assets
- [ ] Add API monitoring (Telescope + Horizon)
- [ ] Write PHPUnit tests for all new API controllers

---

## Java Android Integration Guide

The same API works for Android. Add this to your Android project:

```java
// Retrofit base URL
String BASE_URL = "https://yourcollegedomain.com/api/v1/";

// Auth header interceptor
OkHttpClient client = new OkHttpClient.Builder()
    .addInterceptor(chain -> {
        String token = TokenManager.getToken(context);
        Request request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer " + token)
            .addHeader("Accept", "application/json")
            .build();
        return chain.proceed(request);
    })
    .build();
```

Key points for Android consumption:
- All responses are consistent JSON (`success`, `message`, `data`)
- Pagination uses standard Laravel `meta.current_page` + `meta.last_page`
- Token obtained from `/api/auth/otp/verify` (phone-based)
- All data filterable via query params
- Stateless — no sessions, no cookies needed

---

## Technology Stack Summary

| Layer         | Technology          | Purpose                             |
|---------------|---------------------|-------------------------------------|
| Frontend      | React 18 + TypeScript | SPA UI                            |
| Bundler       | Vite 5              | Fast dev + optimized builds         |
| Styling       | Tailwind CSS 3      | Utility-first CSS                   |
| State         | Redux Toolkit       | Global auth + UI state              |
| Server State  | TanStack Query v5   | API data fetching + caching         |
| Forms         | React Hook Form + Zod | Type-safe form validation         |
| HTTP          | Axios               | API client with interceptors        |
| Routing       | React Router v6     | Client-side routing                 |
| Notifications | react-hot-toast     | Toast notifications                 |
| Backend       | Laravel 11          | PHP API framework                   |
| Auth          | Laravel Sanctum     | Token-based auth                    |
| Permissions   | spatie/permission   | RBAC                                |
| Database      | MySQL               | Primary data store                  |
| Queue         | Laravel Horizon     | Background jobs                     |
