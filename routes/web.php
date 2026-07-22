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
        Route::get('/riwayat/{id}/struk', [SiswaController::class, 'transactionReceipt'])->name('siswa.transaction.receipt');
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
        Route::get('/konfirmasi-batch', [OperatorController::class, 'confirmBatch'])->name('operator.confirm.batch');
        Route::get('/riwayat', [OperatorController::class, 'history'])->name('operator.history');
        Route::get('/riwayat/{id}/struk', [OperatorController::class, 'transactionReceipt'])->name('operator.transaction.receipt');
        Route::get('/export/excel', [OperatorController::class, 'exportExcel'])->name('operator.export.excel');
        Route::get('/export/pdf', [OperatorController::class, 'exportPdf'])->name('operator.export.pdf');
        Route::get('/profil', [OperatorController::class, 'profile'])->name('operator.profile');
        Route::post('/profil', [OperatorController::class, 'updateProfile'])->name('operator.profile.update');
        
        // Cash withdrawals approval/cancel
        Route::post('/tarik/{id}/approve', [OperatorController::class, 'approveTarik'])->name('operator.withdraw.approve');
        Route::post('/tarik/{id}/cancel', [OperatorController::class, 'cancelTarik'])->name('operator.withdraw.cancel');

        // Waste Distributions
        Route::get('/distributions', [\App\Http\Controllers\DistributionController::class, 'index'])->name('operator.distributions.index');
        Route::get('/distributions/create', [\App\Http\Controllers\DistributionController::class, 'create'])->name('operator.distributions.create');
        Route::post('/distributions', [\App\Http\Controllers\DistributionController::class, 'store'])->name('operator.distributions.store');
        Route::get('/distributions/{id}', [\App\Http\Controllers\DistributionController::class, 'show'])->name('operator.distributions.show');
        Route::get('/distributions/{id}/struk', [\App\Http\Controllers\DistributionController::class, 'printReceipt'])->name('operator.distributions.receipt');
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

        // Student Registration (Manual Single & Bulk Import)
        Route::get('/register-siswa', [WaliKelasController::class, 'showRegisterForm'])->name('walikelas.students.register');
        Route::post('/register-siswa/single', [WaliKelasController::class, 'registerSingleStudent'])->name('walikelas.students.register.single');
        Route::post('/register-siswa/bulk', [WaliKelasController::class, 'registerBulkStudents'])->name('walikelas.students.register.bulk');

        // Dynamic Class Reports
        Route::get('/laporan', [WaliKelasController::class, 'reports'])->name('walikelas.reports');
        Route::get('/laporan/export/excel', [WaliKelasController::class, 'exportExcel'])->name('walikelas.reports.excel');
        Route::get('/laporan/export/pdf', [WaliKelasController::class, 'exportPdf'])->name('walikelas.reports.pdf');
    });

    // === MANAJER PORTAL ===
    Route::prefix('manajer')->middleware(['role:manajer'])->group(function () {
        Route::get('/dashboard', [ManajerController::class, 'dashboard'])->name('manajer.dashboard');
        
        // Monitoring menus
        Route::get('/stok', [ManajerController::class, 'stokDetail'])->name('manajer.stok');
        Route::get('/stok/{id}', [ManajerController::class, 'stokCategoryDetail'])->name('manajer.stok.show');
        Route::get('/performa-kelas', [ManajerController::class, 'performaKelasDetail'])->name('manajer.performaKelas');
        Route::get('/log-transaksi', [ManajerController::class, 'logTransaksiDetail'])->name('manajer.logTransaksi');
        Route::get('/siswa-teraktif', [ManajerController::class, 'siswaTeraktifDetail'])->name('manajer.siswaTeraktif');

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

        // Dynamic School Reports
        Route::get('/laporan', [ManajerController::class, 'reports'])->name('manajer.reports');
        Route::get('/laporan/export/excel', [ManajerController::class, 'exportExcel'])->name('manajer.reports.excel');
        Route::get('/laporan/export/pdf', [ManajerController::class, 'exportPdf'])->name('manajer.reports.pdf');

        // Waste Categories CRUD
        Route::get('/waste-categories', [ManajerController::class, 'indexCategories'])->name('manajer.categories.index');
        Route::get('/waste-categories/create', [ManajerController::class, 'createCategory'])->name('manajer.categories.create');
        Route::post('/waste-categories', [ManajerController::class, 'storeCategory'])->name('manajer.categories.store');
        Route::get('/waste-categories/{id}/edit', [ManajerController::class, 'editCategory'])->name('manajer.categories.edit');
        Route::post('/waste-categories/{id}', [ManajerController::class, 'updateCategory'])->name('manajer.categories.update');
        Route::delete('/waste-categories/{id}', [ManajerController::class, 'destroyCategory'])->name('manajer.categories.destroy');

        // Waste Distributions
        Route::get('/distributions', [\App\Http\Controllers\DistributionController::class, 'index'])->name('manajer.distributions.index');
        Route::get('/distributions/{id}', [\App\Http\Controllers\DistributionController::class, 'show'])->name('manajer.distributions.show');
        Route::get('/distributions/{id}/struk', [\App\Http\Controllers\DistributionController::class, 'printReceipt'])->name('manajer.distributions.receipt');

        // Transaction Receipt (for dashboard recent activity)
        Route::get('/transaksi/{id}/struk', [ManajerController::class, 'transactionReceipt'])->name('manajer.transaction.receipt');
    });

});
