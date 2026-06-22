<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nomor_surat'  => $this->nomor_surat,
            'hal'          => $this->hal,
            'ringkasan'    => $this->ringkasan,
            'status'       => $this->status,
            'tanggal_terbit' => $this->tanggal_terbit?->format('d M Y'),
            'created_at'   => $this->created_at?->format('d M Y'),
            'kode_hal'     => $this->whenLoaded('kodeHal', fn() => [
                'kode' => $this->kodeHal->kode,
                'nama' => $this->kodeHal->nama,
            ]),
            'dibuat_oleh'  => $this->whenLoaded('pembuatOleh', fn() => [
                'id'           => $this->pembuatOleh->id,
                'nama_lengkap' => $this->pembuatOleh->nama_lengkap,
            ]),
            'jumlah_lampiran' => $this->lampiran_count ?? 0,
        ];
    }
}
