<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// ==========================================
// 1. LOGIN TANPA TOKEN
// ==========================================

Route::post('/login', [
    AuthController::class,
    'login'
]);

// ==========================================
// 2. HEALTH CHECK DATABASE DAN CACHE
// ==========================================

Route::get('/health', function () {

    $checks = [
        'database' => 'down',
        'cache' => 'down',
    ];

    // Pemeriksaan koneksi database MySQL
    try {

        $pdo = new \PDO(
            'mysql:host=' .
                config('database.connections.mysql.host') .
            ';port=' .
                config('database.connections.mysql.port') .
            ';dbname=' .
                config('database.connections.mysql.database') .
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

        // LOG ERROR: Koneksi database gagal
        Log::error('HEALTH_DATABASE_FAILED', [
            'actor' => 'system_healthcheck',
            'action' => 'check_database_connection',
            'reason' => 'database_connection_failed',
            'error_type' => get_class($e),
            'error_code' => (string) $e->getCode(),
            'result' => 'failed',
        ]);

    }

    // Pemeriksaan cache Laravel
    try {

        $key = 'syakiratask_health_check';

        Cache::put($key, 'healthy', 30);

        if (Cache::get($key) === 'healthy') {
            $checks['cache'] = 'up';
        } else {

            Log::error('HEALTH_CACHE_FAILED', [
                'actor' => 'system_healthcheck',
                'action' => 'check_cache',
                'reason' => 'cache_value_mismatch',
                'result' => 'failed',
            ]);
        }

        Cache::forget($key);

    } catch (\Throwable $e) {

        Log::error('HEALTH_CACHE_FAILED', [
            'actor' => 'system_healthcheck',
            'action' => 'check_cache',
            'reason' => 'cache_operation_failed',
            'error_type' => get_class($e),
            'error_code' => (string) $e->getCode(),
            'result' => 'failed',
        ]);

    }

    // Status kesehatan keseluruhan
    $healthy =
        $checks['database'] === 'up' &&
        $checks['cache'] === 'up';

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
    ], $healthy ? 200 : 503);

});

// ==========================================
// 3. ROUTE DENGAN TOKEN SANCTUM
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {

        return response()->json(
            $request->user()
        );
    });

    Route::post('/logout', [
        AuthController::class,
        'logout'
    ]);

    Route::apiResource(
        'tasks',
        TaskController::class
    );

});
