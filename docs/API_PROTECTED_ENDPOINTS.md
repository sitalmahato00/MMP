# MMP Mobile App API Documentation - Protected Endpoints

## Overview

This document provides comprehensive documentation for all protected (authenticated) endpoints in the MMP Academic Management Portal Mobile API. All endpoints require Bearer token authentication provided by Laravel Sanctum after user login.

**Current Date:** June 5, 2026  
**API Version:** v1  
**Last Updated:** June 5, 2026

---

## Base URL

- **Development:** `http://localhost:8000/api`
- **Staging:** `https://staging.mmp.edu.np/api`
- **Production:** `https://api.mmp.edu.np/api` *(to be deployed)*

---

## Authentication

### Request Headers

All authenticated endpoints require:

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Obtaining Token

**Endpoint:** `POST /auth/login`

**Rate Limit:** 3 requests per minute

**Request:**
```json
{
  "email": "student@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "student@example.com",
      "role": "student",
      "avatar_url": "https://..."
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

---

## Error Handling

### Common HTTP Status Codes

- **200 OK** - Successful request
- **201 Created** - Resource created successfully
- **400 Bad Request** - Invalid input or validation error
- **401 Unauthorized** - Missing or invalid authentication token
- **403 Forbidden** - Insufficient permissions for this role
- **404 Not Found** - Resource not found
- **429 Too Many Requests** - Rate limit exceeded
- **500 Internal Server Error** - Server error

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

# API ENDPOINTS BY ROLE

---

## STUDENT ENDPOINTS

**Role Requirement:** `student`

Base path: `/api/v1/student/`

### Dashboard

**GET** `/dashboard`

Returns KPI cards with attendance %, average marks, pending assignments, and unread notices.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "student_name": "John Doe",
    "student_id": 1,
    "program": "BIT",
    "semester": 4,
    "kpi_cards": {
      "attendance_percentage": 85.5,
      "average_marks": 78.3,
      "pending_assignments": 2,
      "unread_notices": 5
    }
  }
}
```

---

### Attendance

**GET** `/attendance/summary`

Get overall attendance summary.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_classes": 45,
    "present": 38,
    "absent": 5,
    "late": 2,
    "attendance_percentage": 84.44,
    "status": "good"
  }
}
```

**GET** `/attendance/detail`

Get detailed attendance records (paginated).

**Query Parameters:**
- `page` (integer, default: 1)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "subject": "Programming Fundamentals",
      "date": "2026-06-04",
      "status": "present",
      "session": "Class 1"
    }
  ]
}
```

**GET** `/attendance/by-subject/{subject}`

Get attendance for specific subject.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "subject": "Programming Fundamentals",
    "total_classes": 20,
    "present": 18,
    "absent": 1,
    "late": 1,
    "attendance_percentage": 90.0
  }
}
```

---

### Marks

**GET** `/marks/summary`

Get overall marks summary across all exams.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "average_marks": 78.5,
    "total_exams": 6,
    "exams": [
      {
        "exam_id": 1,
        "exam_name": "Midterm Exam",
        "total_marks": 100,
        "obtained_marks": 82,
        "percentage": 82.0
      }
    ]
  }
}
```

**GET** `/marks/exam/{exam}`

Get marks for specific exam (subject-wise).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "subject": "Programming Fundamentals",
      "obtained_marks": 82,
      "total_marks": 100,
      "percentage": 82.0
    }
  ]
}
```

**GET** `/marks/subject/{subject}`

Get all marks for specific subject.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "subject": "Programming Fundamentals",
    "marks": [
      {
        "exam": "Midterm",
        "obtained_marks": 82,
        "total_marks": 100
      }
    ]
  }
}
```

**GET** `/marks/marksheet`

Get marksheet download link (PDF).

**Response (200):**
```json
{
  "success": true,
  "message": "Marksheet download link generated",
  "data": {
    "download_url": "/api/v1/student/marksheet-pdf?token=..."
  }
}
```

---

### Subjects

**GET** `/subjects`

