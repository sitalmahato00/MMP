# Fix Attendance Authorization Issue

## Problem
You're seeing "You are not authorized to view this attendance record" because old attendance records in the database have incorrect `teacher_id` values.

## Solution: Run the SQL Fix Script

### Step 1: Open your database client
- phpMyAdmin
- MySQL Workbench
- Or any SQL client

### Step 2: Run this SQL query

```sql
-- Fix attendance sessions with incorrect teacher_id
UPDATE attendance_sessions a
SET teacher_id = (
    SELECT teacher_id 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.academic_session_id = a.academic_session_id
    LIMIT 1
)
WHERE EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.academic_session_id = a.academic_session_id
);
```

### Step 3: Verify the fix

```sql
-- Check if there are any remaining invalid sessions
SELECT COUNT(*) as invalid_sessions
FROM attendance_sessions a
WHERE NOT EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.teacher_id = a.teacher_id
    AND st.academic_session_id = a.academic_session_id
);
```

If the result is 0, all sessions are fixed!

### Step 4: Delete orphaned sessions (optional)

If there are attendance sessions with no valid teacher, delete them:

```sql
-- Delete sessions that have no valid teacher
DELETE FROM attendance_sessions 
WHERE id IN (
    SELECT a.id
    FROM (
        SELECT id, subject_id, teacher_id, academic_session_id
        FROM attendance_sessions
    ) a
    WHERE NOT EXISTS (
        SELECT 1 
        FROM subject_teacher st 
        WHERE st.subject_id = a.subject_id 
        AND st.teacher_id = a.teacher_id
        AND st.academic_session_id = a.academic_session_id
    )
);
```

## Alternative: Fresh Start (Development Only)

If you're in development and can reset the database:

```bash
php artisan migrate:fresh --seed
```

## After Running the Fix

1. Refresh your browser
2. Login as a teacher
3. Go to Attendance Management
4. You should now be able to view and edit all your attendance records

## What Changed in the Code

The authorization now works like this:
- ✅ Teacher can ONLY view/edit/delete attendance sessions they created (`teacher_id` matches)
- ❌ Teacher CANNOT view/edit/delete attendance created by other teachers
- ✅ Index page shows only attendance sessions created by the logged-in teacher
