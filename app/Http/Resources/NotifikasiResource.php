<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotifikasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'judul'      => $this->judul,
            'pesan'      => $this->pesan,
            'tipe'       => $this->tipe,
            'is_read'    => $this->is_read,
            'surat_id'   => $this->surat_id,
            'created_at' => $this->created_at?->diffForHumans(),
        ];
    }
}