Get list of enrolled subjects.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Programming Fundamentals",
      "code": "CS101"
    }
  ]
}
```

---

### Assignments

**GET** `/assignments`

Get all assignments (paginated, 10 per page).

**Query Parameters:**
- `page` (integer, default: 1)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Create a Calculator App",
      "subject": "Programming Fundamentals",
      "description": "Create a basic calculator...",
      "due_date": "2026-06-15",
      "max_marks": 10,
      "status": "pending"
    }
  ]
}
```

**GET** `/assignments/{assignment}`

Get assignment details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Create a Calculator App",
    "subject": "Programming Fundamentals",
    "description": "Create a basic calculator...",
    "due_date": "2026-06-15",
    "max_marks": 10,
    "attachment_url": "https://..."
  }
}
```

**POST** `/assignments/{assignment}/submit`

Submit assignment with file upload or text content.

**Request:**
```json
{
  "content": "My submission content here",
  "file": "<binary_file_data>"
}
```

**Note:** Send `multipart/form-data` with file upload

**Response (201):**
```json
{
  "success": true,
  "message": "Assignment submitted successfully",
  "data": {
    "submission_id": 456,
    "submitted_at": "2026-06-10T14:30:00Z"
  }
}
```

**GET** `/assignments/{submission}/submission-status`

Check submission status and grades.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "status": "graded",
    "submitted_at": "2026-06-10T14:30:00Z",
    "graded_at": "2026-06-11T10:00:00Z",
    "marks_obtained": 9,
    "max_marks": 10,
    "feedback": "Great work!"
  }
}
```

---

### Timetable

**GET** `/timetable`

Get complete weekly timetable.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "timetable": []
  }
}
```

**GET** `/timetable/{day}`

Get timetable for specific day (e.g., "Monday", "Tuesday").

**Response (200):**
```json
{
  "success": true,
  "data": {
    "day": "Monday",
    "classes": []
  }
}
```

---

### Downloads

**GET** `/downloads`

Get list of study materials and downloadable files (paginated, 10 per page).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Chapter 1 - Introduction",
      "description": "PDF slides for chapter 1",
      "file_url": "https://...",
      "uploaded_at": "2026-06-01T10:00:00Z"
    }
  ]
}
```

**GET** `/downloads/{download}/file`

Get download file link.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "file_url": "https://...",
    "file_name": "Chapter 1 - Introduction"
  }
}
```

---

### Notices

**GET** `/notices`

Get all active notices (paginated, 10 per page).

**Query Parameters:**
- `page` (integer, default: 1)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Exam Schedule Announced",
      "description": "Final exams will be held...",
      "category": "Exam",
      "published_at": "2026-06-01T09:00:00Z"
    }
  ]
}
```

**GET** `/notices/{notice}`

Get notice details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Exam Schedule Announced",
    "description": "Final exams will be held...",
    "category": "Exam",
    "attachments": [],
    "published_at": "2026-06-01T09:00:00Z"
  }
}
```

**GET** `/notices/filter/{category}`

Get notices by category (General, Exam, Event, Department).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Exam Notice",
      "description": "...",
      "published_at": "2026-06-01T09:00:00Z"
    }
  ]
}
```

---

### Profile

**GET** `/profile`

Get student profile details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "9841234567",
    "avatar_url": "https://...",
    "student_id": 1,
    "program": "BIT",
    "semester": 4,
    "roll_number": "BIT-2022-001"
  }
}
```

---

## TEACHER ENDPOINTS

**Role Requirement:** `teacher`

Base path: `/api/v1/teacher/`

### Dashboard

**GET** `/dashboard`

Returns teacher dashboard with KPI cards.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "teacher_name": "Jane Smith",
    "total_classes": 15,
    "total_students": 120,
    "pending_marks": 25,
    "pending_assignments": 3
  }
}
```

---

### Attendance Management

**GET** `/today-schedule`

Get today's class schedule.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "today": "2026-06-05",
    "classes": []
  }
}
```

**GET** `/attendance/session/{session}`

Get students in attendance session for marking.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "students": []
  }
}
```

**POST** `/attendance/mark`

Mark individual attendance.

