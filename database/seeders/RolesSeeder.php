<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate(['slug' => 'admin'], ['name' => 'Admin', 'created_by' => 1]);
        Role::updateOrCreate(['slug' => 'user'], ['name' => 'User', 'created_by' => 1]);
        Role::updateOrCreate(['slug' => 'guest'], ['name' => 'Guest', 'created_by' => 1]);
    }
}