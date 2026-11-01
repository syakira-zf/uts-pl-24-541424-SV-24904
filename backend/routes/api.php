<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// ==========================================
// 1. LOGIN (TANPA TOKEN)
// ==========================================

Route::post('/login', [AuthController::class, 'login']);


// ==========================================
// 3. HEALTH CHECK DATABASE DAN CACHE
// ==========================================

Route::get('/health', function () {

    $checks = [
        'database' => 'down',
        'cache' => 'down',
    ];

    // Periksa koneksi MySQL dengan timeout singkat
    try {

        $pdo = new \PDO(
            'mysql:host=' . config('database.connections.mysql.host') .
            ';port=' . config('database.connections.mysql.port') .
            ';dbname=' . config('database.connections.mysql.database') .
            ';connect_timeout=2',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            [
                \PDO::ATTR_TIMEOUT => 2,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]
        );

        $pdo->query('SELECT 1');

        $checks['database'] = 'up';

    } catch (\Throwable $e) {

        Log::error('HEALTH_DATABASE_FAILED', [
            'error_type' => get_class($e),
            'message' => $e->getMessage(),
        ]);

    }

    // Periksa cache Laravel
    try {

        $key = 'syakiratask_health_check';

        Cache::put($key, 'healthy', 30);

        if (Cache::get($key) === 'healthy') {
            $checks['cache'] = 'up';
        }

        Cache::forget($key);

    } catch (\Throwable $e) {

        Log::error('HEALTH_CACHE_FAILED', [
            'error_type' => get_class($e),
            'message' => $e->getMessage(),
        ]);

    }

    // Tentukan status keseluruhan
    $healthy =
        $checks['database'] === 'up' &&
        $checks['cache'] === 'up';

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
    ], $healthy ? 200 : 503);

});


// ==========================================
// 4. ROUTE DENGAN AUTENTIKASI SANCTUM
// ==========================================

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
