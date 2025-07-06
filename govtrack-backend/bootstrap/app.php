<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Le middleware CORS est ajouté en premier
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->prepend(\App\Http\Middleware\StripGovtrackPrefix::class);


        // Alias et autres middlewares personnalisés
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Pour éviter la redirection automatique vers login sur API
        $middleware->redirectGuestsTo(function (Request $request) {
            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token d\'authentification manquant, invalide ou expiré',
                    'error' => 'Unauthenticated'
                ], 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource',
                    'error' => 'Forbidden'
                ], 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La ressource demandée est introuvable',
                    'error' => 'Not Found'
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Méthode HTTP non autorisée pour cette route',
                    'error' => 'Method Not Allowed'
                ], 405);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les données fournies ne sont pas valides',
                    'error' => 'Validation Failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                if (app()->environment('production')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une erreur interne du serveur s\'est produite',
                        'error' => 'Internal Server Error'
                    ], 500);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur interne du serveur : ' . $e->getMessage(),
                        'error' => 'Internal Server Error',
                        'debug' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]
                    ], 500);
                }
            }
        });
    })
    ->create();
