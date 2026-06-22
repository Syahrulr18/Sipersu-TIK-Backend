<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'surat_id',
        'judul',
        'pesan',
        'tipe',
        'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
