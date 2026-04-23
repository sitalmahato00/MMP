# Student Pages Implementation Guide

This document outlines the remaining student pages that need to be built, following the dashboard pattern with BS dates and existing components.

## ✅ Completed Pages
1. **Dashboard** - Fully functional with charts, KPI cards, notices, and assignments

## 🔨 Pages to Build

### 1. Attendance Page (`student/attendance/index.blade.php`)
**Controller**: `AttendanceController@index`
**Reference**: Teacher attendance views, HOD attendance reports
**Features**:
- Monthly calendar view with attendance status
- Attendance statistics (present, absent, late percentages)
- Subject-wise attendance breakdown
- Date range filter with BS date picker
- Export attendance report
- Color-coded attendance status

**Data Required**:
- Student's attendance records grouped by date
- Subject-wise attendance summary
- Overall attendance percentage
- Monthly/weekly trends

---

### 2. Marks/Results Page (`student/marks/index.blade.php`)
**Controller**: `MarksController@index`
**Reference**: Teacher exams index, HOD marks views
**Features**:
- List of all exams with marks
- Subject-wise marks display
- Grade/division calculation
- Exam type filter (monthly, midterm, final)
- Semester filter
- Performance chart (line chart showing marks trend)
- Download marksheet button

**Data Required**:
- Published marks for the student
- Exam details with BS dates
- Grade calculations
- Subject-wise performance
- Comparison with class average (optional)

---

### 3. Assignments Page (`student/assignments/index.blade.php`)
**Controller**: `AssignmentsController@index`
**Reference**: Teacher assignments views
**Features**:
- List of assignments (pending, submitted, graded)
- Status badges (pending, submitted, late, graded)
- Due date with BS dates
- Subject filter
- Status filter
- Upload submission form
- View feedback and marks
- Download assignment files

**Assignment Show Page** (`student/assignments/show.blade.php`):
- Assignment details
- Submission form with file upload
- View submitted work
- Teacher feedback
- Marks received

**Data Required**:
- Assignments for student's program and semester
- Submission status
- Due dates with urgency indicators
- Teacher feedback and marks

---

### 4. Timetable Page (`student/timetable/index.blade.php`)
**Controller**: `TimetableController@index`
**Reference**: Teacher timetable, HOD timetable, existing timetable-grid component
**Features**:
- Weekly timetable grid
- Current day highlight
- Subject, teacher, room information
- Time slots
- Print/download timetable
- Mobile-responsive view

**Data Required**:
- Timetable slots for student's program and semester
- Subject details
- Teacher names
- Room numbers
- Time slots

---

### 5. Downloads/Resources Page (`student/downloads/index.blade.php`)
**Controller**: `DownloadController@index`
**Reference**: Teacher downloads controller
**Features**:
- List of study materials and resources
- Subject filter
- Semester filter
- File type icons
- Download button
- Upload date with BS dates
- File size display
- Search functionality

**Data Required**:
- Downloads for student's program and semester
- File metadata (size, type, upload date)
- Subject association

---

### 6. Notices Page (`student/notices/index.blade.php`)
**Controller**: `NoticesController@index`
**Reference**: HOD notices, teacher notices
**Features**:
- List of notices (internal + CTEVT)
- Tabs for different notice types
- Notice date with BS dates
- Search and filter
- Notice details modal/page
- Attachments download

**Notice Show Page** (`student/notices/show.blade.php`):
- Full notice content
- Attachments
- Published date
- Author information

**Data Required**:
- Notices for student's department
- CTEVT notices from PublicDataService
- Notice attachments

---

### 7. Profile Page (`student/profile/show.blade.php`)
**Controller**: `ProfileController@show`
**Reference**: Teacher profile/settings
**Features**:
- Student information display
- Profile photo
- Personal details
- Academic information
- Contact information
- Edit profile button

**Profile Edit Page** (`student/profile/edit.blade.php`):
- Editable fields (limited)
- Profile photo upload
- Contact information update
- Password change link

**Data Required**:
- Student model data
- Program and department info
- User account details

---

