<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole && $user = User::find(1)) {
            $user->roles()->syncWithoutDetaching([$adminRole->id => ['created_by' => 1]]);
        }
    }
}