**Request:**
```json
{
  "student_id": 1,
  "subject_id": 1,
  "status": "present",
  "date": "2026-06-05"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Attendance marked successfully"
}
```

**POST** `/attendance/bulk-mark`

Mark attendance for multiple students at once.

**Request:**
```json
{
  "attendance": [
    {
      "student_id": 1,
      "status": "present"
    },
    {
      "student_id": 2,
      "status": "absent"
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "2 attendance records created",
  "data": {
    "records_created": 2
  }
}
```

**GET** `/attendance/history`

Get attendance history (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": "John Doe",
      "subject": "Programming Fundamentals",
      "status": "present",
      "date": "2026-06-04"
    }
  ]
}
```

---

### Marks Entry

**GET** `/marks/components/{subject}`

Get mark components for subject (Internal Theory, External Theory, etc.).

**Response (200):**
```json
{
  "success": true,
  "data": {
    "components": [
      "internal_theory",
      "external_theory",
      "internal_practical",
      "external_practical"
    ]
  }
}
```

**POST** `/marks/submit`

Submit marks for a student in an exam.

**Request:**
```json
{
  "student_id": 1,
  "exam_id": 1,
  "subject_id": 1,
  "obtained_marks": 82
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Marks submitted successfully"
}
```

**GET** `/marks/pending`

Get list of pending mark entries.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "pending_marks": []
  }
}
```

**GET** `/marks/history`

Get marks submission history.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "marks_history": []
  }
}
```

---

### Assignment Management

**GET** `/assignments`

Get all assignments created by teacher (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Create a Calculator App",
      "subject": "Programming Fundamentals",
      "due_date": "2026-06-15",
      "created_at": "2026-06-01"
    }
  ]
}
```

**POST** `/assignments/create`

Create new assignment.

**Request:**
```json
{
  "title": "Create a Calculator App",
  "description": "Create a basic calculator in Python",
  "subject_id": 1,
  "due_date": "2026-06-15",
  "max_marks": 10
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "assignment_id": 1
  }
}
```

**PUT** `/assignments/{assignment}`

Update assignment details.

**Request:**
```json
{
  "title": "Updated Title",
  "due_date": "2026-06-20",
  "max_marks": 15
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Assignment updated successfully"
}
```

**DELETE** `/assignments/{assignment}`

Delete assignment.

**Response (200):**
```json
{
  "success": true,
  "message": "Assignment deleted successfully"
}
```

**GET** `/assignments/{assignment}/submissions`

Get all submissions for assignment (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": "John Doe",
      "status": "graded",
      "submitted_at": "2026-06-10"
    }
  ]
}
```

**POST** `/assignments/{submission}/grade`

Grade a student submission.

**Request:**
```json
{
  "marks_obtained": 9,
  "feedback": "Great work, well done!"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Submission graded successfully"
}
```

---

### Student & Section Management

**GET** `/students`

Get list of all students (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roll_number": "BIT-2022-001"
    }
  ]
}
```

**GET** `/students/{subject}`

Get students of specific subject.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "students": []
  }
}
```

---

### Reports

**GET** `/reports/attendance`

Get attendance report.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "report": []
  }
}
```

**GET** `/reports/marks`

Get marks report.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "report": []
  }
}
```

---

## PARENT ENDPOINTS

**Role Requirement:** `parent`

Base path: `/api/v1/parent/`

### Dashboard

**GET** `/dashboard`

Get parent dashboard with children overview.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "children_count": 2,
    "children": [
      {
        "id": 1,
        "name": "John Jr.",
        "program": "BIT"
      }
    ]
  }
}
```

---

### Children Management

**GET** `/children`

Get list of registered children.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Jr.",
      "email": "john.jr@example.com",
      "roll_number": "BIT-2022-001",
      "program": "BIT",
      "semester": 4
    }
  ]
}
```

**GET** `/children/{child}`

Get specific child's profile.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Jr.",
    "email": "john.jr@example.com",
    "roll_number": "BIT-2022-001",
    "program": "BIT",
    "semester": 4,
    "phone": "9841234567"
  }
}
```

