-- Fix Attendance Session Teacher IDs
-- This script fixes attendance sessions with incorrect teacher_id values
-- Run this in your database client (phpMyAdmin, MySQL Workbench, etc.)

-- Step 1: Check how many sessions need fixing
SELECT COUNT(*) as sessions_needing_fix
FROM attendance_sessions a
WHERE NOT EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.teacher_id = a.teacher_id
);

-- Step 2: Preview which sessions will be updated
SELECT 
    a.id,
    a.subject_id,
    s.name as subject_name,
    a.teacher_id as old_teacher_id,
    (SELECT teacher_id FROM subject_teacher st WHERE st.subject_id = a.subject_id LIMIT 1) as new_teacher_id
FROM attendance_sessions a
JOIN subjects s ON a.subject_id = s.id
WHERE NOT EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.teacher_id = a.teacher_id
);

-- Step 3: Update sessions with a valid teacher (if one exists)
UPDATE attendance_sessions a
SET teacher_id = (
    SELECT teacher_id 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    LIMIT 1
)
WHERE NOT EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.teacher_id = a.teacher_id
)
AND EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id
);

-- Step 4: Delete orphaned sessions (no valid teacher exists for the subject)
DELETE FROM attendance_sessions 
WHERE id IN (
    SELECT a.id
    FROM (
        SELECT id, subject_id, teacher_id
        FROM attendance_sessions
    ) a
    WHERE NOT EXISTS (
        SELECT 1 
        FROM subject_teacher st 
        WHERE st.subject_id = a.subject_id 
        AND st.teacher_id = a.teacher_id
    )
    AND NOT EXISTS (
        SELECT 1 
        FROM subject_teacher st 
        WHERE st.subject_id = a.subject_id
    )
);

-- Step 5: Verify all sessions now have valid teacher_id
SELECT COUNT(*) as remaining_invalid_sessions
FROM attendance_sessions a
WHERE NOT EXISTS (
    SELECT 1 
    FROM subject_teacher st 
    WHERE st.subject_id = a.subject_id 
    AND st.teacher_id = a.teacher_id
);

-- If the result is 0, all sessions are now fixed!