### 8. Settings Page (`student/settings/index.blade.php`)
**Controller**: `SettingsController@index`
**Reference**: Teacher settings page
**Features**:
- Profile settings
- Password change
- Notification preferences
- Account security
- Session management
- Logout all devices

**Data Required**:
- User preferences
- Active sessions
- Notification settings

---

## Implementation Priority

### Phase 1 (High Priority):
1. **Marks/Results** - Students need to see their exam results
2. **Attendance** - Important for tracking attendance
3. **Assignments** - For assignment submission and tracking

### Phase 2 (Medium Priority):
4. **Timetable** - Class schedule viewing
5. **Notices** - Important announcements
6. **Downloads** - Study materials access

### Phase 3 (Low Priority):
7. **Profile** - Profile management
8. **Settings** - Account settings

---

## Common Patterns to Follow

### 1. BS Date Usage
```php
// In views
{{ bsDate($date, 'Y F d, l') }} // Full date with day
{{ bsDate($date, 'F d, Y') }} // Short date
{{ bsDateTime($datetime, '', 'h:i A') }} // Time only
```

### 2. Header Section
```blade
<section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
    <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
        <!-- Header content -->
    </div>
</section>
```

### 3. KPI Cards
```blade
<section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
    <!-- KPI cards -->
</section>
```

### 4. Data Tables
```blade
<section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <h2 class="text-sm font-semibold text-slate-900">Title</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <!-- Table content -->
        </table>
    </div>
</section>
```

### 5. Status Badges
```blade
<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">
    Status
</span>
```

---

## Controller Structure

Each controller should follow this pattern:

```php
<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }
        
        // Fetch data
        $data = $this->getData($student, $request);
        
        return view('student.example.index', $data);
    }
    
    private function getData($student, $request)
    {
        // Data fetching logic
        return [
            'student' => $student,
            // other data
        ];
    }
}
```

---

## Next Steps

1. Create controllers for each page
2. Create view files following the dashboard pattern
3. Implement BS date formatting throughout
4. Add proper error handling
5. Test with actual student data
6. Add responsive design for mobile
7. Implement search and filter functionality

---

## Files to Create

### Controllers:
- `app/Http/Controllers/Student/AttendanceController.php`
- `app/Http/Controllers/Student/MarksController.php`
- `app/Http/Controllers/Student/AssignmentsController.php`
- `app/Http/Controllers/Student/TimetableController.php`
- `app/Http/Controllers/Student/DownloadController.php`
- `app/Http/Controllers/Student/NoticesController.php`
- `app/Http/Controllers/Student/ProfileController.php`
- `app/Http/Controllers/Student/SettingsController.php`

### Views:
- `resources/views/student/attendance/index.blade.php`
- `resources/views/student/marks/index.blade.php`
- `resources/views/student/marks/show.blade.php`
- `resources/views/student/assignments/index.blade.php`
- `resources/views/student/assignments/show.blade.php`
- `resources/views/student/timetable/index.blade.php`
- `resources/views/student/downloads/index.blade.php`
- `resources/views/student/notices/index.blade.php`
- `resources/views/student/notices/show.blade.php`
- `resources/views/student/profile/show.blade.php`
- `resources/views/student/profile/edit.blade.php`
- `resources/views/student/settings/index.blade.php`

---

## Estimated Implementation Time

- **Attendance Page**: 2-3 hours
- **Marks/Results Page**: 2-3 hours
- **Assignments Pages**: 3-4 hours (index + show + submission)
- **Timetable Page**: 1-2 hours (reuse existing component)
- **Downloads Page**: 1-2 hours
- **Notices Pages**: 2-3 hours (index + show)
- **Profile Pages**: 2-3 hours (show + edit)
- **Settings Page**: 2-3 hours

**Total**: ~18-26 hours of development time

---

## Testing Checklist

- [ ] All pages load without errors
- [ ] BS dates display correctly
- [ ] Filters work properly
- [ ] Search functionality works
- [ ] Mobile responsive design
- [ ] Data displays correctly for student
- [ ] Forms submit successfully
- [ ] File uploads work
- [ ] Downloads work
- [ ] Proper error handling
- [ ] Loading states
- [ ] Empty states
- [ ] Permission checks

