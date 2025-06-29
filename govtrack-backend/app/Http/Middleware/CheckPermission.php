<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié',
                'success' => false
            ], 401);
        }

        // Si aucune permission spécifique n'est requise, on vérifie juste l'authentification
        if (!$permission) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a la permission requise
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'message' => 'Permission insuffisante pour effectuer cette action',
                'success' => false,
                'required_permission' => $permission,
                'user_permissions' => $user->getAllPermissions()->pluck('nom')
            ], 403);
        }

        return $next($request);
    }

    /**
     * Middleware pour vérifier les permissions spécifiques selon l'action
     */
    public static function getPermissionForAction(string $action): ?string
    {
        $permissions = [
            // Gestion des utilisateurs
            'users.store' => 'manage_users',
            'users.update' => 'manage_users',
            'users.destroy' => 'manage_users',
            'users.affecter' => 'manage_users',
            'users.terminer-affectation' => 'manage_users',
            'users.assign-role' => 'manage_users',
            'users.remove-role' => 'manage_users',

            // Gestion des entités
            'entites.store' => 'manage_entities',
            'entites.update' => 'manage_entities',
            'entites.destroy' => 'manage_entities',
            'entites.affecter-chef' => 'manage_entities',
            'entites.terminer-mandat-chef' => 'manage_entities',

            // Gestion des postes
            'postes.store' => 'manage_users',
            'postes.update' => 'manage_users',
            'postes.destroy' => 'manage_users',

            // Gestion des types d'entités
            'type-entites.store' => 'manage_entities',
            'type-entites.update' => 'manage_entities',
            'type-entites.destroy' => 'manage_entities',

            // Gestion des rôles
            'roles.store' => 'manage_users',
            'roles.update' => 'manage_users',
            'roles.destroy' => 'manage_users',
            'roles.assign-permission' => 'manage_users',
            'roles.remove-permission' => 'manage_users',

            // Gestion des permissions
            'permissions.store' => 'manage_users',
            'permissions.update' => 'manage_users',
            'permissions.destroy' => 'manage_users',
        ];

        return $permissions[$action] ?? null;
    }
}
