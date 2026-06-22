<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorSuratCounter extends Model
{
    protected $table = 'nomor_surat_counter';

    public $timestamps = false;

    protected $fillable = ['tahun', 'counter_terakhir'];
}
