<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // Post Routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/create-post', [PostController::class, 'store']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/update-post/{id}', [PostController::class, 'update']);
    Route::delete('/delete-post/{id}', [PostController::class, 'destroy']);
});


// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
// Route::middleware('auth:sanctum')->get('/posts', [PostController::class, 'index']);
// Route::middleware('auth:sanctum')->post('/create-post', [PostController::class, 'store']);
// Route::middleware('auth:sanctum')->get('/post/{id}', [PostController::class, 'show']);
// Route::middleware('auth:sanctum')->post('/update-post/{id}', [PostController::class, 'update']);
// Route::middleware('auth:sanctum')->delete('/delete-post/{id}', [PostController::class, 'destroy']);
