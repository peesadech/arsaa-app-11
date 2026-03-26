<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PermissionTypeController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::middleware('auth:api,web')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api,web')->group(function () {
    Route::get('/permission-types/search/{keyword}', [PermissionTypeController::class, 'search']);
    Route::apiResource('permission-types', PermissionTypeController::class);

    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    Route::apiResource('users', UserController::class);
    Route::get('/users-with-roles', [UserRoleController::class, 'index']);
    Route::put('/users/{user}/roles', [UserRoleController::class, 'update']);
});
