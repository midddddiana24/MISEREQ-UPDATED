<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\ServiceRequest::class => \App\Policies\ServiceRequestPolicy::class,
        \App\Models\IctIncidentReport::class => \App\Policies\IctIncidentReportPolicy::class,
        \App\Models\PreventiveMaintenanceChecklist::class => \App\Policies\PreventiveMaintenanceChecklistPolicy::class,
        \App\Models\ClarificationAttachment::class => \App\Policies\ClarificationAttachmentPolicy::class,
        \App\Models\Message::class => \App\Policies\MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
