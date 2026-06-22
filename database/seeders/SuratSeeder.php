<?php

namespace Database\Seeders;

use App\Models\KodeHal;
use App\Models\NomorSuratCounter;
use App\Models\Notifikasi;
use App\Models\Surat;
use App\Models\SuratLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuratSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = User::where('role', 'administrator')->first();
        $sekjur    = User::where('role', 'verifikator')->first();
        $kajur     = User::where('role', 'kajur')->first();
        $dosen1    = User::where('role', 'dosen')->first();
        $dosen2    = User::where('role', 'dosen')->skip(1)->first();
        $kodeUnd   = KodeHal::where('kode', 'KL.01.00')->first();
        $kodeST    = KodeHal::where('kode', 'KP.08.01')->first();
        $kodePerm  = KodeHal::where('kode', 'AK.06.01')->first();

        if (!$admin || !$sekjur || !$kajur || !$dosen1) {
            $this->command->warn('⚠️  User data tidak lengkap, skip SuratSeeder.');
            return;
        }

        $suratData = [
            [
                'data' => [
                    'nomor_surat'        => null,
                    'penanda_tangan_id'  => $kajur->id,
                    'verifikator_id'     => $sekjur->id,
                    'kode_hal_id'        => $kodeUnd?->id ?? 1,
                    'hal'                => 'Permohonan Izin Magang Industri',
                    'ringkasan'          => 'PT. Telkom Indonesia Tbk. - Semester Ganjil 2026/2027',
                    'konten_html'        => '<p>Dengan hormat,</p><p>Sehubungan dengan program magang industri...</p>',
                    'status'             => 'menunggu_verifikasi',
                    'dibuat_oleh'        => $admin->id,
                ],
                'penerima' => [$dosen1->id],
                'log' => [
                    ['status_sebelum' => null, 'status_sesudah' => 'draft', 'catatan' => 'Surat dibuat', 'user_id' => $admin->id],
                    ['status_sebelum' => 'draft', 'status_sesudah' => 'menunggu_verifikasi', 'catatan' => 'Dikirim ke verifikator', 'user_id' => $admin->id],
                ],
            ],
            [
                'data' => [
                    'nomor_urut'         => 1,
                    'nomor_surat'        => 'M.001/9/KL.01.00/2026',
                    'penanda_tangan_id'  => $kajur->id,
                    'verifikator_id'     => $sekjur->id,
                    'kode_hal_id'        => $kodeUnd?->id ?? 1,
                    'hal'                => 'Undangan Rapat Koordinasi Dosen',
                    'ringkasan'          => 'Rapat koordinasi persiapan semester ganjil 2026/2027',
                    'konten_html'        => '<p>Dengan hormat,</p><p>Mengundang seluruh dosen untuk hadir dalam rapat koordinasi...</p>',
                    'status'             => 'terbit',
                    'dibuat_oleh'        => $admin->id,
                    'tanggal_terbit'     => now()->subDays(5)->toDateString(),
                ],
                'penerima' => [$dosen1->id, $dosen2->id ?? $dosen1->id],
                'log' => [
                    ['status_sebelum' => null, 'status_sesudah' => 'draft', 'catatan' => 'Surat dibuat', 'user_id' => $admin->id],
                    ['status_sebelum' => 'draft', 'status_sesudah' => 'menunggu_verifikasi', 'catatan' => 'Dikirim ke verifikator', 'user_id' => $admin->id],
                    ['status_sebelum' => 'menunggu_verifikasi', 'status_sesudah' => 'diverifikasi', 'catatan' => 'Disetujui', 'user_id' => $sekjur->id],
                    ['status_sebelum' => 'diverifikasi', 'status_sesudah' => 'terbit', 'catatan' => 'Surat diterbitkan dengan nomor M.001/9/KL.01.00/2026', 'user_id' => $kajur->id],
                ],
            ],
            [
                'data' => [
                    'nomor_surat'        => null,
                    'penanda_tangan_id'  => $kajur->id,
                    'verifikator_id'     => $sekjur->id,
                    'kode_hal_id'        => $kodeST?->id ?? 2,
                    'hal'                => 'Surat Tugas Dosen Pembimbing PKL',
                    'ringkasan'          => 'Lomba IT Nasional Mahasiswa 2026',
                    'konten_html'        => '<p>Dengan hormat,</p><p>Menugaskan dosen berikut sebagai pembimbing...</p>',
                    'status'             => 'ditolak',
                    'catatan_penolakan'  => 'Lampiran surat tugas belum lengkap. Harap dilengkapi terlebih dahulu.',
                    'dibuat_oleh'        => $admin->id,
                ],
                'penerima' => [$dosen1->id],
                'log' => [
                    ['status_sebelum' => null, 'status_sesudah' => 'draft', 'catatan' => 'Surat dibuat', 'user_id' => $admin->id],
                    ['status_sebelum' => 'draft', 'status_sesudah' => 'menunggu_verifikasi', 'catatan' => 'Dikirim ke verifikator', 'user_id' => $admin->id],
                    ['status_sebelum' => 'menunggu_verifikasi', 'status_sesudah' => 'ditolak', 'catatan' => 'Lampiran surat tugas belum lengkap.', 'user_id' => $sekjur->id],
                ],
            ],
            [
                'data' => [
                    'nomor_surat'        => null,
                    'penanda_tangan_id'  => $kajur->id,
                    'verifikator_id'     => $sekjur->id,
                    'kode_hal_id'        => $kodePerm?->id ?? 3,
                    'hal'                => 'Pengajuan Alat Laboratorium Jaringan',
                    'ringkasan'          => 'Pengajuan perangkat switch dan router untuk Lab Jaringan Komputer',
                    'konten_html'        => null,
                    'status'             => 'draft',
                    'dibuat_oleh'        => $admin->id,
                ],
                'penerima' => [$dosen2->id ?? $dosen1->id],
                'log' => [
                    ['status_sebelum' => null, 'status_sesudah' => 'draft', 'catatan' => 'Surat dibuat', 'user_id' => $admin->id],
                ],
            ],
        ];

        foreach ($suratData as $item) {
            $surat = Surat::create($item['data']);
            $surat->penerima()->attach($item['penerima']);

            foreach ($item['log'] as $log) {
                SuratLog::create([
                    'surat_id'       => $surat->id,
                    'user_id'        => $log['user_id'],
                    'status_sebelum' => $log['status_sebelum'],
                    'status_sesudah' => $log['status_sesudah'],
                    'catatan'        => $log['catatan'],
                    'created_at'     => now(),
                ]);
            }

            // Notifikasi untuk surat terbit
            if ($item['data']['status'] === 'terbit') {
                foreach ($item['penerima'] as $userId) {
                    Notifikasi::create([
                        'user_id'    => $userId,
                        'surat_id'   => $surat->id,
                        'judul'      => 'Surat Baru Untuk Anda',
                        'pesan'      => "Anda menerima surat \"{$item['data']['hal']}\".",
                        'tipe'       => 'terbit',
                        'is_read'    => false,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ SuratSeeder: ' . count($suratData) . ' surat seeded.');
    }
}
