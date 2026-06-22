<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_surat_counter', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun')->unique();
            $table->unsignedInteger('counter_terakhir')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_counter');
    }
};
