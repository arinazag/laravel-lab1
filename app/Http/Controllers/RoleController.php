<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\DTO\RoleDTO;
use App\DTO\RoleCollectionDTO;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::paginate(20);
        $data = array_map([RoleDTO::class, 'fromModel'], $roles->items());
        return response()->json(new RoleCollectionDTO($data, $roles->total()));
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(RoleDTO::fromModel($role));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);
        return response()->json(RoleDTO::fromModel($role), 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());
        return response()->json(RoleDTO::fromModel($role));
    }

    // Жёсткое удаление
    public function destroy(Role $role): JsonResponse
    {
        if (auth()->user()->cannot('delete-role')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('delete-role');
        }
        $role->forceDelete(); // физическое удаление
        return response()->json(null, 204);
    }

    // Мягкое удаление
    public function softDelete(Role $role): JsonResponse
    {
        if (auth()->user()->cannot('delete-role')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('delete-role');
        }
        $role->delete();
        $role->deleted_by = auth()->id();
        $role->save();
        return response()->json(['message' => 'Role soft deleted']);
    }

    // Восстановление
    public function restore($id): JsonResponse
    {
        if (auth()->user()->cannot('restore-role')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('restore-role');
        }
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();
        $role->deleted_by = null;
        $role->save();
        return response()->json(['message' => 'Role restored']);
    }
}