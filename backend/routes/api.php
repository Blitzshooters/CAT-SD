<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ProctoringController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes - CAT SD Backend
|--------------------------------------------------------------------------
*/

// Public Auth & Exam Access
Route::post('/auth/login', [AuthController::class, 'login']);

// Exam queries & remedy verification (accessible for student client)
Route::get('/subjects', [ExamController::class, 'subjects']);
Route::get('/subjects/{subjectId}/questions', [ExamController::class, 'questions']);
Route::post('/exam/verify-remedy', [ExamController::class, 'verifyRemedy']);
Route::post('/exam/submit', [ExamController::class, 'submit']);
Route::get('/exam/history', [ExamController::class, 'history']);
Route::post('/class-code/verify', [ExamController::class, 'verifyClassCode']);
Route::get('/class-code', [ExamController::class, 'getClassChangeCode']);

// AI Proctoring
Route::post('/proctoring/log', [ProctoringController::class, 'logViolation']);
Route::post('/proctoring/snapshot', [ProctoringController::class, 'uploadSnapshot']);
Route::get('/proctoring/session/{examId}', [ProctoringController::class, 'sessionLogs']);

// Profile & Password Update
Route::post('/user/profile', [AuthController::class, 'updateProfile']);
Route::post('/user/change-password', [AuthController::class, 'changePassword']);
Route::post('/user/upload-avatar', [AuthController::class, 'uploadAvatar']);

// Admin API endpoints (CRUD Ujian, Soal, Akun, Hasil Ujian, & Settings)
Route::prefix('admin')->group(function () {
    Route::post('/subjects', [AdminController::class, 'storeSubject']);
    Route::put('/subjects/{id}', [AdminController::class, 'updateSubject']);
    Route::delete('/subjects/{id}', [AdminController::class, 'deleteSubject']);

    Route::post('/questions', [AdminController::class, 'storeQuestion']);
    Route::put('/questions/{id}', [AdminController::class, 'updateQuestion']);
    Route::delete('/questions/{id}', [AdminController::class, 'deleteQuestion']);

    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    Route::delete('/exam-results/{id}', [AdminController::class, 'deleteExamResult']);
    Route::post('/settings/class-change-code', [AdminController::class, 'updateClassChangeCode']);
    Route::post('/proctoring/clear-logs', [AdminController::class, 'clearProctoringLogs']);
});

// Authenticated JWT Protected Group
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});
