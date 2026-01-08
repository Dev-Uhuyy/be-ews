<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KoorEws\DashboardController;


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
});
