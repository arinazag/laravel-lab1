<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\DTO\RoleDTO;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\AttachUserRoleRequest;
use App\Http\Requests\DetachUserRoleRequest;
use App\Http\Requests\RestoreUserRoleRequest;

class UserRoleController extends Controller
{
    // Получить роли пользователя (возвращаем DTO)
    public function index(User $user): JsonResponse
    {
        $roles = $user->roles()->get();
        $data = $roles->map(fn($role) => RoleDTO::fromModel($role))->values();
        return response()->json($data);
    }

    // Назначить роль пользователю (используем AttachUserRoleRequest)
    public function attach(AttachUserRoleRequest $request, User $user): JsonResponse
    {
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'created_by' => auth()->id(),
        ]);
        return response()->json(['message' => 'Role attached'], 201);
    }

    // Жёсткое удаление связи
    public function detach(DetachUserRoleRequest $request, User $user, Role $role): JsonResponse
    {
        $user->roles()->wherePivot('role_id', $role->id)->forceDelete();
        return response()->json(null, 204);
    }

    // Мягкое удаление связи
    public function softDelete(DetachUserRoleRequest $request, User $user, Role $role): JsonResponse
    {
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        if (!$userRole) {
            return response()->json(['message' => 'Relation not found'], 404);
        }

        $userRole->delete();
        $userRole->deleted_by = auth()->id();
        $userRole->save();

        return response()->json(['message' => 'Role soft deleted']);
    }

    // Восстановление мягко удалённой связи
    public function restoreUserRole(RestoreUserRoleRequest $request, User $user, Role $role): JsonResponse
    {
        $userRole = UserRole::withTrashed()
            ->where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        if (!$userRole || !$userRole->trashed()) {
            return response()->json(['message' => 'Soft deleted relation not found'], 404);
        }

        $userRole->restore();
        $userRole->deleted_by = null;
        $userRole->save();

        return response()->json(['message' => 'Role restored for user']);
    }
}