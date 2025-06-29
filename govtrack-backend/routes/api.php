<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TypeEntiteController;
use App\Http\Controllers\Api\EntiteController;
use App\Http\Controllers\Api\PosteController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes pour la partie 1 - Gestion des utilisateurs
Route::prefix('v1')->group(function () {

    // Types d'entité
    Route::apiResource('type-entites', TypeEntiteController::class);

        // Entités - routes spéciales d'abord (avant {id})
    Route::get('entites/organigramme', [EntiteController::class, 'organigramme']);
    Route::get('entites/chefs-actuels', [EntiteController::class, 'chefsActuels']);

    Route::apiResource('entites', EntiteController::class);
    Route::get('entites/{id}/enfants', [EntiteController::class, 'enfants']);
    Route::get('entites/{id}/hierarchy', [EntiteController::class, 'hierarchy']);

    // Gestion des chefs d'entités
    Route::post('entites/{id}/affecter-chef', [EntiteController::class, 'affecterChef']);
    Route::post('entites/{id}/terminer-mandat-chef', [EntiteController::class, 'terminerMandatChef']);
    Route::get('entites/{id}/historique-chefs', [EntiteController::class, 'historiqueChefs']);

    // Postes
    Route::apiResource('postes', PosteController::class);

    // Utilisateurs
    Route::apiResource('users', UserController::class);
    Route::get('users/{id}/affectations', [UserController::class, 'affectations']);
    Route::post('users/{id}/affecter', [UserController::class, 'affecter']);
    Route::post('users/{id}/terminer-affectation', [UserController::class, 'terminerAffectation']);
    Route::post('users/{id}/assign-role', [UserController::class, 'assignRole']);
    Route::delete('users/{userId}/roles/{roleId}', [UserController::class, 'removeRole']);

    // Rôles
    Route::apiResource('roles', RoleController::class);
    Route::get('roles/{id}/available-permissions', [RoleController::class, 'availablePermissions']);
    Route::post('roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);
    Route::delete('roles/{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission']);

    // Permissions
    Route::apiResource('permissions', PermissionController::class);
    Route::get('permissions/{id}/users', [PermissionController::class, 'users']);
    Route::get('permissions/{id}/available-roles', [PermissionController::class, 'availableRoles']);

});
