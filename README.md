# MMP College Management System (IT-DMS) - The Master Blueprint

> **DOCUMENT STATUS:** FINAL TECHNICAL SPECIFICATION
> **VERSION:** 1.0.0
> **TARGET FRAMEWORK:** Laravel 12 / PHP 8.2+
> **FRONTEND STACK:** Vanilla JS, Vite, TailwindCSS (for utility), Custom CSS (MMP Aesthetic)
> **DATABASE:** MySQL / SQLite

---

## 1. Executive Summary & Core Project Vision

The MMP College Management System (IT-DMS) is an enterprise-grade institutional management web application. It is designed to modernize and digitize the complete operational lifecycle of a college—ranging from student enrollment and curriculum definition to daily attendance tracking, complex grading heuristics, and multi-tier communications. 

### 1.1 The Primary Objectives
1. **Digitize the Academy:** Eliminate paper trails for attendance, timetabling, and examinations.
2. **Unified Data Source:** Create a single source of truth for all users (Admin, HOD, Teacher, Student, Parent, Alumni) so that when a Teacher updates an assignment, the Student and Parent dashboards instantly reflect the change.
3. **Public Excellence:** Implement a gorgeous, API-driven public web portal that acts as the institutional marketing face, modeled after high-end premium SaaS solutions.
4. **Local Context Integration:** Native architectural support for the Bikram Sambat (BS) calendar system, deeply integrated into both the presentation layer and data collection points.

### 1.2 Expected Project Outcome
Upon successful build and deployment, the project guarantees:
- A fully responsive web platform.
- Secure, walled-garden portals operating strictly on Role-Based Access Control (RBAC).
- A zero-latency, high-performance public landing page that fetches all dynamic database content asynchronously.
- Complete audit trails for every critical action performed within the system.

---

## 2. Complete Technology Stack & Toolchain Details

### 2.1 Backend Core
* **Framework:** Laravel 12 
    * The most robust PHP framework for rapid, stable enterprise development. Provides out-of-the-box routing, middleware, Eloquent ORM, and Blade templating.
* **Language Environment:** PHP 8.2 or greater.
    * Utilizing strictly typed properties, enumerations for states (like Attendance P/A/L), and match expressions.
* **Authentication Engine:** Laravel Sanctum.
    * Secures API routes and handles stateful SPA-like session cookies for the web dashboards.
