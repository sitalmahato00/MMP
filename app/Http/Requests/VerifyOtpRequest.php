<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10,15}$/',
            ],
            'otp' => [
                'required',
                'string',
                'digits:6',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required',
            'phone.regex' => 'Phone number must be between 10-15 digits',
            'otp.required' => 'OTP is required',
            'otp.digits' => 'OTP must be exactly 6 digits',
        ];
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
