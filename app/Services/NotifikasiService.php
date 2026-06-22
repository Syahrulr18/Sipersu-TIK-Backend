<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Surat;
use App\Models\User;

class NotifikasiService
{
    /**
     * Kirim notifikasi ke verifikator saat surat disubmit.
     */
    public static function kirimKeVerifikator(Surat $surat): void
    {
        self::buat($surat->verifikator_id, $surat->id, [
            'judul' => 'Surat Baru Menunggu Verifikasi',
            'pesan' => "Surat \"{$surat->hal}\" dari {$surat->pembuatOleh->nama_lengkap} menunggu verifikasi Anda.",
            'tipe'  => 'verifikasi',
        ]);
    }

    /**
     * Kirim notifikasi ke kajur saat surat diverifikasi.
     */
    public static function kirimKeKajur(Surat $surat): void
    {
        self::buat($surat->penanda_tangan_id, $surat->id, [
            'judul' => 'Surat Siap Ditandatangani',
            'pesan' => "Surat \"{$surat->hal}\" telah diverifikasi dan menunggu tanda tangan Anda.",
            'tipe'  => 'ttd',
        ]);
    }

    /**
     * Kirim notifikasi ke pembuat surat (status update).
     */
    public static function kirimKePembuat(Surat $surat, string $judul, string $pesan, string $tipe = 'info'): void
    {
        self::buat($surat->dibuat_oleh, $surat->id, [
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe'  => $tipe,
        ]);
    }

    /**
     * Kirim notifikasi ke semua penerima surat saat terbit.
     */
    public static function kirimKePenerima(Surat $surat): void
    {
        foreach ($surat->penerima as $user) {
            self::buat($user->id, $surat->id, [
                'judul' => 'Surat Baru Untuk Anda',
                'pesan' => "Anda menerima surat \"{$surat->hal}\" (No: {$surat->nomor_surat}).",
                'tipe'  => 'terbit',
            ]);
        }
    }

    /**
     * Helper internal untuk membuat notifikasi.
     */
    private static function buat(int $userId, int $suratId, array $data): void
    {
        Notifikasi::create([
            'user_id'    => $userId,
            'surat_id'   => $suratId,
            'judul'      => $data['judul'],
            'pesan'      => $data['pesan'],
            'tipe'       => $data['tipe'],
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }
}
