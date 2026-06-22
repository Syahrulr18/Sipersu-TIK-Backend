<?php

namespace Database\Seeders;

use App\Models\KodeHal;
use Illuminate\Database\Seeder;

class KodeHalSeeder extends Seeder
{
    public function run(): void
    {
        $kodeHalList = [
            // ── KEPEGAWAIAN ──────────────────────────────────────────────────
            ['kode' => 'KP.01.01', 'nama' => 'Pengangkatan Pegawai Baru',         'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.01.02', 'nama' => 'Pengangkatan dalam Jabatan',        'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.02.01', 'nama' => 'Kenaikan Pangkat Reguler',          'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.02.02', 'nama' => 'Kenaikan Pangkat Pilihan',          'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.03.01', 'nama' => 'Mutasi Pegawai',                    'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.04.01', 'nama' => 'Pensiun Pegawai',                   'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.05.01', 'nama' => 'Cuti Tahunan',                      'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.05.02', 'nama' => 'Cuti Besar',                        'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.05.03', 'nama' => 'Cuti Sakit',                        'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.05.04', 'nama' => 'Cuti Melahirkan',                   'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.06.01', 'nama' => 'Izin Belajar',                      'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.06.02', 'nama' => 'Tugas Belajar',                     'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.07.01', 'nama' => 'Penilaian Kinerja Pegawai',         'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.08.01', 'nama' => 'Surat Tugas',                       'kategori' => 'Kepegawaian'],
            ['kode' => 'KP.08.02', 'nama' => 'Perjalanan Dinas',                  'kategori' => 'Kepegawaian'],

            // ── KEUANGAN ─────────────────────────────────────────────────────
            ['kode' => 'KU.01.01', 'nama' => 'Rencana Anggaran',                  'kategori' => 'Keuangan'],
            ['kode' => 'KU.01.02', 'nama' => 'Realisasi Anggaran',                'kategori' => 'Keuangan'],
            ['kode' => 'KU.02.01', 'nama' => 'Honorarium Mengajar',               'kategori' => 'Keuangan'],
            ['kode' => 'KU.02.02', 'nama' => 'Tunjangan Kinerja',                 'kategori' => 'Keuangan'],
            ['kode' => 'KU.03.01', 'nama' => 'Pengajuan Dana Kegiatan',           'kategori' => 'Keuangan'],
            ['kode' => 'KU.04.01', 'nama' => 'Laporan Pertanggungjawaban',        'kategori' => 'Keuangan'],

            // ── AKADEMIK ─────────────────────────────────────────────────────
            ['kode' => 'AK.01.01', 'nama' => 'Jadwal Perkuliahan',                'kategori' => 'Akademik'],
            ['kode' => 'AK.01.02', 'nama' => 'Perubahan Jadwal Kuliah',           'kategori' => 'Akademik'],
            ['kode' => 'AK.02.01', 'nama' => 'Ujian Tengah Semester (UTS)',       'kategori' => 'Akademik'],
            ['kode' => 'AK.02.02', 'nama' => 'Ujian Akhir Semester (UAS)',        'kategori' => 'Akademik'],
            ['kode' => 'AK.03.01', 'nama' => 'Tugas Akhir / Skripsi',            'kategori' => 'Akademik'],
            ['kode' => 'AK.03.02', 'nama' => 'Pembimbing Tugas Akhir',           'kategori' => 'Akademik'],
            ['kode' => 'AK.04.01', 'nama' => 'Kurikulum Program Studi',           'kategori' => 'Akademik'],
            ['kode' => 'AK.05.01', 'nama' => 'Praktikum Laboratorium',           'kategori' => 'Akademik'],
            ['kode' => 'AK.06.01', 'nama' => 'Magang/Kerja Praktek',             'kategori' => 'Akademik'],
            ['kode' => 'AK.07.01', 'nama' => 'Transkip Nilai Mahasiswa',         'kategori' => 'Akademik'],
            ['kode' => 'AK.08.01', 'nama' => 'Sidang Yudisium',                  'kategori' => 'Akademik'],

            // ── KEMAHASISWAAN ────────────────────────────────────────────────
            ['kode' => 'KM.01.01', 'nama' => 'Kegiatan Mahasiswa/BEM',           'kategori' => 'Kemahasiswaan'],
            ['kode' => 'KM.01.02', 'nama' => 'Izin Kegiatan Mahasiswa',          'kategori' => 'Kemahasiswaan'],
            ['kode' => 'KM.02.01', 'nama' => 'Beasiswa Mahasiswa',               'kategori' => 'Kemahasiswaan'],
            ['kode' => 'KM.03.01', 'nama' => 'Prestasi dan Penghargaan',         'kategori' => 'Kemahasiswaan'],
            ['kode' => 'KM.04.01', 'nama' => 'Sanksi dan Disiplin Mahasiswa',    'kategori' => 'Kemahasiswaan'],

            // ── KELEMBAGAAN ──────────────────────────────────────────────────
            ['kode' => 'KL.01.00', 'nama' => 'Undangan Rapat',                   'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.01.01', 'nama' => 'Undangan Rapat Koordinasi',        'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.01.02', 'nama' => 'Undangan Rapat Jurusan',           'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.02.01', 'nama' => 'Notulen Rapat',                    'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.03.01', 'nama' => 'Kerjasama Institusi',              'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.04.01', 'nama' => 'Laporan Tahunan',                  'kategori' => 'Kelembagaan'],
            ['kode' => 'KL.05.01', 'nama' => 'Akreditasi Program Studi',         'kategori' => 'Kelembagaan'],

            // ── SARANA PRASARANA ─────────────────────────────────────────────
            ['kode' => 'SP.01.01', 'nama' => 'Pengadaan Alat dan Bahan',         'kategori' => 'Sarana Prasarana'],
            ['kode' => 'SP.01.02', 'nama' => 'Pengadaan Perangkat Komputer',     'kategori' => 'Sarana Prasarana'],
            ['kode' => 'SP.02.01', 'nama' => 'Pemeliharaan Gedung/Ruang',        'kategori' => 'Sarana Prasarana'],
            ['kode' => 'SP.02.02', 'nama' => 'Pemeliharaan Lab Komputer',        'kategori' => 'Sarana Prasarana'],
            ['kode' => 'SP.03.01', 'nama' => 'Inventarisasi Barang',             'kategori' => 'Sarana Prasarana'],

            // ── PENELITIAN & PENGABDIAN ──────────────────────────────────────
            ['kode' => 'PP.01.01', 'nama' => 'Proposal Penelitian',              'kategori' => 'Penelitian & Pengabdian'],
            ['kode' => 'PP.01.02', 'nama' => 'Laporan Hasil Penelitian',         'kategori' => 'Penelitian & Pengabdian'],
            ['kode' => 'PP.02.01', 'nama' => 'Pengabdian Kepada Masyarakat',     'kategori' => 'Penelitian & Pengabdian'],
            ['kode' => 'PP.02.02', 'nama' => 'Laporan Pengabdian Masyarakat',    'kategori' => 'Penelitian & Pengabdian'],
            ['kode' => 'PP.03.01', 'nama' => 'Seminar dan Konferensi',           'kategori' => 'Penelitian & Pengabdian'],
            ['kode' => 'PP.04.01', 'nama' => 'Publikasi Ilmiah',                 'kategori' => 'Penelitian & Pengabdian'],

            // ── UMUM ─────────────────────────────────────────────────────────
            ['kode' => 'UM.01.01', 'nama' => 'Surat Keterangan',                 'kategori' => 'Umum'],
            ['kode' => 'UM.01.02', 'nama' => 'Surat Pengantar',                  'kategori' => 'Umum'],
            ['kode' => 'UM.02.01', 'nama' => 'Pemberitahuan Umum',              'kategori' => 'Umum'],
            ['kode' => 'UM.03.01', 'nama' => 'Permohonan/Permintaan',           'kategori' => 'Umum'],
            ['kode' => 'UM.04.01', 'nama' => 'Pengumuman Jurusan',              'kategori' => 'Umum'],
        ];

        foreach ($kodeHalList as $item) {
            KodeHal::firstOrCreate(
                ['kode' => $item['kode']],
                array_merge($item, ['is_active' => true])
            );
        }

        $this->command->info('✅ KodeHalSeeder: ' . count($kodeHalList) . ' kode hal seeded.');
    }
}
