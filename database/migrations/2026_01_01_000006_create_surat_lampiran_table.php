<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();
            $table->string('nama_file_asli');
            $table->string('nama_file_sistem');
            $table->string('path');
            $table->unsignedBigInteger('ukuran_bytes');
            $table->string('mime_type', 100);
            $table->timestamp('created_at')->nullable();

            $table->index('surat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_lampiran');
    }
};
