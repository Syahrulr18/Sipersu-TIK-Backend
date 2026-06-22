<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KodeHalController;

/*
|--------------------------------------------------------------------------
| SIPERSU TIK — API Routes
|--------------------------------------------------------------------------
*/

// ── Auth (public) ──────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// ── Authenticated routes ───────────────────────────────────────────────────
Route::middleware(['auth:api', 'active'])->group(function () {

    // Auth
    Route::post('/auth/logout',             [AuthController::class, 'logout']);
    Route::get('/auth/me',                  [AuthController::class, 'me']);
    Route::put('/auth/profile',             [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password',    [AuthController::class, 'changePassword']);
    Route::post('/auth/profile/photo',      [AuthController::class, 'uploadPhoto']);
    Route::post('/auth/profile/ttd',        [AuthController::class, 'uploadTtd']);

    // Users — lookup endpoints (all authenticated roles)
    Route::get('/users/penanda-tangan',     [UserController::class, 'penandaTangan']);
    Route::get('/users/verifikator',        [UserController::class, 'verifikator']);
    Route::get('/users/dosen',              [UserController::class, 'searchDosen']);

    // Users — admin only
    Route::middleware('role:administrator')->group(function () {
        Route::get('/users',                    [UserController::class, 'index']);
        Route::post('/users',                   [UserController::class, 'store']);
        Route::put('/users/{id}',               [UserController::class, 'update']);
        Route::delete('/users/{id}',            [UserController::class, 'destroy']);
        Route::patch('/users/{id}/toggle',      [UserController::class, 'toggleAktif']);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    // Surat — antrian (harus SEBELUM /{id} agar tidak konflik)
    Route::middleware('role:verifikator')->get('/surat/verifikasi', [SuratController::class, 'antrianVerifikasi']);
    Route::middleware('role:kajur')->get('/surat/tandatangan',      [SuratController::class, 'antrianTandaTangan']);

    // Surat — read (semua role)
    Route::get('/surat',        [SuratController::class, 'index']);
    Route::get('/surat/{id}',   [SuratController::class, 'show']);
    Route::get('/surat/{id}/pdf', [SuratController::class, 'pdf'])->name('surat.pdf');
    Route::get('/surat/{id}/lampiran/{lid}/download', [SuratController::class, 'downloadLampiran'])->name('surat.lampiran.download');

    // Surat — administrator only
    Route::middleware('role:administrator')->group(function () {
        Route::post('/surat',                       [SuratController::class, 'store']);
        Route::patch('/surat/{id}/konten',          [SuratController::class, 'updateKonten']);
        Route::delete('/surat/{id}',                [SuratController::class, 'destroy']);
        Route::post('/surat/{id}/submit',           [SuratController::class, 'submit']);
        Route::delete('/surat/{id}/lampiran/{lid}', [SuratController::class, 'deleteLampiran']);
    });

    // Surat — verifikator only
    Route::middleware('role:verifikator')->group(function () {
        Route::post('/surat/{id}/verifikasi', [SuratController::class, 'verifikasi']);
    });

    // Surat — kajur only
    Route::middleware('role:kajur')->group(function () {
        Route::post('/surat/{id}/tandatangan', [SuratController::class, 'tandatangan']);
    });

    // Notifikasi
    Route::get('/notifikasi',                   [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/unread-count',      [NotifikasiController::class, 'unreadCount']);
    Route::patch('/notifikasi/baca-semua',      [NotifikasiController::class, 'bacaSemua']);
    Route::delete('/notifikasi/hapus-semua',    [NotifikasiController::class, 'hapusSemua']);
    Route::patch('/notifikasi/{id}/baca',       [NotifikasiController::class, 'baca']);
    Route::delete('/notifikasi/{id}',           [NotifikasiController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard/statistik', [DashboardController::class, 'statistik']);
    Route::get('/dashboard/chart',     [DashboardController::class, 'chart']);
    Route::get('/dashboard/recent',    [DashboardController::class, 'recent']);

    // Kode Hal
    Route::get('/kode-hal',            [KodeHalController::class, 'index']);
    Route::middleware('role:administrator')->group(function () {
        Route::post('/kode-hal',        [KodeHalController::class, 'store']);
        Route::put('/kode-hal/{id}',    [KodeHalController::class, 'update']);
        Route::delete('/kode-hal/{id}', [KodeHalController::class, 'destroy']);
    });
});
