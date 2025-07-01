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
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Configuration pour éviter la redirection vers 'login' pour les requêtes API
        $middleware->redirectGuestsTo(function (Request $request) {
            // Pour une API pure, on ne redirige jamais - AuthenticationException sera lancée
            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gestion des erreurs d'authentification
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token d\'authentification manquant, invalide ou expiré',
                    'error' => 'Unauthenticated'
                ], 401);
            }
        });

        // Gestion des erreurs d'autorisation (permissions)
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource',
                    'error' => 'Forbidden'
                ], 403);
            }
        });

        // Gestion des erreurs 404 (ressource non trouvée)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La ressource demandée est introuvable',
                    'error' => 'Not Found'
                ], 404);
            }
        });

        // Gestion des erreurs de méthode non autorisée
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Méthode HTTP non autorisée pour cette route',
                    'error' => 'Method Not Allowed'
                ], 405);
            }
        });

        // Gestion des erreurs de validation
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

        // Gestion des erreurs 500 (erreurs serveur)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Ne pas exposer les détails de l'erreur en production
                if (app()->environment('production')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une erreur interne du serveur s\'est produite',
                        'error' => 'Internal Server Error'
                    ], 500);
                } else {
                    // En développement, on peut montrer plus de détails
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
    })->create();
