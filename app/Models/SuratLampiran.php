<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratLampiran extends Model
{
    public $timestamps = false;

    protected $table = 'surat_lampiran';

    protected $fillable = [
        'surat_id',
        'nama_file_asli',
        'nama_file_sistem',
        'path',
        'ukuran_bytes',
        'jumlah_halaman',
        'mime_type',
    ];

    // ── Accessor ─────────────────────────────────────────────────────────────
    public function getUkuranFormattedAttribute(): string
    {
        $bytes = $this->ukuran_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    // ── Relations ────────────────────────────────────────────────────────────
    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
