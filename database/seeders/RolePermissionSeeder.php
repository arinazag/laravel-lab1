<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();
        $guest = Role::where('slug', 'guest')->first();

        $allPermissions = Permission::pluck('id');
        $userPermissions = Permission::whereIn('slug', [
            'get-list-user', 'read-user', 'update-user'
        ])->pluck('id');
        $guestPermissions = Permission::where('slug', 'get-list-user')->pluck('id');

        if ($admin) {
            $admin->permissions()->sync(
                $allPermissions->mapWithKeys(fn($id) => [$id => ['created_by' => 1]])->toArray()
            );
        }
        if ($userRole) {
            $userRole->permissions()->sync(
                $userPermissions->mapWithKeys(fn($id) => [$id => ['created_by' => 1]])->toArray()
            );
        }
        if ($guest) {
            $guest->permissions()->sync(
                $guestPermissions->mapWithKeys(fn($id) => [$id => ['created_by' => 1]])->toArray()
            );
        }
    }
}