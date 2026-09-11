<?php

namespace App\Providers;

use App\Models\ClarificationAttachment;
use App\Models\IctIncidentReport;
use App\Models\Message;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\ClarificationAttachmentPolicy;
use App\Policies\IctIncidentReportPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PreventiveMaintenanceChecklistPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        ServiceRequest::class => ServiceRequestPolicy::class,
        IctIncidentReport::class => IctIncidentReportPolicy::class,
        PreventiveMaintenanceChecklist::class => PreventiveMaintenanceChecklistPolicy::class,
        ClarificationAttachment::class => ClarificationAttachmentPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
