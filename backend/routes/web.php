<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

Route::get('/', function () {
    return view('welcome');
});

// Endpoint metrics untuk Prometheus
Route::get('/metrics', function () {

    $metrics = [
        '# HELP http_requests_total Total permintaan API SyakiraTask',
        '# TYPE http_requests_total counter',
    ];

    $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    $statuses = [200, 201, 204, 400, 401, 403, 404, 422, 500, 503];

    foreach ($methods as $method) {
        foreach ($statuses as $status) {

            $key = "metrics_http_{$method}_{$status}";
            $count = (int) Cache::get($key, 0);

            $metrics[] = sprintf(
                'http_requests_total{method="%s",status="%s"} %d',
                $method,
                $status,
                $count
            );
        }
    }

    return response(
        implode("\n", $metrics) . "\n",
        200,
        ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']
    );
});
