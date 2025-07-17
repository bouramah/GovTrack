<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TypeEntiteController;
use App\Http\Controllers\Api\EntiteController;
use App\Http\Controllers\Api\PosteController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TypeProjetController;
use App\Http\Controllers\Api\TypeTacheController;
use App\Http\Controllers\Api\ProjetController;
use App\Http\Controllers\Api\TacheController;
use App\Http\Controllers\Api\DiscussionProjetController;
use App\Http\Controllers\Api\DiscussionTacheController;
use App\Http\Controllers\Api\PieceJointeProjetController;
use App\Http\Controllers\Api\PieceJointeTacheController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\PasswordResetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes d'authentification (publiques)
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    // Routes protégées par l'authentification
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('profile/photo', [AuthController::class, 'uploadProfilePhoto']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes pour la partie 1 - Gestion des utilisateurs (PROTÉGÉES)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Types d'entité
    Route::get('type-entites', [TypeEntiteController::class, 'index']); // Lecture libre
    Route::get('type-entites/{id}', [TypeEntiteController::class, 'show']); // Lecture libre
    Route::post('type-entites', [TypeEntiteController::class, 'store'])->middleware('permission:create_entity');
    Route::put('type-entites/{id}', [TypeEntiteController::class, 'update'])->middleware('permission:edit_entity');
    Route::delete('type-entites/{id}', [TypeEntiteController::class, 'destroy'])->middleware('permission:delete_entity');

    // Entités - routes spéciales d'abord (avant {id})
    Route::get('entites/organigramme', [EntiteController::class, 'organigramme'])->middleware('permission:view_entity_hierarchy');
    Route::get('entites/chefs-actuels', [EntiteController::class, 'chefsActuels']); // Lecture libre

    // Entités
    Route::get('entites', [EntiteController::class, 'index'])->middleware('permission:view_entities_list');
    Route::get('entites/{id}', [EntiteController::class, 'show'])->middleware('permission:view_entity_details');
    Route::get('entites/{id}/enfants', [EntiteController::class, 'enfants'])->middleware('permission:view_entity_details');
    Route::get('entites/{id}/hierarchy', [EntiteController::class, 'hierarchy'])->middleware('permission:view_entity_hierarchy');
    Route::post('entites', [EntiteController::class, 'store'])->middleware('permission:create_entity');
    Route::put('entites/{id}', [EntiteController::class, 'update'])->middleware('permission:edit_entity');
    Route::delete('entites/{id}', [EntiteController::class, 'destroy'])->middleware('permission:delete_entity');

    // Gestion des chefs d'entités
    Route::post('entites/{id}/affecter-chef', [EntiteController::class, 'affecterChef'])->middleware('permission:manage_entity_assignments');
    Route::post('entites/{id}/terminer-mandat-chef', [EntiteController::class, 'terminerMandatChef'])->middleware('permission:manage_entity_assignments');
    Route::get('entites/{id}/historique-chefs', [EntiteController::class, 'historiqueChefs'])->middleware('permission:view_entity_chief_history');

    // Gestion des utilisateurs d'entités
    Route::get('entites/{id}/utilisateurs', [EntiteController::class, 'utilisateurs'])->middleware('permission:view_entity_users');

    // Postes
    Route::get('postes', [PosteController::class, 'index']); // Lecture libre
    Route::get('postes/{id}', [PosteController::class, 'show']); // Lecture libre
    Route::post('postes', [PosteController::class, 'store'])->middleware('permission:create_user');
    Route::put('postes/{id}', [PosteController::class, 'update'])->middleware('permission:edit_user');
    Route::delete('postes/{id}', [PosteController::class, 'destroy'])->middleware('permission:delete_user');

    // Utilisateurs
    Route::get('users', [UserController::class, 'index'])->middleware('permission:view_users_list'); // Voir la liste
    Route::get('users/{id}', [UserController::class, 'show'])->middleware('permission:view_user_details'); // Voir les détails
    Route::get('users/{id}/affectations', [UserController::class, 'affectations'])->middleware('permission:view_user_details'); // Voir les affectations
    Route::post('users', [UserController::class, 'store'])->middleware('permission:create_user');
    Route::put('users/{id}', [UserController::class, 'update'])->middleware('permission:edit_user');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('permission:delete_user');
    Route::post('users/{id}/affecter', [UserController::class, 'affecter'])->middleware('permission:manage_user_assignments');
    Route::post('users/{id}/terminer-affectation', [UserController::class, 'terminerAffectation'])->middleware('permission:manage_user_assignments');
    Route::post('users/{id}/assign-role', [UserController::class, 'assignRole'])->middleware('permission:manage_user_roles');
    Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles'])->middleware('permission:manage_user_roles');
    Route::delete('users/{userId}/roles/{roleId}', [UserController::class, 'removeRole'])->middleware('permission:manage_user_roles');

    // Rôles
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:view_roles_list');
    Route::get('roles/{id}', [RoleController::class, 'show'])->middleware('permission:view_role_details');
    Route::get('roles/{id}/available-permissions', [RoleController::class, 'availablePermissions'])->middleware('permission:view_role_details');
    Route::get('roles/{id}/users', [RoleController::class, 'users'])->middleware('permission:view_role_users');
    Route::get('roles/{id}/stats', [RoleController::class, 'stats'])->middleware('permission:view_role_stats');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:create_role');
    Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('permission:edit_role');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:delete_role');
    Route::post('roles/{id}/assign-permission', [RoleController::class, 'assignPermission'])->middleware('permission:assign_permissions_to_role');
    Route::delete('roles/{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission'])->middleware('permission:remove_permissions_from_role');
    Route::post('roles/{id}/assign-to-user', [RoleController::class, 'assignToUser'])->middleware('permission:assign_role_to_user');
    Route::delete('roles/{roleId}/users/{userId}', [RoleController::class, 'removeFromUser'])->middleware('permission:remove_role_from_user');
    Route::post('roles/{id}/assign-permissions-bulk', [RoleController::class, 'assignPermissionsBulk'])->middleware('permission:assign_permissions_to_role');
    Route::post('roles/{id}/remove-permissions-bulk', [RoleController::class, 'removePermissionsBulk'])->middleware('permission:remove_permissions_from_role');

    // Permissions
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:view_permissions_list');
    Route::get('permissions/{id}', [PermissionController::class, 'show'])->middleware('permission:view_permission_details');
    Route::get('permissions/{id}/users', [PermissionController::class, 'users'])->middleware('permission:view_permission_users');
    Route::get('permissions/{id}/roles', [PermissionController::class, 'roles'])->middleware('permission:view_permission_roles');
    Route::get('permissions/{id}/stats', [PermissionController::class, 'stats'])->middleware('permission:view_permission_stats');
    Route::get('permissions/{id}/available-roles', [PermissionController::class, 'availableRoles'])->middleware('permission:view_permission_details');
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:create_permission');
    Route::put('permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:edit_permission');
    Route::delete('permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:delete_permission');

    // =================================================================
    // PARTIE 2 - GESTION DES PROJETS ET INSTRUCTIONS
    // =================================================================

    // Types d'instructions
    Route::get('type-projets', [TypeProjetController::class, 'index'])->middleware('permission:view_type_projets_list');
    Route::get('type-projets/{id}', [TypeProjetController::class, 'show'])->middleware('permission:view_type_projet_details');
    Route::get('type-projets/{id}/statistiques', [TypeProjetController::class, 'statistiques'])->middleware('permission:view_type_projet_stats');
    Route::post('type-projets', [TypeProjetController::class, 'store'])->middleware('permission:create_type_projet');
    Route::put('type-projets/{id}', [TypeProjetController::class, 'update'])->middleware('permission:edit_type_projet');
    Route::delete('type-projets/{id}', [TypeProjetController::class, 'destroy'])->middleware('permission:delete_type_projet');

    // Types de tâches
    Route::get('type-taches', [TypeTacheController::class, 'index'])->middleware('permission:view_type_taches_list');
    Route::get('type-taches/actifs', [TypeTacheController::class, 'actifs'])->middleware('permission:view_type_taches_list');
    Route::get('type-taches/{id}', [TypeTacheController::class, 'show'])->middleware('permission:view_type_tache_details');
    Route::get('type-taches/{id}/statistiques', [TypeTacheController::class, 'statistiques'])->middleware('permission:view_type_tache_stats');
    Route::post('type-taches', [TypeTacheController::class, 'store'])->middleware('permission:create_type_tache');
    Route::put('type-taches/{id}', [TypeTacheController::class, 'update'])->middleware('permission:edit_type_tache');
    Route::delete('type-taches/{id}', [TypeTacheController::class, 'destroy'])->middleware('permission:delete_type_tache');

    // Projets (Instructions/Recommandations) - routes spéciales d'abord
    Route::get('projets/tableau-bord', [ProjetController::class, 'tableauBord'])->middleware('permission:view_projects_list');

    // Filtres pour les projets
    Route::get('projets/filtres/entites', [ProjetController::class, 'getEntitesForFilter'])->middleware('permission:view_projects_list');
    Route::get('projets/filtres/utilisateurs', [ProjetController::class, 'getUsersForFilter'])->middleware('permission:view_projects_list');

    // Projets - CRUD
    Route::get('projets', [ProjetController::class, 'index'])->middleware('permission:view_projects_list');
    Route::get('projets/{id}', [ProjetController::class, 'show'])->middleware('permission:view_project_details');
    Route::post('projets', [ProjetController::class, 'store'])->middleware('permission:create_project');
    Route::put('projets/{id}', [ProjetController::class, 'update'])->middleware('permission:edit_project');
    Route::delete('projets/{id}', [ProjetController::class, 'destroy'])->middleware('permission:delete_project');

    // Gestion des statuts de projets
    Route::post('projets/{id}/changer-statut', [ProjetController::class, 'changerStatut'])->middleware('permission:change_project_status');

    // Gestion du niveau d'exécution de projets
    Route::post('projets/{id}/niveau-execution', [ProjetController::class, 'mettreAJourNiveauExecution'])->middleware('permission:update_project_execution_level');
    Route::get('projets/{id}/niveau-execution-info', [ProjetController::class, 'getNiveauExecutionInfo'])->middleware('permission:view_project_details');

    // Historique des projets
    Route::get('projets/{id}/historique', [ProjetController::class, 'historique'])->middleware('permission:view_project_history');

    // Tâches - routes spéciales d'abord
    Route::get('taches/mes-taches', [TacheController::class, 'mesTaches'])->middleware('permission:view_tasks_list');

    // Tâches - CRUD
    Route::get('taches', [TacheController::class, 'index'])->middleware('permission:view_tasks_list');
    Route::get('taches/{id}', [TacheController::class, 'show'])->middleware('permission:view_task_details');
    Route::post('taches', [TacheController::class, 'store'])->middleware('permission:create_task');
    Route::put('taches/{id}', [TacheController::class, 'update'])->middleware('permission:edit_task');
    Route::delete('taches/{id}', [TacheController::class, 'destroy'])->middleware('permission:delete_task');

    // Gestion des statuts de tâches
    Route::post('taches/{id}/changer-statut', [TacheController::class, 'changerStatut'])->middleware('permission:change_task_status');
    Route::get('taches/{id}/historique-statuts', [TacheController::class, 'historiqueStatuts'])->middleware('permission:view_task_history');

    // Gestion du niveau d'exécution des tâches
    Route::post('taches/{id}/niveau-execution', [TacheController::class, 'mettreAJourNiveauExecution'])->middleware('permission:edit_task');

    // =================================================================
    // DISCUSSIONS - COLLABORATION SUR PROJETS ET TÂCHES
    // =================================================================

    // Discussions des projets
    Route::prefix('projets/{projetId}/discussions')->group(function () {
        Route::get('/', [DiscussionProjetController::class, 'index'])->middleware('permission:view_project_comments');
        Route::post('/', [DiscussionProjetController::class, 'store'])->middleware('permission:add_project_comment');
        Route::get('statistiques', [DiscussionProjetController::class, 'statistiques'])->middleware('permission:view_project_comment_stats');
        Route::get('{id}', [DiscussionProjetController::class, 'show'])->middleware('permission:view_project_comments');
        Route::put('{id}', [DiscussionProjetController::class, 'update'])->middleware('permission:edit_project_comment');
        Route::delete('{id}', [DiscussionProjetController::class, 'destroy'])->middleware('permission:delete_project_comment');
    });

    // Discussions des tâches
    Route::prefix('taches/{tacheId}/discussions')->group(function () {
        Route::get('/', [DiscussionTacheController::class, 'index'])->middleware('permission:view_task_comments');
        Route::post('/', [DiscussionTacheController::class, 'store'])->middleware('permission:add_task_comment');
        Route::get('statistiques', [DiscussionTacheController::class, 'statistiques'])->middleware('permission:view_task_comment_stats');
        Route::get('{id}', [DiscussionTacheController::class, 'show'])->middleware('permission:view_task_comments');
        Route::put('{id}', [DiscussionTacheController::class, 'update'])->middleware('permission:edit_task_comment');
        Route::delete('{id}', [DiscussionTacheController::class, 'destroy'])->middleware('permission:delete_task_comment');
    });

    // =================================================================
    // PIÈCES JOINTES - GESTION DES FICHIERS ET JUSTIFICATIFS
    // =================================================================

    // Pièces jointes des projets
    Route::prefix('projets/{projetId}/pieces-jointes')->group(function () {
        Route::get('/', [PieceJointeProjetController::class, 'index'])->middleware('permission:view_project_attachments');
        Route::post('/', [PieceJointeProjetController::class, 'store'])->middleware('permission:add_project_attachment');
        Route::get('statistiques', [PieceJointeProjetController::class, 'statistiques'])->middleware('permission:view_project_attachments');
        Route::get('{id}', [PieceJointeProjetController::class, 'show'])->middleware('permission:view_project_attachments');
        Route::get('{id}/download', [PieceJointeProjetController::class, 'download'])->middleware('permission:download_project_attachment');
        Route::put('{id}', [PieceJointeProjetController::class, 'update'])->middleware('permission:edit_project_attachment');
        Route::delete('{id}', [PieceJointeProjetController::class, 'destroy'])->middleware('permission:delete_project_attachment');
    });

    // Pièces jointes des tâches
    Route::prefix('taches/{tacheId}/pieces-jointes')->group(function () {
        Route::get('/', [PieceJointeTacheController::class, 'index'])->middleware('permission:view_task_attachments');
        Route::post('/', [PieceJointeTacheController::class, 'store'])->middleware('permission:add_task_attachment');
        Route::get('statistiques', [PieceJointeTacheController::class, 'statistiques'])->middleware('permission:view_task_attachments');
        Route::get('{id}', [PieceJointeTacheController::class, 'show'])->middleware('permission:view_task_attachments');
        Route::get('{id}/download', [PieceJointeTacheController::class, 'download'])->middleware('permission:download_task_attachment');
        Route::put('{id}', [PieceJointeTacheController::class, 'update'])->middleware('permission:view_task_attachments');
        Route::delete('{id}', [PieceJointeTacheController::class, 'destroy'])->middleware('permission:delete_task_attachment');
    });

    // =================================================================
    // AUDIT - TRACABILITÉ DES ACTIONS
    // =================================================================

    // Logs d'audit
    Route::get('audit/logs', [AuditController::class, 'index'])->middleware('permission:view_audit_logs');
    Route::get('audit/logs/{id}', [AuditController::class, 'show'])->middleware('permission:view_audit_logs');
    Route::get('audit/stats', [AuditController::class, 'stats'])->middleware('permission:view_audit_logs');
    Route::get('audit/export', [AuditController::class, 'export'])->middleware('permission:export_audit_logs');

});

// Auth reset password
Route::post('v1/auth/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('v1/auth/reset-password', [PasswordResetController::class, 'reset']);

// Admin reset user password
Route::post('v1/users/{id}/reset-password', [UserController::class, 'resetPassword'])->middleware(['auth:sanctum','permission:reset_user_password']);
