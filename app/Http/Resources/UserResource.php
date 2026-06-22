<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nama_lengkap' => $this->nama_lengkap,
            'email'        => $this->email,
            'role'         => $this->role,
            'nip'          => $this->nip,
            'jabatan'      => $this->jabatan,
            'jurusan'      => $this->jurusan,
            'foto_url'     => $this->foto_url,
            'ttd_url'      => $this->ttd_url,
            'is_active'    => $this->is_active,
        ];
    }
}
