<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KoorEws\DashboardController;
use App\Http\Controllers\KoorEws\StatusMahasiswaController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Public route - Login
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
});

// Protected routes - memerlukan authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'profile');
    });

    Route::middleware(['role:koor|dosen'])->prefix('koor-doswal')->group(function () {
        Route::get('/dashboard/kategori-all', [DashboardController::class, 'kategoriAll']);
        Route::get('/dashboard/ipk-eligible', [DashboardController::class, 'ipkEligible']);
        Route::get('/status-mahasiswa/status-all', [StatusMahasiswaController::class, 'getAllStatusMahasiswaEws']);
        Route::get('/status-mahasiswa/ringkasan-status', [StatusMahasiswaController::class, 'getTableRingkasanStatusMahasiswa']);
        Route::get('/status-mahasiswa/mahasiswa-berisiko', [StatusMahasiswaController::class, 'getMahasiswaBerisko']);
        Route::get('/status-mahasiswa/ringkasan-status/export', [StatusMahasiswaController::class, 'exportTableRingkasanStatusMahasiswa']);
    });
});
