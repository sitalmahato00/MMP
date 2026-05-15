<?php

namespace App\Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']);
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'password'            => ['required', 'string', 'min:8', 'max:128'],
            'student_no'          => ['required', 'string', 'max:50', 'unique:students,student_no'],
            'registration_number' => ['nullable', 'string', 'max:100', 'unique:students,registration_number'],
            'department_id'       => ['required', 'integer', 'exists:departments,id'],
            'program_id'          => ['required', 'integer', 'exists:programs,id'],
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'current_semester'    => ['required', 'integer', 'min:1', 'max:12'],
            'section'             => ['nullable', 'string', 'max:10'],
            'batch'               => ['nullable', 'string', 'max:20'],
            'admission_date'      => ['nullable', 'date'],
            'guardian_name'       => ['nullable', 'string', 'max:255'],
            'guardian_phone'      => ['nullable', 'string', 'max:20'],
            'blood_group'         => ['nullable', 'string', Rule::in(['A+','A-','B+','B-','AB+','AB-','O+','O-'])],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'               => 'This email is already registered.',
            'student_no.unique'          => 'This student number is already taken.',
            'registration_number.unique' => 'This registration number is already taken.',
        ];
    }
}
