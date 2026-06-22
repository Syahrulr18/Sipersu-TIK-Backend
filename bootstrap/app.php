<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        // Register named middleware aliases
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role'   => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 401 — Unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated. Silakan login kembali.'], 401);
            }
        });

        // 404 — Model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }
        });

        // 422 — Validation
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // JWT expired
        $exceptions->render(function (TokenExpiredException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Token expired. Silakan login kembali.'], 401);
            }
        });

        // JWT invalid
        $exceptions->render(function (TokenInvalidException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Token tidak valid.'], 401);
            }
        });

        // JWT general
        $exceptions->render(function (JWTException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Token error: ' . $e->getMessage()], 401);
            }
        });
    })->create();
