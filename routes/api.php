<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use App\Notifications\VerifyEmailCustom;

Route::post('/oauth/token', function (Request $request) { return app()->handle($request); })->middleware('validate.oauth');
Route::get('/test-email', function () { $user = User::first(); return (new VerifyEmailCustom())->toMail($user)->render();});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleAuthGoogleCallback']);
});

Route::get('/das', function () {
    return response()->json(['message' => 'Welcome Admin']);
});


Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/superadmin', function () {
        return response()->json(['message' => 'Welcome Superadmin']);
    })->middleware('role:superadmin');
    Route::get('/admin', function () {
        return response()->json(['message' => 'Welcome Admin']);
    })->middleware('role:admin|superadmin');
    Route::get('/user', function () {
        return response()->json(['message' => 'Welcome User']);
    })->middleware('role:user|admin|superadmin');
});

Route::get('email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify');
Route::middleware('throttle:resend-verification')->post('email/verify/resend', [AuthController::class, 'resend'])->name('verification.resend');
