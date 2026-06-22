<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'nomor_urut'         => $this->nomor_urut,
            'nomor_surat'        => $this->nomor_surat,
            'hal'                => $this->hal,
            'ringkasan'          => $this->ringkasan,
            'konten_html'        => $this->konten_html,
            'status'             => $this->status,
            'catatan_penolakan'  => $this->catatan_penolakan,
            'tanggal_terbit'     => $this->tanggal_terbit?->format('d M Y'),
            'created_at'         => $this->created_at?->format('d M Y H:i'),
            'created_at_date'    => $this->created_at?->format('d M Y'),
            'updated_at'         => $this->updated_at?->format('d M Y H:i'),

            'kode_hal' => $this->whenLoaded('kodeHal', fn() => [
                'id'       => $this->kodeHal->id,
                'kode'     => $this->kodeHal->kode,
                'nama'     => $this->kodeHal->nama,
                'kategori' => $this->kodeHal->kategori,
            ]),

            'penanda_tangan' => $this->whenLoaded('penandaTangan', fn() => [
                'id'           => $this->penandaTangan->id,
                'nama_lengkap' => $this->penandaTangan->nama_lengkap,
                'jabatan'      => $this->penandaTangan->jabatan,
                'nip'          => $this->penandaTangan->nip,
                'ttd_url'      => $this->penandaTangan->ttd ? url('storage/' . $this->penandaTangan->ttd) : null,
            ]),

            'verifikator' => $this->whenLoaded('verifikator', fn() => [
                'id'           => $this->verifikator->id,
                'nama_lengkap' => $this->verifikator->nama_lengkap,
                'jabatan'      => $this->verifikator->jabatan,
            ]),

            'dibuat_oleh' => $this->whenLoaded('pembuatOleh', fn() => [
                'id'           => $this->pembuatOleh->id,
                'nama_lengkap' => $this->pembuatOleh->nama_lengkap,
                'role'         => $this->pembuatOleh->role,
            ]),

            'penerima' => $this->whenLoaded('penerima', function() {
                $user = auth()->user();
                $penerima = $this->penerima;
                
                if ($user && strtolower($user->role) === 'dosen') {
                    $penerima = $penerima->filter(fn($u) => $u->id === $user->id);
                }
                
                return $penerima->values()->map(function($u) {
                    $kontenSpesifik = null;
                    if ($this->relationLoaded('kontenPenerima')) {
                        $kontenSpesifik = $this->kontenPenerima->firstWhere('penerima_user_id', $u->id)?->konten_html;
                    }
                    
                    return [
                        'id'           => $u->id,
                        'nama_lengkap' => $u->nama_lengkap,
                        'jabatan'      => $u->jabatan,
                        'konten_html'  => $kontenSpesifik,
                    ];
                });
            }),

            'lampiran' => $this->whenLoaded('lampiran', fn() =>
                $this->lampiran->map(fn($l) => [
                    'id'             => $l->id,
                    'nama_file_asli' => $l->nama_file_asli,
                    'ukuran'         => $l->ukuran_formatted,
                    'jumlah_halaman' => $l->jumlah_halaman,
                    'mime_type'      => $l->mime_type,
                    'download_url'   => route('surat.lampiran.download', [
                        'id'  => $this->id,
                        'lid' => $l->id,
                    ]),
                ])
            ),

            'log' => $this->whenLoaded('log', fn() =>
                $this->log->map(fn($entry) => [
                    'id'             => $entry->id,
                    'status_sebelum' => $entry->status_sebelum,
                    'status_sesudah' => $entry->status_sesudah,
                    'catatan'        => $entry->catatan,
                    'created_at'     => $entry->created_at?->format('d M Y H:i'),
                    'user' => $entry->user ? [
                        'id'           => $entry->user->id,
                        'nama_lengkap' => $entry->user->nama_lengkap,
                        'role'         => $entry->user->role,
                    ] : null,
                ])
            ),
        ];
    }
}
