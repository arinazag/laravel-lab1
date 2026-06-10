<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $actions = ['get-list', 'read', 'create', 'update', 'delete', 'restore'];
        $entities = ['user', 'role', 'permission'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    ['slug' => "{$action}-{$entity}"],
                    ['name' => ucfirst($action) . ' ' . ucfirst($entity), 'created_by' => 1]
                );
            }
        }
    }
}