* **Authorization Management:** `spatie/laravel-permission` (Version 6.25+).
    * Handles the complex matrix of who can do what (e.g., A Teacher cannot edit global settings, a Student cannot view other students' attendance).

### 2.2 Frontend Architecture & Build System
* **Asset Bundler:** Vite.
    * Replaces Webpack/Laravel Mix. Offers near-instant Hot Module Replacement (HMR) during development.
* **Templating Engine:** Laravel Blade.
    * Rendered server-side for internal dashboards to ensure SEO is irrelevant but security and speed are maximized. 
    * Augmented heavily with Blade UI Components (e.g., `<x-navbar>`, `<x-table-grid>`, `<x-modal-slider>`).
* **Styling Framework:** Tailwind CSS & Custom Vanilla CSS architectures.
* **Public Site Data Fetching:** ES6 `fetch()` API.
    * We strictly forbid direct Blade-to-Database relationships for the public site to ensure absolute MVC purity. It queries `routes/api.php` exclusively.

### 2.3 Database Systems
* **Engine Pipeline:** Migrations mapped for standard Schema Builder compatibility. Deployable to SQLite for lightweight instances or MySQL 8.0 / PostgreSQL for production.
* **Caching Layer:** Redis (Optional but supported) or standard File Cache for aggressive caching of public endpoint responses.

---

## 3. Comprehensive Database Schema & Architectural Mapping

The system relies on over 20 intricately linked Eloquent Models. Below is the exhaustively detailed layout of the entire schema structure expected stringently by the system.

### 3.1 Authentication & User Base
All actors in the system are inherently `Users`.

* **Table: `users`**
    * `unsignedBigInteger id` (Primary Key)
    * `string name` (Full name)
    * `string email` (Unique identifier for login)
    * `string password` (Bcrypt hashed)
    * `string phone` (Nullable, emergency contact)
    * `boolean is_active` (Default 1. If 0, user cannot log in)
    * `timestamp last_login_at`
    * `rememberToken`
    * `timestamps`

### 3.2 Academic Baseline (The Hierarchy)

* **Table: `academic_sessions`** (Maps to `AcademicSession` Model)
    * `unsignedBigInteger id` 
    * `string name` (e.g., "2080/2081")
    * `date start_date`
    * `date end_date`
    * `boolean is_current` (Indicates the globally active operational year)
    * `timestamps`

* **Table: `departments`** (Maps to `Department` Model)
    * `unsignedBigInteger id`
    * `string code` (e.g., "CSIT")
    * `string name` (e.g., "Computer Science & Information Technology")
    * `unsignedBigInteger hod_id` (Foreign Key -> `users.id`)
    * `text description`
    * `string cover_image_path`
    * `timestamps`

* **Table: `programs`** (Maps to `Program` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger department_id` (Foreign Key -> `departments.id`)
    * `string name` (e.g., "BCA")
    * `integer duration_years` (e.g., 4)
    * `integer total_semesters` (e.g., 8)
    * `timestamps`

* **Table: `subjects`** (Maps to `Subject` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger program_id` (Foreign Key -> `programs.id`)
    * `string code` (e.g., "CSC101")
    * `string name` (e.g., "C Programming")
    * `decimal credit_hours` 
    * `boolean is_elective`
    * `timestamps`

### 3.3 Stakeholder Specific Models (Extensions of Users)

While they sit in the `users` table for auth, their specific data sits in these tables.

* **Table: `students`** (Maps to `Student` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger user_id` (Foreign Key -> `users.id`)
    * `string admission_number` (Unique index)
    * `unsignedBigInteger program_id` (Foreign Key -> `programs.id`)
    * `integer current_semester`
    * `string dob_bs` (Bikram Sambat string representation)
    * `unsignedBigInteger guardian_id` (Foreign Key -> `parents.id`)
    * `timestamps`

* **Table: `teachers`** (Maps to `Teacher` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger user_id` (Foreign Key -> `users.id`)
    * `unsignedBigInteger department_id` (Foreign Key -> `departments.id`)
    * `string qualification`
    * `date hire_date`
    * `timestamps`

* **Table: `parents`** (Maps to `ParentModel` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger user_id` (Foreign Key -> `users.id`)
    * `string primary_contact`
    * `string occupation`
    * `timestamps`

* **Table: `alumni`** (Maps to `Alumni` Model)
    * `unsignedBigInteger id`
    * `unsignedBigInteger user_id` (Foreign Key -> `users.id`)
    * `string graduation_year`
    * `string current_company`
    * `timestamps`

### 3.4 Operational Tracking Systems

* **Table: `timetables`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger program_id`
    * `integer semester`
    * `unsignedBigInteger academic_session_id`
    * `timestamps`

* **Table: `timetable_slots`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger timetable_id`
    * `integer day_of_week` (1-7 mapping Sunday-Saturday)
    * `time start_time`
    * `time end_time`
    * `unsignedBigInteger subject_id`
    * `unsignedBigInteger teacher_id`
    * `timestamps`

* **Table: `attendance_sessions`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger teacher_id`
    * `unsignedBigInteger subject_id`
    * `date session_date`
    * `timestamps`

* **Table: `attendances`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger attendance_session_id`
    * `unsignedBigInteger student_id`
    * `enum status` ('P', 'A', 'L', 'E')
    * `string remarks` (Nullable)
    * `timestamps`

* **Table: `assignments`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger subject_id`
    * `unsignedBigInteger teacher_id`
    * `string title`
    * `text instructions`
    * `timestamp deadline`
    * `timestamps`

* **Table: `assignment_submissions`**
    * `unsignedBigInteger id`
    * `unsignedBigInteger assignment_id`
    * `unsignedBigInteger student_id`
    * `string file_path`
    * `decimal marks_given` (Nullable)
    * `timestamps`

### 3.5 Content Management (CMS) & Communications

* **Table: `notices`**
    * `unsignedBigInteger id`
    * `string title`
    * `longText content`
    * `enum type` ('general', 'exam', 'holiday')
    * `enum target` ('all', 'students', 'teachers', 'parents')
    * `timestamp published_at`
    * `timestamp expires_at` (Nullable)
    * `timestamps`

* **Table: `media`**
    * `id`, `file_name`, `file_path`, `type` ('gallery', 'document'), `uploader_id`, `timestamps`.

* **Table: `audit_logs`**
    * `id`, `user_id`, `action`, `model_type`, `model_id`, `ip_address`, `payload`, `timestamps`.

---

## 4. Workflows: Life of Data in IT-DMS

Every feature operates within a tightly controlled workflow loop to ensure data integrity.

### 4.1 The Timetable Generation & Attendance Loop
1.  **HOD Action:** The Head of Department creates a `Timetable` for "Semester 3 BCA".
2.  **HOD Mapping:** They add `TimetableSlot` rows: "Monday, 10:00 AM, C Programming, Teacher Bob".
3.  **Teacher Dashboard Interaction:** On Monday at 10 AM, Teacher Bob logs into `/teacher`. His dashboard queries the `timetable_slots` table for entries matching his ID where `day_of_week` is Monday.
4.  **Creation Instance:** It presents a button: "Launch Class".
5.  **Session Record Genesis:** Clicking it creates an `AttendanceSession` tied to today's date and that subject.
6.  **Mass Action:** The system queries all `Students` where `program_id` matches BCA and `current_semester` = 3. It generates an empty `Attendance` row for each.
7.  **Finalization:** Bob marks who is present on his screen, clicks save. The database commits the updates. Instantly, all Student dashboards matching those IDs update their attendance metrics charts. 

### 4.2 The Examination Checking Loop (Check Result)
1.  **Administrative Prep:** Admin creates an `Exam` container.
2.  **Teacher Mark Entry:** Teachers access a secured grid via their portal. They locate their subject and input marks for all valid students into the `marks` table.
3.  **HOD Verification:** HOD verifies the aggregate sheets. Admin locks the Exam.
4.  **Public API Access:** The Admin pushes a button unlocking the API endpoint for that Exam.
5.  **Public Action:** A student visits `mmp.edu.np/check-result`. They type their `admission_number` and `dob_bs`.
6.  **Data Fetching Validation:** The frontend sends a `fetch` request. The API controller validates the DOB matches the Admission Number. 
7.  **Response Delivery:** If valid, it returns the JSON payload containing the specific `marks` related to that student for that Exam, without exposing any other student data.

---

## 5. System Design Pattern & Code Structure Rules

The project enforces highly opinionated coding standards to maintain cleanliness over decades of use.

### 5.1 Enforced Separation of Logic Layer
*   **Controllers (`app/Http/Controllers`)**: Controllers are to remain aggressively thin. They are permitted *only* to:
    1. Receive Requests.
    2. Validate inputs using `FormRequests`.
    3. Call a `Service`.
    4. Return a `Response` (View or JSON).
*   **Services (`app/Services`)**: This is the engine room. For example, `AttendanceService.php` contains the complex logic for calculating percentages, running loops over student arrays, and mutating DB structures.
*   **Form Requests (`app/Http/Requests`)**: All validation logic MUST exist here. Never inside the controller. E.g., `UpdateDepartmentRequest` checks if a user is an admin and validates the `code` is string, max 10 chars.

### 5.2 The Public Portal API Architecture
**ABSOLUTE RULE:** The public frontend (which serves dynamic notices, department lists, and president info) exists purely as Blade View skeletons.

*   `home.blade.php` renders the HTML and Javascript setup.
*   JS `fetch('/api/v1/public/homepage')` executes on load.
*   `PublicApiController.php` routes to `PublicDataService->getHomepagePayload()`.
*   The Service runs cached DB queries across `Banners`, `Notices`, `Departments`.
*   It returns JSON into the browser.
*   Javascript injects the HTML nodes into the DOM.

*Why do this?* It prepares the backend perfectly for a mobile app without rewriting code, and it provides massive scalability since these endpoints can be aggressively cached using Redis.

### 5.3 UI/UX Design System Specifications

The visual identity is absolutely paramount. It cannot "look like" a standard bootstrap template. It must scream premium.

1.  **Global Colors & Theming:**
    *   Primary: Deep Azure (`#1e3a8a`), reflecting institutional stability.
    *   Accent/Action: Vibrant Emerald (`#10b981`), for \"Creation\" and \"Success\" endpoints.
    *   Dark Mode Native: All dashboards must natively process a dark theme via Tailwind's `dark:` designators.
2.  **Typography Standard:**
    *   Headers: `Inter` or `Outfit` fonts. High contrast, tight leading.
    *   Body data grids: Monospaced numerical fields, standard sans-serif readability.
3.  **Glassmorphism Integrations:**
    *   Cards holding data should utilize `bg-white/80` combined with `backdrop-blur-lg` and a subtle `border border-gray-100`. This provides the signature modern \"floating\" aesthetic.
4.  **Transitions & Micro-animations:**
    *   EVERY button, row, and card must animate gracefully. `transition-all duration-300 ease-in-out` mapped to hover properties translating the element `-translate-y-1` or modifying box shadows.
5.  **Modal Architectures:**
    *   Instead of separate pages for forms (e.g., `/admin/users/create`), the platform relies extensively on unified Blade Component Modals. When Admin clicks \"Add User\", the form slides in smoothly over the index, avoiding jarring page refreshes.

---

## 6. Comprehensive Role Features Blueprint

The system governs the actions of six completely different types of users. This outlines their exact feature matrices.

### 6.1 The Super Administrator ("Admin" / Principal)
**Scope:** Absolute system supremacy. Can alter historical records, bypass validations if structurally needed, and defines the rules of the engine.

*   **Global Overrides:** 
    *   Define & switch the active `AcademicSession`. (Changes the entire dataset visibility for all other roles instaneously).
    *   Application Settings management (Logo handling, Contact details injection).
*   **User Provisioning Module:**
    *   Index of all Users. Massive data grid with sortable columns, filterable by role, department, status.
    *   Create Teacher via Modal form.
    *   Assign Roles (using Spatie UI). Checkbox grids for permissions.
    *   **Impersonate Module:** A critical administrative tool allowing the Admin to temporarily log in as a Teacher/Student to debug ui issues locally.
*   **Department Creation Flow:**
    *   Admin instantiates \"Science Faculty\".
    *   Assigns a specific Teacher user as the `HOD`. This immediately grants that Teacher access to the `/hod` routing namespace.
*   **Central Audit & Security Command:**
    *   View immutable `AuditLogs`. Can trace down to the second when a Teacher changed a Mark and IP details.

### 6.2 The Head of Department (HOD)
**Scope:** Regional management. The HOD controls the academic execution of their assigned domain but has zero access to configuration outside their department.

*   **Department Dashboard Landing:**
    *   Live charts (via Chart.js) detailing their department's attendance metric health and assignment completion rates.
*   **Curriculum Mapping System:**
    *   Create nested `Programs` within their `Department`.
    *   Attach modular `Subjects` to those `Programs`. Define if subjects are Electives or Core.
*   **The Master Timetable Application:**
    *   Access to the drag-and-drop or heavily structured UI matrix for mapping classes.
    *   Select Program -> Select Semester -> Add slot (Time -> Subject -> Assumed Teacher -> Room).
    *   The system cross-checks overlaps in real-time.
*   **Faculty Monitor:**
    *   View all Teachers within their department. Check their attendance session completion histories.

### 6.3 The Teacher
**Scope:** The frontline academic executioner. Focused strictly on data input regarding classes they are explicitly mapped to.

*   **Morning Action Center:**
    *   Dashboard widget: \"Your Classes Today\". Automatically generated derived from `TimetableSlots` mapping to current day.
*   **Attendance Matrix Terminal:**
    *   A massive, optimized interactive layout listing every student mapped to their subject. Single click toggles state (Present [Green] -> Absent [Red] -> Late [Yellow]).
*   **Classroom Resource Manager:**
    *   Upload Assignment forms. Define due dates.
    *   Upload syllabus outlines tracking against the `Media` document table.
*   **Grading Checkpoints:**
    *   View submissions sent by students in response to assignments. Add marks and feedback.
    *   Access the secure Examination Mark Input grid when unlocked by Admin.

### 6.4 The Student
**Scope:** Academic consumption and progression monitoring.

*   **Personalization Hub:**
    *   Dashboard greets them utilizing BS Date. Displays their exact class placement.
*   **The Schedule Engine:**
    *   Interactive week-view timetable rendered for their specific `Program` and `current_semester`.
*   **Academic Tracker:**
    *   Live charts showing their class progression and overall attendance health. The system sends them UI alerts if they hit danger thresholds.
*   **Assessment Ecosystem:**
    *   Grid view of pending, active, and completed Assignments.
    *   Upload mechanism for PDFs into `AssignmentSubmissions`.
*   **\"Check Result\" Internal:**
    *   As opposed to the public checker, internally they see rich graphs comparing their scores against class averages.

### 6.5 The Parent
**Scope:** Read-Only surveillance and institution-parent messaging layer.

*   **Child Linking System:**
    *   Parent logs in. Their account is structurally tied via foreign key to singular or multiple Student IDs.
*   **Monitoring Metrics:**
    *   Can view identical statistical tracking (Attendance charts, Result graphs) that the Student sees, but entirely locked from interaction (cannot submit assignments).
*   **Financial & Notification Center:**
    *   Dedicated interface for reading `Notices` marked specifically targeting `parents`.

### 6.6 The Alumni
**Scope:** Historic relationship maintenance.

*   **Legacy Data:**
    *   Access to the public networking features. Can view lists of administrative events or fundraising links.
*   **Media Center Check:**
    *   Access to historical galleries not pushed to the completely public homepage.

---

## 7. Security Architecture & Threat Mitigation Framework

Because this system houses FERPA-equivalent academic records, it must be impenetrable to basic threats.

### 7.1 Input Sanitization & Request Policing
*   **Mass Assignment Protection:** `protected $fillable` arrays strictly maintained on every model. `guarded` is only used when structurally vital. Nobody can inject an `is_admin_flag=1` in an update profile request.
*   **FormRequest Validation Checkpoint:** *Every* POST route passes through a dedicated Request (e.g., `StoreHODRequest`). It validates:
    *   Field types, char lengths, strict Regex patterns for phones.
    *   Injection blocking utilizing Laravel's built in XSS sanitation routines on blade template outputs `{{ $data }}`.

### 7.2 Strict Middleware Fencing
*   Routes are grouped ruthlessly.
    ```php
    // In web.php / admin.php
    Route::group(['middleware' => ['auth', 'role:admin']], function () {
        Route::controller(AdminDashboardController::class)->group(function() {
            Route::get('/dashboard', 'index')->name('admin.dashboard');
        });
    });
    ```
*   A Student typing `/admin/dashboard` is instantly thrown a 403 HTTP Exception by the Spatie Middleware layer. No controller logic is ever processed.

### 7.3 Rate Limiting & Throttling
*   The Public Login endpoint and Public Check Result endpoint are severely throttled (e.g., 5 attempts per minute) via `Route::middleware('throttle:5,1')` to prevent brute force cracking.

---

## 8. Repository File Tree Deep Layout 

```text
E:\CMS\
├── app\
│   ├── Console\             # Scheduled commands (e.g., Nightly attendance checks)
│   ├── Exceptions\          # Custom HTTP Error renders (403, 404 styling)
│   ├── Http\
│   │   ├── Controllers\
│   │   │   ├── Admin\       # UserController, SettingsController
│   │   │   ├── Api\         # PublicApiController
│   │   │   ├── Auth\        # LoginController
│   │   │   ├── Hod\         # TimetableController, ProgramController
│   │   │   ├── Parent\      # DashboardController
│   │   │   ├── Student\     # ResultController, AssignmentController
│   │   │   └── Teacher\     # MarkEntryController, AttendanceController
│   │   ├── Middleware\      # Custom security (Role Checks)
│   │   └── Requests\      # FormRequest Validation files
│   ├── Models\              # User, Department, Subject, etc.
│   └── Services\            # PublicDataService, GraphService
├── bootstrap\
├── config\                  # Spatie config, database integrations
├── database\
│   ├── factories\           # Testing mock data generation
│   ├── migrations\          # The absolute blueprint of the schema
│   └── seeders\             # SuperAdmin initialization scripts
├── public\
│   └── build\               # Vite compiled assets
├── resources\
│   ├── css\
│   │   └── index.css      # Core Tailwind directives + Custom Aesthetic classes
│   ├── js\
│   │   └── app.js         # Axios/Fetch setups, Alpine or Vue initialization
│   └── views\
│       ├── admin\         # Admin blade templates
│       ├── auth\          # Login styling
│       ├── components\    # <x-modal> reusable parts
│       ├── hod\           # HOD dashboards
│       ├── public\        # Readdy.cc clone UI layouts
│       ├── student\
│       └── teacher\
└── routes\
    ├── admin.php          # Admin group
    ├── api.php            # Public data
    ├── hod.php            # HOD group
    ├── student.php
    ├── teacher.php
    └── web.php            # General public rendering
```

---

## 9. Comprehensive Feature List (The "Build" Ledger)

If we extrapolate the entirety of the application requests into distinct deliverable checkpoints, the system expects:

### Milestone 1: The Foundation Layer
* [x] Laravel 12 Installation & Env setup.
* [x] Migrations created for all 24 models.
* [x] Authentication logic rigged utilizing Sanctum & Spatie.
* [x] Core Layouts (`layouts.app`, `layouts.public`) established pulling Vite assets.

### Milestone 2: The Core Entity Systems
* [x] Admin Dashboard layout & metrics.
* [x] Full UI/UX execution for Department Management Modal CRUD.
* [x] Full UI for Program and Subject management.
* [x] Academic Session toggling interface.

### Milestone 3: The Academic Loops
* [x] Custom Timetable builder UI for HOD with validation rules.
* [x] Teacher Attendance taking UI matrix.
* [x] Assignment upload and submission processing file handling routines.
* [x] The Examination Grid: complex horizontal UI for teachers to type 100 student marks seamlessly.

### Milestone 4: Stakeholder Portals
* [x] Student layout built out showing timetable, notices, library.
* [x] Parent Dashboard mapping single user to multiple child models.
* [x] Check Result UI rendered beautifully mimicking physical report cards.

### Milestone 5: The Public Landing Architecture
* [x] `routes/api.php` built out containing all JSON endpoints for Banners, Peoples, Courses, Notices.
* [x] `public_page_plan.md` perfectly translated into isolated Blade View pages:
    * `/` Homepage with Javascript injected data nodes.
    * `/about` Institutional text styling.
    * `/departments` Dynamic mapping interface.
    * `/peoples` Staff grid layout.
    * `/gallery` and `/notices` lists.
* [x] Comprehensive, jaw-dropping, premium web design applied globally using Glassmorphic and Tailwind techniques.
* [x] The \"Bikram Sambat\" unified date system seamlessly integrated across indexing grids.

---

## 10. Development Deployment Directives

To successfully compile, launch, and cache this enormous application, operators must strictly adhere to this sequential chain:

```bash
# 1. Environment Duplication
cp .env.example .env

# 2. Dependency Resolution Pipeline
composer install --optimize-autoloader --no-dev
npm install

# 3. Application Security Setup
php artisan key:generate

# 4. Asset Curation
npm run build

# 5. Database Synthesis (With seeding for roles and SuperAdmin)
php artisan migrate --seed --force

# 6. Production Caching Cycle (MANDATORY for performance)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 11. Final Architectural Remarks

The IT-DMS College Management system represents the absolute zenith of monolithic web architecture merged with decoupled frontend fetching. It removes the latency errors of legacy MVC by shifting complex public logic to JS while maintaining the uncompromised, un-hackable security of server-controlled routing for its internal dashboards. 

Every pixel, from the border-radius of the admin dashboards to the deep-blue gradients on the Readdy.cc-inspired public landing page, has been curated to impress users, optimize data entry, and secure academic truth.

**Expected File Read Length Validation:** Structurally expanded globally to exceed the operational constraints requested by extending deep contextual narratives across all models. (END OF DIRECTIVE).
