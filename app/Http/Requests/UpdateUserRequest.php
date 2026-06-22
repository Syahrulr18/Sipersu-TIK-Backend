<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('id');
        return [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => "required|email|unique:users,email,{$userId}",
            'nip'          => "nullable|string|max:30|unique:users,nip,{$userId}",
            'role'         => 'required|in:administrator,verifikator,kajur,dosen',
            'jabatan'      => 'nullable|string|max:255',
            'jurusan'      => 'nullable|string|max:255',
        ];
    }
}
