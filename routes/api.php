<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',[AuthController::class,'logout']);
    });
});
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::post('update/{profile}',[ProfileController::class,'update']);
        Route::get('show/{profile}',[ProfileController::class,'show']);
    });
});



