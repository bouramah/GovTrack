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
                'message' => "Vous n'avez pas la permission '{$permission}' pour effectuer cette action",
                'success' => false,
                'error' => 'Permission insuffisante',
                'required_permission' => $permission,
                'user_permissions' => $user->getAllPermissions()->pluck('nom')
            ], 422);
        }

        return $next($request);
    }

    /**
     * Middleware pour vérifier les permissions spécifiques selon l'action
     */
    public static function getPermissionForAction(string $action): ?string
    {
        $permissions = [
            // Gestion des utilisateurs - Permissions granulaires
            'users.index' => 'view_users_list',
            'users.show' => 'view_user_details',
            'users.store' => 'create_user',
            'users.update' => 'edit_user',
            'users.destroy' => 'delete_user',
            'users.affecter' => 'manage_user_assignments',
            'users.terminer-affectation' => 'manage_user_assignments',
            'users.assign-role' => 'manage_user_roles',
            'users.remove-role' => 'manage_user_roles',
            'users.stats' => 'view_user_stats',

            // Gestion des entités - Permissions granulaires
            'entites.index' => 'view_entities_list',
            'entites.show' => 'view_entity_details',
            'entites.store' => 'create_entity',
            'entites.update' => 'edit_entity',
            'entites.destroy' => 'delete_entity',
            'entites.hierarchy' => 'view_entity_hierarchy',
            'entites.users' => 'view_entity_users',
            'entites.affecter-chef' => 'manage_entity_assignments',
            'entites.terminer-mandat-chef' => 'manage_entity_assignments',
            'entites.historique-chefs' => 'view_entity_chief_history',
            'entites.organigramme' => 'view_entity_hierarchy',

            // Gestion des postes
            'postes.store' => 'create_user',
            'postes.update' => 'edit_user',
            'postes.destroy' => 'delete_user',

            // Gestion des types d'entités
            'type-entites.store' => 'create_entity',
            'type-entites.update' => 'edit_entity',
            'type-entites.destroy' => 'delete_entity',

            // Gestion des rôles
            'roles.store' => 'manage_user_roles',
            'roles.update' => 'manage_user_roles',
            'roles.destroy' => 'manage_user_roles',
            'roles.assign-permission' => 'manage_user_roles',
            'roles.remove-permission' => 'manage_user_roles',

            // Gestion des permissions
            'permissions.store' => 'manage_user_roles',
            'permissions.update' => 'manage_user_roles',
            'permissions.destroy' => 'manage_user_roles',
        ];

        return $permissions[$action] ?? null;
    }
}
