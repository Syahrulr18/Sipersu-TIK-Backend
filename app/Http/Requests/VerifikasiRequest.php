<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'aksi'    => 'required|in:setuju,tolak',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'aksi.required' => 'Aksi verifikasi wajib dipilih.',
            'aksi.in'       => 'Aksi harus setuju atau tolak.',
        ];
    }
}
