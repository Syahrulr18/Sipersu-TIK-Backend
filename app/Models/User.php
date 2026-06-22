<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'role',
        'nip',
        'jabatan',
        'jurusan',
        'foto',
        'ttd',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password'  => 'hashed',
    ];

    // ── JWT ──────────────────────────────────────────────────────────────────
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }

    // ── Accessors ────────────────────────────────────────────────────────────
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        return Storage::disk('public')->url($this->foto);
    }

    public function getTtdUrlAttribute(): ?string
    {
        if (!$this->ttd) return null;
        return Storage::disk('public')->url($this->ttd);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // ── Relations ────────────────────────────────────────────────────────────
    public function suratDibuat()
    {
        return $this->hasMany(Surat::class, 'dibuat_oleh');
    }

    public function suratDitandatangani()
    {
        return $this->hasMany(Surat::class, 'penanda_tangan_id');
    }

    public function suratDiverifikasi()
    {
        return $this->hasMany(Surat::class, 'verifikator_id');
    }

    public function suratDiterima()
    {
        return $this->belongsToMany(Surat::class, 'surat_penerima');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function suratLog()
    {
        return $this->hasMany(SuratLog::class);
    }
}