---

### Child Attendance Monitoring

**GET** `/child/{child}/attendance`

Get child's attendance records (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "subject": "Programming Fundamentals",
      "status": "present",
      "date": "2026-06-04"
    }
  ]
}
```

**GET** `/child/{child}/attendance/summary`

Get child's attendance summary.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_classes": 45,
    "present": 38,
    "absent": 5,
    "late": 2,
    "attendance_percentage": 84.44,
    "status": "good"
  }
}
```

**GET** `/child/{child}/attendance/by-subject/{subject}`

Get child's attendance for specific subject.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_classes": 20,
    "present": 18,
    "attendance_percentage": 90.0
  }
}
```

---

### Child Marks Monitoring

**GET** `/child/{child}/marks`

Get child's marks (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "subject": "Programming Fundamentals",
      "exam": "Midterm",
      "obtained_marks": 82,
      "total_marks": 100
    }
  ]
}
```

**GET** `/child/{child}/marks/summary`

Get child's marks summary.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "average_marks": 78.5,
    "total_exams": 6
  }
}
```

**GET** `/child/{child}/marks/exam/{exam}`

Get child's marks for specific exam.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "subject": "Programming Fundamentals",
      "obtained_marks": 82,
      "total_marks": 100,
      "percentage": 82.0
    }
  ]
}
```

**GET** `/child/{child}/marks/marksheet`

Get child's marksheet download link.

**Response (200):**
```json
{
  "success": true,
  "message": "Marksheet download link generated",
  "data": {
    "download_url": "/api/v1/parent/child/1/marksheet-pdf"
  }
}
```

---

### Child Assignment Monitoring

**GET** `/child/{child}/assignments`

Get child's assignments (paginated).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Create a Calculator App",
      "subject": "Programming Fundamentals",
      "due_date": "2026-06-15",
      "status": "pending"
    }
  ]
}
```

**GET** `/child/{child}/assignments/{assignment}`

Get specific assignment details for child.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Create a Calculator App",
    "description": "Create a basic calculator in Python",
    "due_date": "2026-06-15",
    "status": "pending"
  }
}
```

---

### Notices

**GET** `/notices`

Get all notices relevant to parent.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Exam Schedule",
      "description": "Final exams will be held...",
      "category": "Exam",
      "published_at": "2026-06-01"
    }
  ]
}
```

**GET** `/notices/{notice}`

Get notice details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Exam Schedule",
    "description": "Final exams will be held...",
    "category": "Exam",
    "published_at": "2026-06-01"
  }
}
```

---

### Child Timetable

**GET** `/child/{child}/timetable`

Get child's weekly timetable.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "timetable": []
  }
}
```

---

## HOD ENDPOINTS

**Role Requirement:** `hod`

Base path: `/api/v1/hod/`

### Dashboard & Overview

**GET** `/dashboard`

Get HOD dashboard.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "department": "Information Technology",
    "total_students": 120,
    "total_teachers": 8,
    "total_subjects": 12
  }
}
```

**GET** `/department`

Get department overview.

**GET** `/statistics`

Get department statistics (avg attendance, avg marks, etc.).

---

### Student Management

**GET** `/students`

Get all department students (paginated).

**GET** `/students/{student}`

Get student details.

**GET** `/students/{student}/attendance`

Get student's attendance summary.

**GET** `/students/{student}/marks`

Get student's marks and average.

---

### Teacher Management

**GET** `/teachers`

Get all department teachers (paginated).

**GET** `/teachers/{teacher}`

Get teacher details.

**GET** `/teachers/{teacher}/subjects`

Get subjects taught by teacher.

---

### Subject Management

**GET** `/subjects`

Get all department subjects (paginated).

**GET** `/subjects/{subject}`

Get subject details.

---

### Reports

**GET** `/reports/attendance`

Get department attendance report.

**GET** `/reports/marks`

Get department marks report.

**GET** `/reports/performance`

Get department performance report.

**GET** `/reports/assignments`

Get assignment submission report.

---

## ALUMNI ENDPOINTS

**Role Requirement:** `alumni`

