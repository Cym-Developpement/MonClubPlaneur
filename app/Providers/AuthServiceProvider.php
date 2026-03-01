<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::define('admin', function ($user) {
            return $user->isAdmin == 1;
        });

        Gate::define('debug', function ($user) {
            return $user->name == 'Challet Yann';
        });

        // Gestion dynamique des permissions préfixées : can('admin:backups'), can('pilote:campagne'), etc.
        Gate::before(function ($user, $ability) {
            if (!str_contains($ability, ':')) {
                return null; // Laisse les autres gates gérer
            }

            [$prefix] = explode(':', $ability, 2);

            // Pour admin:*, l'utilisateur doit être admin
            if ($prefix === 'admin' && $user->isAdmin != 1) {
                return false;
            }

            return $user->isAttr($ability);
        });
    }
}
