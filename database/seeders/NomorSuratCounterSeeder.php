<?php

namespace Database\Seeders;

use App\Models\NomorSuratCounter;
use Illuminate\Database\Seeder;

class NomorSuratCounterSeeder extends Seeder
{
    public function run(): void
    {
        NomorSuratCounter::firstOrCreate(
            ['tahun' => 2026],
            ['counter_terakhir' => 1]
        );

        $this->command->info('✅ NomorSuratCounterSeeder: tahun 2026, counter=1.');
    }
}
