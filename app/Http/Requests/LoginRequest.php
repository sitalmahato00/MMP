<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
            ],
            'otp' => [
                'nullable',
                'string',
                'digits:6',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'email.exists' => 'No account found with this email address',
            'password.min' => 'Password must be at least 6 characters',
            'otp.digits' => 'OTP must be exactly 6 digits',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('password') && !$this->filled('otp')) {
                $validator->errors()->add('auth', 'Either password or OTP is required');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
