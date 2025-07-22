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
use App\Http\Controllers\Api\ProjetFavoriController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LoginActivityController;


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

    // Gestion des porteurs multiples de projets
    Route::post('projets/{id}/porteurs', [ProjetController::class, 'assignerPorteurs'])->middleware('permission:manage_project_porteurs');
    Route::delete('projets/{id}/porteurs/{userId}', [ProjetController::class, 'retirerPorteur'])->middleware('permission:manage_project_porteurs');
    Route::get('projets/{id}/porteurs', [ProjetController::class, 'listerPorteurs'])->middleware('permission:view_project_details');

    // Gestion des favoris de projets
    Route::get('projets/favoris', [ProjetFavoriController::class, 'index'])->middleware('permission:view_projects_list');
    Route::post('projets/{id}/favoris', [ProjetFavoriController::class, 'store'])->middleware('permission:view_projects_list');
    Route::delete('projets/{id}/favoris', [ProjetFavoriController::class, 'destroy'])->middleware('permission:view_projects_list');
    Route::post('projets/{id}/favoris/toggle', [ProjetFavoriController::class, 'toggle'])->middleware('permission:view_projects_list');

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

    // Gestion des responsables multiples de tâches
    Route::post('taches/{id}/responsables', [TacheController::class, 'assignerResponsables'])->middleware('permission:manage_task_responsables');
    Route::delete('taches/{id}/responsables/{userId}', [TacheController::class, 'retirerResponsable'])->middleware('permission:manage_task_responsables');
    Route::get('taches/{id}/responsables', [TacheController::class, 'listerResponsables'])->middleware('permission:view_task_details');

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

    // =================================================================
    // RÉUNIONS - GESTION DES RÉUNIONS ET RENDEZ-VOUS
    // =================================================================

    // Types de réunions
    Route::prefix('types-reunions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'index'])->middleware('permission:view_reunion_types');
        Route::get('actifs', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'active'])->middleware('permission:view_reunion_types');
        Route::get('{id}', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'show'])->middleware('permission:view_reunion_types');
        Route::post('/', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'store'])->middleware('permission:create_reunion_types');
        Route::put('{id}', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'update'])->middleware('permission:update_reunion_types');
        Route::delete('{id}', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'destroy'])->middleware('permission:delete_reunion_types');
        Route::post('{id}/toggle-active', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'toggleActive'])->middleware('permission:update_reunion_types');
        Route::get('{id}/stats', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'stats'])->middleware('permission:view_reunion_types');
        Route::get('{id}/reunions', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'reunions'])->middleware('permission:view_reunions');

        // Gestion des gestionnaires
        Route::get('{id}/gestionnaires', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'gestionnaires'])->middleware('permission:view_reunion_types');
        Route::post('{id}/gestionnaires', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'addGestionnaires'])->middleware('permission:update_reunion_types');
        Route::delete('{id}/gestionnaires', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'removeGestionnaires'])->middleware('permission:update_reunion_types');

        // Gestion des membres
        Route::get('{id}/membres', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'membres'])->middleware('permission:view_reunion_types');
        Route::post('{id}/membres', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'addMembres'])->middleware('permission:update_reunion_types');
        Route::delete('{id}/membres', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'removeMembres'])->middleware('permission:update_reunion_types');

        // Gestion des validateurs PV
        Route::get('{id}/validateurs-pv', [\App\Http\Controllers\Api\Reunion\TypeReunionController::class, 'validateursPV'])->middleware('permission:view_reunion_types');
    });

    // Réunions principales
    Route::prefix('reunions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'index'])->middleware('permission:view_reunions');
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'stats'])->middleware('permission:view_reunions');
        Route::get('{id}', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'show'])->middleware('permission:view_reunions');
        Route::post('/', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'store'])->middleware('permission:create_reunions');
        Route::put('{id}', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'update'])->middleware('permission:update_reunions');
        Route::delete('{id}', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'destroy'])->middleware('permission:delete_reunions');
        Route::post('{id}/changer-statut', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'changeStatut'])->middleware('permission:update_reunions');

        // Gestion des participants
        Route::get('{id}/participants', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'participants'])->middleware('permission:view_reunions');
        Route::post('{id}/participants', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'addParticipant'])->middleware('permission:update_reunions');
        Route::post('{id}/participants/multiple', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'addMultipleParticipants'])->middleware('permission:update_reunions');
        Route::put('{reunionId}/participants/{participantId}', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'updateParticipant'])->middleware('permission:update_reunions');
        Route::delete('{reunionId}/participants/{participantId}', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'removeParticipant'])->middleware('permission:update_reunions');
        Route::post('{reunionId}/participants/{participantId}/presence', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'updatePresenceStatus'])->middleware('permission:update_reunions');
        Route::get('{id}/participants/stats', [\App\Http\Controllers\Api\Reunion\ReunionController::class, 'participantStats'])->middleware('permission:view_reunions');

        // =================================================================
        // PHASE 2 : PROCÈS-VERBAUX
        // =================================================================
        Route::prefix('{reunionId}/pv')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'index'])->middleware('permission:view_reunion_pv');
            Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'stats'])->middleware('permission:view_reunion_pv');
            Route::get('dernier-valide', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'getLastValidated'])->middleware('permission:view_reunion_pv');
            Route::post('/', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'store'])->middleware('permission:create_reunion_pv');
            Route::get('{pvId}', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'show'])->middleware('permission:view_reunion_pv');
            Route::put('{pvId}', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'update'])->middleware('permission:update_reunion_pv');
            Route::delete('{pvId}', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'destroy'])->middleware('permission:delete_reunion_pv');
            Route::post('{pvId}/soumettre', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'submitForValidation'])->middleware('permission:update_reunion_pv');
            Route::post('{pvId}/valider', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'validate'])->middleware('permission:validate_reunion_pv');
            Route::post('{pvId}/rejeter', [\App\Http\Controllers\Api\Reunion\ReunionPVController::class, 'reject'])->middleware('permission:validate_reunion_pv');
        });

        // =================================================================
        // PHASE 2 : NOTIFICATIONS
        // =================================================================
        Route::prefix('{reunionId}/notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'getReunionNotifications'])->middleware('permission:view_reunion_notifications');
            Route::post('envoyer', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'sendManualNotification'])->middleware('permission:send_reunion_notifications');
            Route::post('automatiques', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'sendAutomaticNotifications'])->middleware('permission:send_reunion_notifications');
        });
    });

    // =================================================================
    // PHASE 2 : SÉRIES DE RÉUNIONS
    // =================================================================
    Route::prefix('series-reunions')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'index'])->middleware('permission:view_reunion_series');
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'stats'])->middleware('permission:view_reunion_series');
        Route::get('{id}', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'show'])->middleware('permission:view_reunion_series');
        Route::post('/', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'store'])->middleware('permission:create_reunion_series');
        Route::put('{id}', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'update'])->middleware('permission:update_reunion_series');
        Route::delete('{id}', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'destroy'])->middleware('permission:delete_reunion_series');
        Route::post('{id}/toggle-active', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'toggleActive'])->middleware('permission:update_reunion_series');
        Route::post('{id}/generer-reunions', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'generateReunions'])->middleware('permission:update_reunion_series');
        Route::post('{id}/regenerer-reunions', [\App\Http\Controllers\Api\Reunion\ReunionSerieController::class, 'regenerateReunions'])->middleware('permission:update_reunion_series');
    });

    // =================================================================
    // PHASE 2 : NOTIFICATIONS GLOBALES
    // =================================================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'getUserNotifications'])->middleware('permission:view_notifications');
        Route::get('non-lues', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'getUnreadNotifications'])->middleware('permission:view_notifications');
        Route::get('nombre-non-lues', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'getUnreadCount'])->middleware('permission:view_notifications');
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'getNotificationStats'])->middleware('permission:view_notifications');
        Route::post('marquer-lue/{notificationId}', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'markAsRead'])->middleware('permission:view_notifications');
        Route::post('marquer-toutes-lues', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'markAllAsRead'])->middleware('permission:view_notifications');
        Route::delete('{notificationId}', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'deleteNotification'])->middleware('permission:view_notifications');
        Route::delete('supprimer-lues', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'deleteReadNotifications'])->middleware('permission:view_notifications');
        Route::delete('supprimer-toutes', [\App\Http\Controllers\Api\Reunion\ReunionNotificationController::class, 'deleteAllNotifications'])->middleware('permission:view_notifications');
    });

    // =================================================================
    // PHASE 3 : CALENDRIER ET VUES TEMPORELLES
    // =================================================================
    Route::prefix('calendar')->group(function () {
        // Événements calendrier
                Route::get('events', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getEvents'])->middleware('permission:view_reunions');
        Route::get('events/my', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getMyEvents'])->middleware('permission:view_reunions');

        // Vues calendrier
        Route::get('day', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getDayView'])->middleware('permission:view_reunions');
        Route::get('week', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getWeekView'])->middleware('permission:view_reunions');
        Route::get('month', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getMonthView'])->middleware('permission:view_reunions');

        // Disponibilité et créneaux
        Route::post('availability/check', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'checkAvailability'])->middleware('permission:view_reunions');
        Route::post('availability/slots', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'findAvailableSlots'])->middleware('permission:view_reunions');

        // Statistiques calendrier
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getStats'])->middleware('permission:view_reunions');
        Route::get('stats/my', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'getMyStats'])->middleware('permission:view_reunions');

        // Export iCal
        Route::get('export/ical', [\App\Http\Controllers\Api\Reunion\ReunionCalendarController::class, 'exportICal'])->middleware('permission:view_reunions');
    });

    // =================================================================
    // PHASE 3 : ANALYTICS ET TABLEAUX DE BORD
    // =================================================================
    Route::prefix('analytics')->group(function () {
                // Statistiques globales
        Route::get('global-stats', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getGlobalStats'])->middleware('permission:view_analytics');
        Route::get('trends', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getTrends'])->middleware('permission:view_analytics');

        // Rapports détaillés
        Route::get('entity-report', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getEntityReport'])->middleware('permission:view_analytics');
        Route::get('participant-performance', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getParticipantPerformanceReport'])->middleware('permission:view_analytics');
        Route::get('pv-quality', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getPVQualityReport'])->middleware('permission:view_analytics');
        Route::get('performance-metrics', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getPerformanceMetrics'])->middleware('permission:view_analytics');

        // Tableau de bord exécutif
        Route::get('executive-dashboard', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getExecutiveDashboard'])->middleware('permission:view_analytics');

        // Rapports personnalisés
        Route::post('custom-report', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'generateCustomReport'])->middleware('permission:view_analytics');
        Route::get('comparison-report', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'getComparisonReport'])->middleware('permission:view_analytics');

        // Export de données
        Route::get('export-data', [\App\Http\Controllers\Api\Reunion\ReunionAnalyticsController::class, 'exportData'])->middleware('permission:export_analytics');
    });

    // =================================================================
    // PHASE 3 : WORKFLOWS DE VALIDATION
    // =================================================================
    Route::prefix('workflows')->group(function () {
        // Configuration des workflows
        Route::get('configs/{typeReunionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'getWorkflowConfigs'])->middleware('permission:view_reunion_workflows');
        Route::post('configs', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'createWorkflowConfig'])->middleware('permission:create_reunion_workflow');

        // Exécution des workflows
        Route::post('start/{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'startWorkflow'])->middleware('permission:start_reunion_workflow');
        Route::post('validate/{executionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'validateEtape'])->middleware('permission:validate_reunion_workflow');
        Route::post('reject/{executionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'rejectEtape'])->middleware('permission:validate_reunion_workflow');
        Route::get('en-cours', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'getWorkflowsEnCours'])->middleware('permission:view_reunion_workflows');
        Route::get('execution/{executionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'getWorkflowExecution'])->middleware('permission:view_reunion_workflows');
        Route::post('cancel/{executionId}', [\App\Http\Controllers\Api\Reunion\ReunionWorkflowController::class, 'cancelWorkflow'])->middleware('permission:cancel_reunion_workflow');
    });

    // =================================================================
    // PHASE 3 : ORDRE DU JOUR
    // =================================================================
    Route::prefix('ordre-jour')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'getOrdreJour'])->middleware('permission:view_reunion_ordre_jour');
        Route::post('{reunionId}/points', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'addPoint'])->middleware('permission:create_reunion_ordre_jour');
        Route::put('points/{pointId}', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'updatePoint'])->middleware('permission:update_reunion_ordre_jour');
        Route::delete('points/{pointId}', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'deletePoint'])->middleware('permission:delete_reunion_ordre_jour');
        Route::post('{reunionId}/reorder', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'reorderPoints'])->middleware('permission:update_reunion_ordre_jour');
        Route::post('points/{pointId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'changeStatut'])->middleware('permission:update_reunion_ordre_jour');
        Route::get('{reunionId}/stats', [\App\Http\Controllers\Api\Reunion\ReunionOrdreJourController::class, 'getStats'])->middleware('permission:view_reunion_ordre_jour');
    });

    // =================================================================
    // PHASE 3 : DÉCISIONS
    // =================================================================
    Route::prefix('decisions')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'getDecisions'])->middleware('permission:view_reunion_decisions');
        Route::post('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'createDecision'])->middleware('permission:create_reunion_decisions');
        Route::put('{decisionId}', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'updateDecision'])->middleware('permission:update_reunion_decisions');
        Route::delete('{decisionId}', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'deleteDecision'])->middleware('permission:delete_reunion_decisions');
        Route::post('{decisionId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'changeStatutExecution'])->middleware('permission:update_reunion_decisions');
        Route::get('en-retard', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'getDecisionsEnRetard'])->middleware('permission:view_reunion_decisions');
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionDecisionController::class, 'getStats'])->middleware('permission:view_reunion_decisions');
    });

    // =================================================================
    // PHASE 3 : ACTIONS
    // =================================================================
    Route::prefix('actions')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'getActions'])->middleware('permission:view_reunion_actions');
        Route::post('/', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'createAction'])->middleware('permission:create_reunion_actions');
        Route::put('{actionId}', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'updateAction'])->middleware('permission:update_reunion_actions');
        Route::delete('{actionId}', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'deleteAction'])->middleware('permission:delete_reunion_actions');
        Route::post('{actionId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'changeStatut'])->middleware('permission:update_reunion_actions');
        Route::post('{actionId}/progression', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'updateProgression'])->middleware('permission:update_reunion_actions');
        Route::get('en-retard', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'getActionsEnRetard'])->middleware('permission:view_reunion_actions');
        Route::get('stats', [\App\Http\Controllers\Api\Reunion\ReunionActionController::class, 'getStats'])->middleware('permission:view_reunion_actions');
    });

    // =================================================================
    // PHASE 4 : SUJETS DE RÉUNION
    // =================================================================
    Route::prefix('sujets')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'getSujets'])->middleware('permission:view_reunion_sujets');
        Route::get('sujet/{sujetId}', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'getSujet'])->middleware('permission:view_reunion_sujets');
        Route::post('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'createSujet'])->middleware('permission:create_reunion_sujets');
        Route::put('{sujetId}', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'updateSujet'])->middleware('permission:update_reunion_sujets');
        Route::delete('{sujetId}', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'deleteSujet'])->middleware('permission:delete_reunion_sujets');
        Route::post('{sujetId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'changeStatut'])->middleware('permission:update_reunion_sujets');
        Route::post('{reunionId}/reorder', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'reorderSujets'])->middleware('permission:update_reunion_sujets');
        Route::get('{reunionId}/stats', [\App\Http\Controllers\Api\Reunion\ReunionSujetController::class, 'getStats'])->middleware('permission:view_reunion_sujets');
    });

    // =================================================================
    // PHASE 4 : OBJECTIFS DE RÉUNION
    // =================================================================
    Route::prefix('objectifs')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'getObjectifs'])->middleware('permission:view_reunion_objectifs');
        Route::get('objectif/{objectifId}', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'getObjectif'])->middleware('permission:view_reunion_objectifs');
        Route::post('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'createObjectif'])->middleware('permission:create_reunion_objectifs');
        Route::put('{objectifId}', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'updateObjectif'])->middleware('permission:update_reunion_objectifs');
        Route::delete('{objectifId}', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'deleteObjectif'])->middleware('permission:delete_reunion_objectifs');
        Route::post('{objectifId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'changeStatut'])->middleware('permission:update_reunion_objectifs');
        Route::post('{objectifId}/progression', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'updateProgression'])->middleware('permission:update_reunion_objectifs');
        Route::get('{reunionId}/stats', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'getStats'])->middleware('permission:view_reunion_objectifs');
        Route::get('{reunionId}/evaluation', [\App\Http\Controllers\Api\Reunion\ReunionObjectifController::class, 'evaluerRealisation'])->middleware('permission:view_reunion_objectifs');
    });

    // =================================================================
    // PHASE 4 : DIFFICULTÉS DE RÉUNION
    // =================================================================
    Route::prefix('difficultes')->group(function () {
        Route::get('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'getDifficultes'])->middleware('permission:view_reunion_difficultes');
        Route::get('difficulte/{difficulteId}', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'getDifficulte'])->middleware('permission:view_reunion_difficultes');
        Route::post('{reunionId}', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'createDifficulte'])->middleware('permission:create_reunion_difficultes');
        Route::put('{difficulteId}', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'updateDifficulte'])->middleware('permission:update_reunion_difficultes');
        Route::delete('{difficulteId}', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'deleteDifficulte'])->middleware('permission:delete_reunion_difficultes');
        Route::post('{difficulteId}/statut', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'changeStatut'])->middleware('permission:update_reunion_difficultes');
        Route::post('{difficulteId}/progression', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'updateProgressionResolution'])->middleware('permission:update_reunion_difficultes');
        Route::post('{difficulteId}/solution', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'ajouterSolution'])->middleware('permission:update_reunion_difficultes');
        Route::get('{reunionId}/stats', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'getStats'])->middleware('permission:view_reunion_difficultes');
        Route::get('{reunionId}/analyse-risques', [\App\Http\Controllers\Api\Reunion\ReunionDifficulteController::class, 'analyserRisques'])->middleware('permission:view_reunion_difficultes');
    });

});

// Auth reset password
Route::post('v1/auth/forgot-password', [PasswordResetController::class, 'forgot']);
Route::post('v1/auth/reset-password', [PasswordResetController::class, 'reset']);

// Admin reset user password
Route::post('v1/users/{id}/reset-password', [UserController::class, 'resetPassword'])->middleware(['auth:sanctum','permission:reset_user_password']);

// Activités de connexion
Route::prefix('v1/login-activities')->middleware(['auth:sanctum'])->group(function () {
    Route::get('user/{userId}', [LoginActivityController::class, 'getUserActivities'])->middleware('permission:view_user_login_activities');
    Route::get('user/{userId}/stats', [LoginActivityController::class, 'getUserStats'])->middleware('permission:view_user_login_activities');
    Route::get('global', [LoginActivityController::class, 'getGlobalActivities'])->middleware('permission:view_global_login_activities');
    Route::get('global/stats', [LoginActivityController::class, 'getGlobalStats'])->middleware('permission:view_global_login_activities');
    Route::get('recent', [LoginActivityController::class, 'getRecentActivities'])->middleware('permission:view_user_login_activities');
});