Base path: `/api/v1/alumni/`

### Dashboard

**GET** `/dashboard`

Get alumni dashboard.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "alumni_id": 1,
    "name": "John Graduated",
    "graduation_year": 2023,
    "program": "BIT"
  }
}
```

---

### Academic Records

**GET** `/records/marksheets`

Get all marksheets (paginated).

**GET** `/records/marksheet/{marksheet}`

Get specific marksheet details.

**GET** `/records/transcripts`

Get all transcripts.

**GET** `/records/transcript/{transcript}`

Get specific transcript.

---

### Documents

**GET** `/documents`

Get list of downloadable documents.

**GET** `/documents/{document}`

Get document details.

**POST** `/documents/{document}/download`

Get download link for document.

---

### Alumni Network

**GET** `/notices`

Get alumni-specific notices.

**GET** `/alumni-list`

Get list of all alumni (paginated).

---

### Profile

**GET** `/profile`

Get alumni profile.

**PUT** `/profile`

Update alumni profile (company, position, etc.).

---

# AUTHENTICATION ENDPOINTS

---

## Login

**POST** `/auth/login`

Authenticate user with email and password.

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "student",
      "avatar_url": "https://..."
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## Verify OTP (2FA)

**POST** `/auth/verify-otp`

Verify one-time password for two-factor authentication.

**Request:**
```json
{
  "email": "user@example.com",
  "otp": "123456"
}
```

---

## Logout

**POST** `/auth/logout`

Logout and invalidate current token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## Get Current User

**GET** `/user`

Get authenticated user's profile.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "avatar_url": "https://..."
  }
}
```

---

## Update Profile

**PUT** `/user/profile`

Update user profile information.

**Request:**
```json
{
  "name": "John Updated",
  "phone": "9841234567"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

---

## Change Password

**POST** `/user/change-password`

Change user password.

**Request:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword456"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

## Update Notification Preferences

**PUT** `/user/notification-preferences`

Update user notification settings.

**Request:**
```json
{
  "email_notifications": true,
  "push_notifications": true,
  "sms_notifications": false
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Preferences updated successfully"
}
```

---

# IMPLEMENTATION NOTES

## Rate Limiting

- **Authentication endpoints:** 3 requests per minute (throttle:3,1)
- **Public endpoints:** Rate limited per IP
- **Authenticated endpoints:** No strict limit, but reasonable usage expected
- **Response Header:** `X-RateLimit-Remaining` shows remaining requests

---

## Token Management

1. **Token Validity:** Tokens do not expire by default in Sanctum (configurable)
2. **Token Refresh:** Use `/auth/refresh-token` to get a new token
3. **Token Revocation:** Calling `/auth/logout` revokes the token
4. **Multiple Tokens:** User can have multiple active tokens (different devices)

---

## Pagination

Paginated endpoints return:

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 100,
    "count": 10,
    "per_page": 10,
    "current_page": 1,
    "total_pages": 10
  }
}
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Records per page (default: 10, max: 50)

---

## File Upload

For endpoints accepting file uploads (assignments, etc.):

**Request Type:** `multipart/form-data`

**Example (cURL):**
```bash
curl -X POST https://api.mmp.edu.np/api/v1/student/assignments/1/submit \
  -H "Authorization: Bearer {token}" \
  -F "content=My solution" \
  -F "file=@solution.pdf"
```

---

## CORS Policy

All API endpoints are accessible from mobile clients. CORS headers are properly configured for cross-origin requests.

---

## Security Best Practices

1. **Always use HTTPS** in production
2. **Never expose tokens** in logs or error messages
3. **Validate all inputs** on client side before sending
4. **Store tokens securely** using EncryptedSharedPreferences (Android)
5. **Refresh tokens** regularly during long sessions
6. **Clear tokens** when logging out

---

## Support & Contact

For API issues or questions:
- **Email:** api.support@mmp.edu.np
- **Documentation:** https://docs.api.mmp.edu.np
- **Status Page:** https://status.mmp.edu.np

---

**Last Updated:** June 5, 2026  
**Version:** 1.0
