<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;

// Root Route: Redirect to dashboard if logged in as admin/teacher, otherwise go to login
Route::get('/', function () {
    if (Auth::check() && Auth::user()->hasAdminAccess()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});

// Authentication Routes for Admin & Teacher Web Panel
Route::prefix('admin')->middleware(['web'])->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/logout', [AdminAuthController::class, 'logout']); // Fallback GET logout
});

// Protected Admin & Teacher Routes
Route::prefix('admin')->middleware(['web', 'admin.auth'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    // Subjects (Ujian) Management
    Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('admin.subjects.store');
    Route::put('/subjects/{id}', [AdminController::class, 'updateSubject'])->name('admin.subjects.update');
    Route::delete('/subjects/{id}', [AdminController::class, 'deleteSubject'])->name('admin.subjects.delete');

    // Questions (Bank Soal) Management
    Route::post('/questions', [AdminController::class, 'storeQuestion'])->name('admin.questions.store');
    Route::put('/questions/{id}', [AdminController::class, 'updateQuestion'])->name('admin.questions.update');
    Route::delete('/questions/{id}', [AdminController::class, 'deleteQuestion'])->name('admin.questions.delete');

    // Users (Akun Siswa, Guru & Admin) Management
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Exam Results Deletion (Hapus ujian yang telah diambil user)
    Route::delete('/exam-results/{id}', [AdminController::class, 'deleteExamResult'])->name('admin.exam-results.delete');

    // System Settings (Kode Penggantian Kelas)
    Route::post('/settings/class-change-code', [AdminController::class, 'updateClassChangeCode'])->name('admin.settings.class-change-code');

    // Proctoring Logs Management
    Route::post('/proctoring/clear-logs', [AdminController::class, 'clearProctoringLogs'])->name('admin.proctoring.clear');
});
