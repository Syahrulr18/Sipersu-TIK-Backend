<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surat';

    protected $fillable = [
        'nomor_urut',
        'nomor_surat',
        'penanda_tangan_id',
        'verifikator_id',
        'kode_hal_id',
        'hal',
        'ringkasan',
        'konten_html',
        'status',
        'catatan_penolakan',
        'dibuat_oleh',
        'tanggal_terbit',
        'file_pdf_path',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
    ];

    // ── Status Constants ─────────────────────────────────────────────────────
    const STATUS_DRAFT                = 'draft';
    const STATUS_MENUNGGU_VERIFIKASI  = 'menunggu_verifikasi';
    const STATUS_DIVERIFIKASI         = 'diverifikasi';
    const STATUS_DITOLAK              = 'ditolak';
    const STATUS_TERBIT               = 'terbit';

    // ── Accessors ────────────────────────────────────────────────────────────
    public function getJumlahPenerimaAttribute(): int
    {
        return $this->penerima()->count();
    }

    public function getJumlahLampiranAttribute(): int
    {
        return $this->lampiran()->count();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Relations ────────────────────────────────────────────────────────────
    public function penandaTangan()
    {
        return $this->belongsTo(User::class, 'penanda_tangan_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function kodeHal()
    {
        return $this->belongsTo(KodeHal::class, 'kode_hal_id');
    }

    public function pembuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penerima()
    {
        return $this->belongsToMany(User::class, 'surat_penerima');
    }

    public function lampiran()
    {
        return $this->hasMany(SuratLampiran::class);
    }

    public function log()
    {
        return $this->hasMany(SuratLog::class)->orderByDesc('created_at');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function kontenPenerima()
    {
        return $this->hasMany(SuratKontenPenerima::class);
    }
}
