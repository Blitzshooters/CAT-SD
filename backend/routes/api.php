<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ProctoringController;

/*
|--------------------------------------------------------------------------
| API Routes - CAT SD Backend
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected JWT Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Exam Routes
    Route::get('/subjects', [ExamController::class, 'subjects']);
    Route::get('/subjects/{subjectId}/questions', [ExamController::class, 'questions']);
    Route::post('/exam/submit', [ExamController::class, 'submit']);
    Route::get('/exam/history', [ExamController::class, 'history']);

    // AI Proctoring Log Routes
    Route::post('/proctoring/log', [ProctoringController::class, 'logViolation']);
    Route::post('/proctoring/snapshot', [ProctoringController::class, 'uploadSnapshot']);
    Route::get('/proctoring/session/{examId}', [ProctoringController::class, 'sessionLogs']);
});
