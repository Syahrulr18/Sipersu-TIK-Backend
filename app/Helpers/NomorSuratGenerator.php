<?php

namespace App\Helpers;

use App\Models\NomorSuratCounter;
use App\Models\Surat;
use Illuminate\Support\Facades\DB;

class NomorSuratGenerator
{
    /**
     * Generate nomor surat resmi dengan format M.[URUTAN]/9/[KODE_HAL]/[TAHUN]
     * Menggunakan DB::transaction + lockForUpdate untuk mencegah race condition.
     */
    public static function generate(Surat $surat): string
    {
        return DB::transaction(function () use ($surat) {
            $tahun = date('Y');

            // Lock row untuk mencegah concurrent access
            $counter = NomorSuratCounter::where('tahun', $tahun)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = NomorSuratCounter::create([
                    'tahun'           => $tahun,
                    'counter_terakhir' => 0,
                ]);
                // Lock again after insert
                $counter = NomorSuratCounter::where('tahun', $tahun)
                    ->lockForUpdate()
                    ->first();
            }

            $nomorUrut = $counter->counter_terakhir + 1;

            // Update counter
            NomorSuratCounter::where('tahun', $tahun)->update([
                'counter_terakhir' => $nomorUrut,
                'updated_at'       => now(),
            ]);

            $kodeHal = $surat->kodeHal->kode ?? 'XX';
            $nomorFormatted = str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);

            $nomor = sprintf(
                '%s.%s/%s/%s/%s',
                config('surat.prefix', 'M'),
                $nomorFormatted,
                config('surat.kode_unit', '9'),
                $kodeHal,
                $tahun
            );

            // Simpan ke surat
            $surat->update([
                'nomor_urut'   => $nomorUrut,
                'nomor_surat'  => $nomor,
                'tanggal_terbit' => now()->toDateString(),
            ]);

            return $nomor;
        });
    }
}
