<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'jabatan'      => 'nullable|string|max:255',
            'jurusan'      => 'nullable|string|max:255',
        ];
    }
}
