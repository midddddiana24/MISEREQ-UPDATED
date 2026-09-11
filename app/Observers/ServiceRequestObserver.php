<?php

namespace App\Observers;

use App\Jobs\GeneratePdfJob;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceRequestObserver
{
    /**
     * Handle the ServiceRequest "updating" event.
     * This is called before the model is updated.
     */
    public function updating(ServiceRequest $serviceRequest): void
    {
        // Check if status is being changed
        if ($serviceRequest->isDirty('status')) {
            $oldStatus = $serviceRequest->getOriginal('status');
            $newStatus = $serviceRequest->getAttribute('status');

            // Store the status change info temporarily on the model
            $serviceRequest->_statusChangeData = [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ];
        }
    }

    /**
     * Handle the ServiceRequest "updated" event.
     * This is called after the model is updated.
     */
    public function updated(ServiceRequest $serviceRequest): void
    {
        // Check if status was changed
        if (isset($serviceRequest->_statusChangeData)) {
            $statusChangeData = $serviceRequest->_statusChangeData;

            // Only create log if status actually changed
            if ($statusChangeData['old_status'] !== $statusChangeData['new_status']) {
                // Use custom remarks if provided, otherwise generate default ones
                $remarks = $serviceRequest->_statusChangeRemarks
                    ?? ('Status changed from '.ucfirst(str_replace('-', ' ', $statusChangeData['old_status'])).' to '.ucfirst(str_replace('-', ' ', $statusChangeData['new_status'])));

                // Create status log entry automatically
                $serviceRequest->statusLogs()->create([
                    'status' => $statusChangeData['new_status'],
                    'remarks' => $remarks,
                    'updated_by' => Auth::id() ?? null,
                ]);
            }

            // Clean up temporary data
            unset($serviceRequest->_statusChangeData);
            unset($serviceRequest->_statusChangeRemarks);
        }

        GeneratePdfJob::dispatch($serviceRequest->id)->afterCommit();
    }

    /**
     * Handle the ServiceRequest "created" event.
     */
    public function created(ServiceRequest $serviceRequest): void
    {
        // Create initial status log for newly created requests
        $serviceRequest->statusLogs()->create([
            'status' => $serviceRequest->status ?? 'pending',
            'remarks' => 'Request created',
            'updated_by' => Auth::id() ?? null,
        ]);

        GeneratePdfJob::dispatch($serviceRequest->id)->afterCommit();
    }
}
