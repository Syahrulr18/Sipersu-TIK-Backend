<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nomor_urut')->nullable();
            $table->string('nomor_surat', 60)->unique()->nullable();
            $table->foreignId('penanda_tangan_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('verifikator_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('kode_hal_id')->constrained('kode_hal')->restrictOnDelete();
            $table->string('hal', 500);
            $table->text('ringkasan');
            $table->longText('konten_html')->nullable();
            $table->string('status')->default('draft')->comment('draft|menunggu_verifikasi|diverifikasi|ditolak|terbit');
            $table->text('catatan_penolakan')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_terbit')->nullable();
            $table->string('file_pdf_path')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('dibuat_oleh');
            $table->index('verifikator_id');
            $table->index('penanda_tangan_id');
            $table->index('tanggal_terbit');
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
