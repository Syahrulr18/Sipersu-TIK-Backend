<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKontenPenerima extends Model
{
    protected $table = 'surat_konten_penerima';

    protected $fillable = [
        'surat_id',
        'penerima_user_id',
        'konten_html',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_user_id');
    }
}
