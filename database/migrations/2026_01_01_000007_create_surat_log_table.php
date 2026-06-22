<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status_sebelum', 30)->nullable();
            $table->string('status_sesudah', 30);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('surat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_log');
    }
};
