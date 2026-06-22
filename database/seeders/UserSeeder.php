<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nama_lengkap' => 'Admin Jurusan TIK',
                'email'        => 'admin.tik@poliupg.ac.id',
                'nip'          => '198705222015041002',
                'password'     => Hash::make('password'),
                'role'         => 'administrator',
                'jabatan'      => 'Administrator Jurusan',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
            [
                'nama_lengkap' => 'Dr. Ahmad Sekretaris, M.T.',
                'email'        => 'sekjur.tik@poliupg.ac.id',
                'nip'          => '197801012006041001',
                'password'     => Hash::make('password'),
                'role'         => 'verifikator',
                'jabatan'      => 'Sekretaris Jurusan',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
            [
                'nama_lengkap' => 'Prof. Budi Santoso, S.T., M.Kom',
                'email'        => 'kajur.tik@poliupg.ac.id',
                'nip'          => '197505152003121001',
                'password'     => Hash::make('password'),
                'role'         => 'kajur',
                'jabatan'      => 'Ketua Jurusan',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
            [
                'nama_lengkap' => 'Ir. Siti Aminah, M.T.',
                'email'        => 'siti.aminah@poliupg.ac.id',
                'nip'          => '198201032010122001',
                'password'     => Hash::make('password'),
                'role'         => 'dosen',
                'jabatan'      => 'Dosen Tetap',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
            [
                'nama_lengkap' => 'Dr. Andi Pratama, M.Kom',
                'email'        => 'andi.pratama@poliupg.ac.id',
                'nip'          => '198506182012011003',
                'password'     => Hash::make('password'),
                'role'         => 'dosen',
                'jabatan'      => 'Dosen Tetap',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
            [
                'nama_lengkap' => 'Muh. Rizky Aditya, S.Kom., M.Cs.',
                'email'        => 'rizky.aditya@poliupg.ac.id',
                'nip'          => '199003142015041001',
                'password'     => Hash::make('password'),
                'role'         => 'dosen',
                'jabatan'      => 'Dosen Tetap',
                'jurusan'      => 'Teknik Informatika & Komputer',
                'is_active'    => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['email' => $user['email']], $user);
        }

        $this->command->info('✅ UserSeeder: ' . count($users) . ' users seeded.');
    }
}
