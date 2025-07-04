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

            // Gestion des rôles - Permissions granulaires
            'roles.index' => 'view_roles_list',
            'roles.show' => 'view_role_details',
            'roles.store' => 'create_role',
            'roles.update' => 'edit_role',
            'roles.destroy' => 'delete_role',
            'roles.assign-permission' => 'assign_permissions_to_role',
            'roles.remove-permission' => 'remove_permissions_from_role',
            'roles.users' => 'view_role_users',
            'roles.assign-to-user' => 'assign_role_to_user',
            'roles.remove-from-user' => 'remove_role_from_user',
            'roles.stats' => 'view_role_stats',

            // Gestion des permissions - Permissions granulaires
            'permissions.index' => 'view_permissions_list',
            'permissions.show' => 'view_permission_details',
            'permissions.store' => 'create_permission',
            'permissions.update' => 'edit_permission',
            'permissions.destroy' => 'delete_permission',
            'permissions.users' => 'view_permission_users',
            'permissions.roles' => 'view_permission_roles',
            'permissions.stats' => 'view_permission_stats',
        ];

        return $permissions[$action] ?? null;
    }
}
