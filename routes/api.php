<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);

});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('/create-admin', [AuthController::class, 'createAdmin']);
});