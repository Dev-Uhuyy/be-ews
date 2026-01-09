<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KoorEws\DashboardController;
use App\Http\Controllers\KoorEws\StatusMahasiswaController;
use App\Http\Controllers\KoorEws\CapaianMhsController;
use App\Http\Controllers\KoorEws\StatistikKelulusanController;
use App\Http\Controllers\MahasiswaEws\DashboardController as MahasiswaDashboardController;


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
        Route::get('/dashboard/summary-all', [DashboardController::class, 'summaryAll']);
        Route::get('/dashboard/export-ringkasan', [DashboardController::class, 'exportSummaryAll']);
        Route::get('/dashboard/detail-mahasiswa', [DashboardController::class, 'detailMahasiswa']);
        Route::get('/dashboard/export-detail-mahasiswa', [DashboardController::class, 'exportDetailMahasiswa']);


    });

    Route::middleware(['role:koor'])->prefix('koor-ews')->group(function () {
        // Capaian Mahasiswa Routes
        Route::get('/capaian/all-mahasiswa', [CapaianMhsController::class, 'getRataRataIps']);
        Route::get('/capaian/mk-gagal', [CapaianMhsController::class, 'getTop10MatkulGagal']);
        Route::get('/capaian/all-angkatan', [CapaianMhsController::class, 'getAllAngkatan']);
        Route::get('/capaian/export-all-angkatan', [CapaianMhsController::class, 'exportCapaianAngkatan']);
        Route::get('/capaian/mkgagal-angkatan', [CapaianMhsController::class, 'getDaftarGagalPerAngkatan']);
        Route::get('/capaian/export-mk-gagal', [CapaianMhsController::class, 'exportDaftarGagalPerAngkatan']);
        // Status Mahasiswa Routes
        Route::get('/status-mahasiswa/status-all', [StatusMahasiswaController::class, 'getAllStatusMahasiswaEws']);
        Route::get('/status-mahasiswa/status-angkatan/{angkatan}', [StatusMahasiswaController::class, 'getStatusMahasiswaEwsByAngkatan']);
        Route::get('/status-mahasiswa/ringkasan-status', [StatusMahasiswaController::class, 'getTableRingkasanStatusMahasiswa']);
        Route::get('/status-mahasiswa/detail-angkatan/{angkatan}', [StatusMahasiswaController::class, 'getTableRingkasanStatusMahasiswaByAngkatan']);
        Route::get('/status-mahasiswa/mahasiswa-berisiko', [StatusMahasiswaController::class, 'getMahasiswaBerisiko']);
        // Statistik Kelulusan Routes
        Route::get('/statistik-kelulusan/status', [StatistikKelulusanController::class, 'getStatistikKelulusan']);
    });

    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->group(function () {
        Route::get('/dashboard/status-mahasiswa', [MahasiswaDashboardController::class, 'statusMahasiswa']);
    });
});
