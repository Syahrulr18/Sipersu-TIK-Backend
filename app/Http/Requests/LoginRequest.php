<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nip'      => 'nullable|string',
            'email'    => 'nullable|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (!$this->nip && !$this->email) {
                $v->errors()->add('nip', 'NIP atau email wajib diisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ];
    }
}
