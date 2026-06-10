<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
   public function boot()
   {
         foreach (\App\Models\Permission::all() as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission->slug, function ($user) use ($permission) {
               return $user->roles->flatMap->permissions->contains('slug', $permission->slug);
            });
         }
    }
}