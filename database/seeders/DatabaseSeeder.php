<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KodeHalSeeder::class,
            NomorSuratCounterSeeder::class,
            SuratSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Database seeding selesai!');
        $this->command->info('-----------------------------------');
        $this->command->info('Login credentials (password: password):');
        $this->command->table(
            ['Role', 'Email', 'NIP'],
            [
                ['administrator', 'admin.tik@poliupg.ac.id', '198705222015041002'],
                ['verifikator',   'sekjur.tik@poliupg.ac.id', '197801012006041001'],
                ['kajur',         'kajur.tik@poliupg.ac.id', '197505152003121001'],
                ['dosen',         'siti.aminah@poliupg.ac.id', '198201032010122001'],
            ]
        );
    }
}
