<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CatatMetrik
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->record($request->method(), 500);
            throw $e;
        }

        $this->record($request->method(), $response->getStatusCode());

        return $response;
    }

    private function record(string $method, int $status): void
    {
        try {
            $key = "metrics_http_{$method}_{$status}";

            // Simpan counter untuk kebutuhan monitoring praktikum.
            Cache::add($key, 0, now()->addYears(5));
            Cache::increment($key);

        } catch (Throwable $e) {
            // Gangguan monitoring tidak boleh menghentikan API.
        }
    }
}
