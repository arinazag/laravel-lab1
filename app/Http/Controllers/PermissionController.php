<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\DTO\PermissionDTO;
use App\DTO\PermissionCollectionDTO;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::paginate(20);
        $data = array_map([PermissionDTO::class, 'fromModel'], $permissions->items());
        return response()->json(new PermissionCollectionDTO($data, $permissions->total()));
    }

    public function show(Permission $permission): JsonResponse
    {
        return response()->json(PermissionDTO::fromModel($permission));
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);
        return response()->json(PermissionDTO::fromModel($permission), 201);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());
        return response()->json(PermissionDTO::fromModel($permission));
    }

    public function destroy(Permission $permission): JsonResponse
    {
        if (auth()->user()->cannot('delete-permission')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('delete-permission');
        }
        $permission->forceDelete();
        return response()->json(null, 204);
    }

    public function softDelete(Permission $permission): JsonResponse
    {
        if (auth()->user()->cannot('delete-permission')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('delete-permission');
        }
        $permission->delete();
        $permission->deleted_by = auth()->id();
        $permission->save();
        return response()->json(['message' => 'Permission soft deleted']);
    }

    public function restore($id): JsonResponse
    {
        if (auth()->user()->cannot('restore-permission')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('restore-permission');
        }
        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->restore();
        $permission->deleted_by = null;
        $permission->save();
        return response()->json(['message' => 'Permission restored']);
    }
}