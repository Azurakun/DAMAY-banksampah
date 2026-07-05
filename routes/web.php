<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\ManajerController;

// 1. General Fallback Redirect to Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Authentication Paths
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password Paths
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// 3. Authenticated Portal Groups
Route::middleware(['auth'])->group(function () {

    // === SISWA PORTAL ===
    Route::prefix('siswa')->middleware(['role:siswa'])->group(function () {
        Route::get('/dashboard', [SiswaController::class, 'dashboard'])->name('siswa.dashboard');
        Route::get('/riwayat', [SiswaController::class, 'history'])->name('siswa.history');
        Route::get('/peringkat', [SiswaController::class, 'leaderboard'])->name('siswa.leaderboard');
        Route::get('/tarik', [SiswaController::class, 'showWithdrawForm'])->name('siswa.withdraw');
        Route::post('/tarik', [SiswaController::class, 'requestWithdraw'])->name('siswa.withdraw.post');
        Route::get('/profil', [SiswaController::class, 'profile'])->name('siswa.profile');
        Route::post('/profil', [SiswaController::class, 'updateProfile'])->name('siswa.profile.update');
    });

    // === OPERATOR PORTAL ===
    Route::prefix('operator')->middleware(['role:operator'])->group(function () {
        Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('operator.dashboard');
        Route::get('/search', [OperatorController::class, 'searchStudents'])->name('operator.search');
        Route::get('/setor/{id}', [OperatorController::class, 'showSetorForm'])->name('operator.setor');
        Route::post('/setor/{id}', [OperatorController::class, 'storeSetor'])->name('operator.setor.post');
        Route::get('/konfirmasi/{id}', [OperatorController::class, 'confirmSetor'])->name('operator.confirm');
        Route::get('/riwayat', [OperatorController::class, 'history'])->name('operator.history');
        Route::get('/export/excel', [OperatorController::class, 'exportExcel'])->name('operator.export.excel');
        Route::get('/export/pdf', [OperatorController::class, 'exportPdf'])->name('operator.export.pdf');
        Route::get('/profil', [OperatorController::class, 'profile'])->name('operator.profile');
        Route::post('/profil', [OperatorController::class, 'updateProfile'])->name('operator.profile.update');
        
        // Student Registration (Manual Single & Bulk Import)
        Route::get('/register-siswa', [OperatorController::class, 'showRegisterForm'])->name('operator.students.register');
        Route::post('/register-siswa/single', [OperatorController::class, 'registerSingleStudent'])->name('operator.students.register.single');
        Route::post('/register-siswa/bulk', [OperatorController::class, 'registerBulkStudents'])->name('operator.students.register.bulk');
        
        // Cash withdrawals approval/cancel
        Route::post('/tarik/{id}/approve', [OperatorController::class, 'approveTarik'])->name('operator.withdraw.approve');
        Route::post('/tarik/{id}/cancel', [OperatorController::class, 'cancelTarik'])->name('operator.withdraw.cancel');
    });

    // === WALI KELAS PORTAL ===
    Route::prefix('walikelas')->middleware(['role:walikelas'])->group(function () {
        Route::get('/dashboard', [WaliKelasController::class, 'dashboard'])->name('walikelas.dashboard');
        Route::get('/pendaftar', [WaliKelasController::class, 'showPendaftar'])->name('walikelas.pendaftar');
        Route::post('/pendaftar/approve', [WaliKelasController::class, 'approveBulk'])->name('walikelas.pendaftar.approve');
        Route::post('/pendaftar/reject', [WaliKelasController::class, 'rejectBulk'])->name('walikelas.pendaftar.reject');
        Route::get('/profil', [WaliKelasController::class, 'profile'])->name('walikelas.profile');
        Route::post('/profil', [WaliKelasController::class, 'updateProfile'])->name('walikelas.profile.update');
        Route::get('/siswa/{id}/detail', [WaliKelasController::class, 'studentDetail'])->name('walikelas.student.detail');
    });

    // === MANAJER PORTAL ===
    Route::prefix('manajer')->middleware(['role:manajer'])->group(function () {
        Route::get('/dashboard', [ManajerController::class, 'dashboard'])->name('manajer.dashboard');
        Route::post('/staff/register', [ManajerController::class, 'registerStaff'])->name('manajer.staff.register.post');
        Route::get('/profil', [ManajerController::class, 'profile'])->name('manajer.profile');
        Route::post('/profil', [ManajerController::class, 'updateProfile'])->name('manajer.profile.update');
        Route::get('/users', [ManajerController::class, 'indexUsers'])->name('manajer.users');
        Route::post('/users/{id}', [ManajerController::class, 'updateUser'])->name('manajer.users.update');
        Route::delete('/users/{id}', [ManajerController::class, 'destroyUser'])->name('manajer.users.destroy');
        
        // Classrooms Management
        Route::get('/classrooms', [ManajerController::class, 'indexClassrooms'])->name('manajer.classrooms');
        Route::post('/classrooms', [ManajerController::class, 'storeClassroom'])->name('manajer.classrooms.store');
        Route::delete('/classrooms/{id}', [ManajerController::class, 'destroyClassroom'])->name('manajer.classrooms.destroy');
        Route::post('/school-year/roll-over', [ManajerController::class, 'rollOverSchoolYear'])->name('manajer.schoolyear.rollover');
        Route::post('/school-year/update', [ManajerController::class, 'updateSchoolYear'])->name('manajer.schoolyear.update');
    });

});
