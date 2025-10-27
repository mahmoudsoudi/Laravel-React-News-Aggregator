<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NewsSourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserPreferenceController;

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

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public news routes (no authentication required)
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/trending', [NewsController::class, 'trending']);
Route::get('/news/search', [NewsController::class, 'search']);
Route::get('/news/category/{categorySlug}', [NewsController::class, 'byCategory']);
Route::get('/news/source/{sourceSlug}', [NewsController::class, 'bySource']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// Public sources and categories
Route::get('/sources', [NewsSourceController::class, 'index']);
Route::get('/sources/{slug}', [NewsSourceController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);

// Protected routes
Route::middleware('api.auth:sanctum')->group(function () {
    // User management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'delete']);

    // User preferences
    Route::get('/preferences', [UserPreferenceController::class, 'index']);
    Route::put('/preferences', [UserPreferenceController::class, 'update']);
    Route::post('/preferences/sources', [UserPreferenceController::class, 'addPreferredSource']);
    Route::delete('/preferences/sources', [UserPreferenceController::class, 'removePreferredSource']);
    Route::post('/preferences/categories', [UserPreferenceController::class, 'addPreferredCategory']);
    Route::delete('/preferences/categories', [UserPreferenceController::class, 'removePreferredCategory']);
});
