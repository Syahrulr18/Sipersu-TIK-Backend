<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_lampiran', function (Blueprint $table) {
            $table->integer('jumlah_halaman')->default(1)->after('ukuran_bytes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_lampiran', function (Blueprint $table) {
            $table->dropColumn('jumlah_halaman');
        });
    }
};
