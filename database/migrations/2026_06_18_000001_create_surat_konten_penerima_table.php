<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_konten_penerima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();
            $table->foreignId('penerima_user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('konten_html')->nullable();
            $table->timestamps();

            $table->unique(['surat_id', 'penerima_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_konten_penerima');
    }
};
