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
    Route::post('type-entites', [TypeEntiteController::class, 'store'])->middleware('permission:manage_entities');
    Route::put('type-entites/{id}', [TypeEntiteController::class, 'update'])->middleware('permission:manage_entities');
    Route::delete('type-entites/{id}', [TypeEntiteController::class, 'destroy'])->middleware('permission:manage_entities');

    // Entités - routes spéciales d'abord (avant {id})
    Route::get('entites/organigramme', [EntiteController::class, 'organigramme']); // Lecture libre
    Route::get('entites/chefs-actuels', [EntiteController::class, 'chefsActuels']); // Lecture libre

    // Entités
    Route::get('entites', [EntiteController::class, 'index']); // Lecture libre
    Route::get('entites/{id}', [EntiteController::class, 'show']); // Lecture libre
    Route::get('entites/{id}/enfants', [EntiteController::class, 'enfants']); // Lecture libre
    Route::get('entites/{id}/hierarchy', [EntiteController::class, 'hierarchy']); // Lecture libre
    Route::post('entites', [EntiteController::class, 'store'])->middleware('permission:manage_entities');
    Route::put('entites/{id}', [EntiteController::class, 'update'])->middleware('permission:manage_entities');
    Route::delete('entites/{id}', [EntiteController::class, 'destroy'])->middleware('permission:manage_entities');

    // Gestion des chefs d'entités
    Route::post('entites/{id}/affecter-chef', [EntiteController::class, 'affecterChef'])->middleware('permission:manage_entities');
    Route::post('entites/{id}/terminer-mandat-chef', [EntiteController::class, 'terminerMandatChef'])->middleware('permission:manage_entities');
    Route::get('entites/{id}/historique-chefs', [EntiteController::class, 'historiqueChefs']); // Lecture libre

    // Postes
    Route::get('postes', [PosteController::class, 'index']); // Lecture libre
    Route::get('postes/{id}', [PosteController::class, 'show']); // Lecture libre
    Route::post('postes', [PosteController::class, 'store'])->middleware('permission:manage_users');
    Route::put('postes/{id}', [PosteController::class, 'update'])->middleware('permission:manage_users');
    Route::delete('postes/{id}', [PosteController::class, 'destroy'])->middleware('permission:manage_users');

    // Utilisateurs
    Route::get('users', [UserController::class, 'index']); // Lecture libre (avec filtres selon permissions)
    Route::get('users/{id}', [UserController::class, 'show']); // Lecture libre
    Route::get('users/{id}/affectations', [UserController::class, 'affectations']); // Lecture libre
    Route::post('users', [UserController::class, 'store'])->middleware('permission:manage_users');
    Route::put('users/{id}', [UserController::class, 'update'])->middleware('permission:manage_users');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('permission:manage_users');
    Route::post('users/{id}/affecter', [UserController::class, 'affecter'])->middleware('permission:manage_users');
    Route::post('users/{id}/terminer-affectation', [UserController::class, 'terminerAffectation'])->middleware('permission:manage_users');
    Route::post('users/{id}/assign-role', [UserController::class, 'assignRole'])->middleware('permission:manage_users');
    Route::delete('users/{userId}/roles/{roleId}', [UserController::class, 'removeRole'])->middleware('permission:manage_users');

    // Rôles
    Route::get('roles', [RoleController::class, 'index']); // Lecture libre
    Route::get('roles/{id}', [RoleController::class, 'show']); // Lecture libre
    Route::get('roles/{id}/available-permissions', [RoleController::class, 'availablePermissions']); // Lecture libre
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:manage_users');
    Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('permission:manage_users');
    Route::delete('roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:manage_users');
    Route::post('roles/{id}/assign-permission', [RoleController::class, 'assignPermission'])->middleware('permission:manage_users');
    Route::delete('roles/{roleId}/permissions/{permissionId}', [RoleController::class, 'removePermission'])->middleware('permission:manage_users');

    // Permissions
    Route::get('permissions', [PermissionController::class, 'index']); // Lecture libre
    Route::get('permissions/{id}', [PermissionController::class, 'show']); // Lecture libre
    Route::get('permissions/{id}/users', [PermissionController::class, 'users']); // Lecture libre
    Route::get('permissions/{id}/available-roles', [PermissionController::class, 'availableRoles']); // Lecture libre
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:manage_users');
    Route::put('permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:manage_users');
    Route::delete('permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:manage_users');

});
