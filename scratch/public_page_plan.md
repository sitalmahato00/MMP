# MMP Public Website — Page Architecture Plan

## Page Data Classification

| Page | Data Source | Notes |
|------|------------|-------|
| **Homepage** | `GET /api/v1/public/homepage` | Banners, Departments, Notices, Alumni |
| **About Us — What is MMP** | **Hard-coded** | Institutional mission, static text |
| **About — Objectives** | **Hard-coded** | Static institutional goals |
| **About — President & Principals** | `GET /api/v1/public/principals` | From teachers/users DB, API endpoint needed |
| **About — Contact Us** | **Hard-coded** | Address, phone, map embed |
| **Courses/Departments (listing)** | `GET /api/v1/public/departments` | Dynamic from DB |
| **Course/Department (detail)** | `GET /api/v1/public/departments/{slug}` | Dynamic per dept |
| **Features — Classrooms & Labs** | **Hard-coded** | Static content with images |
| **Features — Workshops** | **Hard-coded** | Static content |
| **Features — Scholarships** | **Hard-coded** | Static content |
| **Features — Transportation** | **Hard-coded** | Static content |
| **Features — Internships** | **Hard-coded** | Static content |
| **Features — Library & Hostel** | **Hard-coded** | Static content |
| **Features — Game Courts** | **Hard-coded** | Static content |
| **Features — First Aid** | **Hard-coded** | Static content |
| **Peoples — Admin Staff** | `GET /api/v1/public/staff` | From teachers DB (admin role), API |
| **Peoples — Dept Teachers** | `GET /api/v1/public/departments/{slug}/teachers` | Per-department teachers |
| **News & Events** | `GET /api/v1/public/notices?type=news` | Dynamic from notices DB |
| **Gallery** | `GET /api/v1/public/gallery` | From media table by type=gallery |
| **Resources — Formats/Downloads** | `GET /api/v1/public/downloads` | From downloads table |
| **Resources — Question Bank** | `GET /api/v1/public/downloads?category=question_bank` | Filtered downloads |
| **Notices Board** | `GET /api/v1/public/notices` | Dynamic from DB |
| **Exam Schedules** | `GET /api/v1/public/notices?type=exam` | Filtered notices |
| **Alumni Page** | `GET /api/v1/public/alumni` | Dynamic from alumni table |

## Architecture Rule (ENFORCED)
```
Public Page (Blade) → JS fetch() → /api/v1/public/* → PublicApiController → PublicDataService → DB
```
NO Blade view ever calls the database directly.

## Navigation Structure (matches official MMP site)
- Home
- About Us → What is MMP | Objectives | Presidents & Principals | Contact
- Courses → (all 7 departments + Short Term)
- Features → (8 sub-pages)
- Peoples → Admin Staff + Per-department teachers
- News & Events
- Gallery
- Resources → Formats | Question Bank
- 🔐 Login Portal
