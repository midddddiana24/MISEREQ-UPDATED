<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\ServiceRequest;
use App\Policies\UserPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Rules\ScanMalware;
use App\Observers\ServiceRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('security.force_https', false)) {
            URL::forceScheme('https');
        }

        // Register Model Observers
        ServiceRequest::observe(ServiceRequestObserver::class);

        // Register custom validation rules
        Validator::extend('scan_malware', function ($attribute, $value, $parameters, $validator) {
            $rule = new ScanMalware();
            // The rule will be invoked during validation
            return true; // Laravel will call the rule's validate method
        });

        // Register Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);

        // Define Gates for role-based access
        Gate::define('is-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('is-it-staff', function (User $user) {
            return $user->role === 'it_staff' || $user->isAdmin();
        });

        Gate::define('can-manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('can-manage-requests', function (User $user) {
            return $user->isAdmin() || $user->role === 'it_staff';
        });

        Gate::define('can-view-reports', function (User $user) {
            return $user->isAdmin() || $user->role === 'it_staff';
        });

        Gate::define('can-export-data', function (User $user) {
            return $user->isAdmin();
        });
    }
}
