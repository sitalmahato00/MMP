<?php

namespace App\Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff', 'hod']);
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'name'                => ['sometimes', 'string', 'max:255'],
            'email'               => ['sometimes', 'email', 'max:255',
                                      Rule::unique('users', 'email')->ignore($this->route('student')?->user_id)],
            'phone'               => ['nullable', 'string', 'max:20'],
            'password'            => ['nullable', 'string', 'min:8', 'max:128'],
            'avatar'              => ['nullable', 'image', 'max:2048'],
            'student_no'          => ['sometimes', 'string', 'max:50',
                                      Rule::unique('students', 'student_no')->ignore($studentId)],
            'registration_number' => ['nullable', 'string', 'max:100',
                                      Rule::unique('students', 'registration_number')->ignore($studentId)],
            'department_id'       => ['sometimes', 'integer', 'exists:departments,id'],
            'program_id'          => ['sometimes', 'integer', 'exists:programs,id'],
            'academic_session_id' => ['sometimes', 'integer', 'exists:academic_sessions,id'],
            'current_semester'    => ['sometimes', 'integer', 'min:1', 'max:12'],
            'section'             => ['nullable', 'string', 'max:10'],
            'batch'               => ['nullable', 'string', 'max:20'],
            'admission_date'      => ['nullable', 'date'],
            'guardian_name'       => ['nullable', 'string', 'max:255'],
            'guardian_phone'      => ['nullable', 'string', 'max:20'],
            'blood_group'         => ['nullable', 'string', Rule::in(['A+','A-','B+','B-','AB+','AB-','O+','O-'])],
            'status'              => ['nullable', 'string', Rule::in(['active','inactive','graduated','suspended','transferred'])],
        ];
    }
}
