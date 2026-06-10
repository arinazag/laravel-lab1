<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;



Route::prefix('auth')->group(function () {
  Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    
    Route::prefix('ref')->middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('out', [AuthController::class], 'out');
        Route::get('tokens', [AuthController::class, 'tokens']);
        Route::post('out_all', [AuthController::class, 'outAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

Route::prefix('ref')->middleware('auth:sanctum')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('{user}/role', [UserRoleController::class, 'index']);
        Route::post('{user}/role', [UserRoleController::class, 'attach']);
        Route::delete('{user}/role/{role}', [UserRoleController::class, 'detach']);
        Route::delete('{user}/role/{role}/soft', [UserRoleController::class, 'softDelete']);
        Route::post('{user}/role/{role}/restore', [UserRoleController::class, 'restoreUserRole']);
        Route::get('/', [UserController::class, 'index']); 
    });

    Route::prefix('policy/role')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('{role}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('{role}', [RoleController::class, 'update']);
        Route::patch('{role}', [RoleController::class, 'update']);
        Route::delete('{role}', [RoleController::class, 'destroy']);
        Route::delete('{role}/soft', [RoleController::class, 'softDelete']);
        Route::post('{role}/restore', [RoleController::class, 'restore']);
    });

    Route::prefix('policy/permission')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('{permission}', [PermissionController::class, 'show']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::put('{permission}', [PermissionController::class, 'update']);
        Route::patch('{permission}', [PermissionController::class, 'update']);
        Route::delete('{permission}', [PermissionController::class, 'destroy']);
        Route::delete('{permission}/soft', [PermissionController::class, 'softDelete']);
        Route::post('{permission}/restore', [PermissionController::class, 'restore']);
    });
});