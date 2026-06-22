<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'nip'          => 'nullable|string|max:30|unique:users,nip',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:administrator,verifikator,kajur,dosen',
            'jabatan'      => 'nullable|string|max:255',
            'jurusan'      => 'nullable|string|max:255',
        ];
    }
}
