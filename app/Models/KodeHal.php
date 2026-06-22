<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KodeHal extends Model
{
    protected $table = 'kode_hal';

    protected $fillable = ['kode', 'nama', 'kategori', 'deskripsi', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
