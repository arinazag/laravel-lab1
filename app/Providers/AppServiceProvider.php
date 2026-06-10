<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
    }

    public function boot(): void
    {
        
        foreach (Permission::all() as $permission) {
            Gate::define($permission->slug, function ($user) use ($permission) {
                return $user->roles->flatMap->permissions->contains('slug', $permission->slug);
            });
        }
    }
}