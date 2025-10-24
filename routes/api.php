<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstateUserController;
use App\Http\Controllers\Api\VisitorCodeController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AnnouncementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
Route::post('/verify-email', [AuthController::class, 'verifyEmailWithCode']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('set-password', [AuthController::class, 'setPassword']);
Route::post('logout', [AuthController::class, 'logout']);
Route::delete('delete-user', [AuthController::class, 'deleteUser']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// Estate Management Routes
Route::middleware('auth:sanctum')->group(function () {
    // Admin Routes (Admin only)
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::apiResource('users', EstateUserController::class);
        Route::get('/complaints', [ComplaintController::class, 'adminIndex']);
        Route::get('/complaints/{id}', [ComplaintController::class, 'adminShow']);
        Route::put('/complaints/{id}', [ComplaintController::class, 'update']);
        Route::post('/complaints/{id}/reply', [ComplaintController::class, 'reply']);
        Route::get('/complaints/statistics', [ComplaintController::class, 'statistics']);
        
        // Announcement management (Admin only)
        Route::get('/announcements', [AnnouncementController::class, 'adminIndex']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    });

    // Maintainer Routes (Admin and Maintainer)
    Route::prefix('maintainer')->middleware('maintainer')->group(function () {
        Route::get('/visitor-codes', [VisitorCodeController::class, 'adminIndex']);
        Route::get('/activities', [ActivityController::class, 'adminIndex']);
        Route::get('/activities/statistics', [ActivityController::class, 'statistics']);
    });

    // Visitor Code Routes
    Route::prefix('visitor-codes')->group(function () {
        Route::get('/', [VisitorCodeController::class, 'index']);
        Route::post('/', [VisitorCodeController::class, 'store']);
        Route::get('/{id}', [VisitorCodeController::class, 'show']);
        Route::post('/{id}/verify', [VisitorCodeController::class, 'verify']);
        Route::post('/{id}/cancel', [VisitorCodeController::class, 'cancel']);
        Route::post('/verify-by-code', [VisitorCodeController::class, 'verifyByCode']);
        
        // Admin/Maintainer only routes for time management
        Route::middleware('maintainer')->group(function () {
            Route::post('/{id}/time-in', [VisitorCodeController::class, 'setTimeIn']);
            Route::post('/{id}/time-out', [VisitorCodeController::class, 'setTimeOut']);
        });
    });

    // Complaint Routes
    Route::prefix('complaints')->group(function () {
        Route::get('/', [ComplaintController::class, 'index']);
        Route::post('/', [ComplaintController::class, 'store']);
        Route::get('/categories', [ComplaintController::class, 'categories']);
        Route::get('/{id}', [ComplaintController::class, 'show']);
        Route::put('/{id}', [ComplaintController::class, 'update']);
    });

    // Activity Routes
    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::get('/recent', [ActivityController::class, 'recent']);
        Route::get('/actions', [ActivityController::class, 'actions']);
        Route::get('/{id}', [ActivityController::class, 'show']);
    });

    // Announcement Routes (Public to all authenticated users)
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::get('/{id}', [AnnouncementController::class, 'show']);
    });
});