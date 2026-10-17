
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// Login tidak memerlukan token
Route::post('/login', [AuthController::class, 'login']);

// Semua endpoint berikut wajib menggunakan token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Data pengguna yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD Tasks
    Route::apiResource('tasks', TaskController::class);

});
