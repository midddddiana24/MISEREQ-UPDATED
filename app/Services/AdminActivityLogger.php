<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class AdminActivityLogger
{
    public function log(
        ServiceRequest $serviceRequest,
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): AdminActivityLog {
        return AdminActivityLog::create([
            'service_request_id' => $serviceRequest->id,
            'actor_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }

    public function logAdministrative(
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): AdminActivityLog {
        return AdminActivityLog::create([
            'service_request_id' => null,
            'actor_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
