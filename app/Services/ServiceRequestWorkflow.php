<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceRequestWorkflow
{
    private const TRANSITIONS = [
        'submitted' => ['pending', 'processing', 'awaiting_requester_info', 'rejected', 'cancelled'],
        'pending' => ['processing', 'awaiting_requester_info', 'rejected', 'cancelled', 'escalated'],
        'processing' => ['pending', 'awaiting_requester_info', 'awaiting_confirmation', 'rejected', 'cancelled', 'escalated'],
        'awaiting_requester_info' => ['pending', 'processing', 'reopened', 'escalated', 'cancelled', 'rejected'],
        'awaiting_confirmation' => ['closed', 'reopened', 'processing'],
        'completed' => ['closed', 'reopened'],
        'closed' => ['reopened'],
        'reopened' => ['processing', 'awaiting_requester_info', 'awaiting_confirmation', 'rejected', 'cancelled', 'escalated'],
        'escalated' => ['processing', 'awaiting_requester_info', 'awaiting_confirmation', 'rejected', 'cancelled'],
        'rejected' => ['reopened'],
        'cancelled' => ['reopened'],
    ];

    public function __construct(
        private readonly EmailNotificationService $notifications,
        private readonly AdminActivityLogger $activityLogger
    ) {}

    public function allowedTransitions(ServiceRequest $serviceRequest): array
    {
        return self::TRANSITIONS[$serviceRequest->status] ?? [];
    }

    public function transition(
        ServiceRequest $serviceRequest,
        string $newStatus,
        ?string $reason = null,
        array $additionalData = [],
        bool $notify = true,
        ?int $expectedVersion = null
    ): ServiceRequest {
        $oldStatus = $serviceRequest->status;

        if ($oldStatus === $newStatus) {
            return $serviceRequest;
        }

        if (! in_array($newStatus, $this->allowedTransitions($serviceRequest), true)) {
            throw ValidationException::withMessages([
                'status' => "A request cannot move from {$this->label($oldStatus)} to {$this->label($newStatus)}.",
            ]);
        }

        if (
            in_array($newStatus, ['processing', 'awaiting_confirmation', 'completed', 'closed'], true)
            && ! $serviceRequest->assigned_technician_id
        ) {
            throw ValidationException::withMessages([
                'status' => 'Assign a technician before moving this request to the selected status.',
            ]);
        }

        if ($newStatus === 'awaiting_confirmation' && $oldStatus !== 'processing') {
            throw ValidationException::withMessages([
                'status' => 'Move the assigned request to In Progress before marking the work complete.',
            ]);
        }

        if (
            in_array($newStatus, ['rejected', 'cancelled', 'reopened', 'escalated'], true)
            && blank($reason)
        ) {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required for this workflow action.',
            ]);
        }

        $updateData = array_merge($additionalData, $this->timestampsFor($serviceRequest, $newStatus, $reason));
        $updateData['status'] = $newStatus;

        DB::transaction(function () use ($serviceRequest, $oldStatus, $newStatus, $reason, $updateData, $expectedVersion) {
            $query = $serviceRequest->newQuery()->whereKey($serviceRequest->id);
            if ($expectedVersion !== null) {
                $query->where('version', $expectedVersion);
            }

            $nextVersion = ($serviceRequest->version ?? 0) + 1;
            $updated = $query->lockForUpdate()->update(array_merge($updateData, ['version' => $nextVersion]));

            if ($expectedVersion !== null && $updated === 0) {
                throw ValidationException::withMessages([
                    'version' => 'This request was already updated by someone else. Please refresh and review the current state before acting again.',
                ]);
            }

            $serviceRequest->_statusChangeRemarks = $reason
                ?: "Status changed from {$this->label($oldStatus)} to {$this->label($newStatus)}";
            $serviceRequest->refresh();

            $this->activityLogger->log(
                $serviceRequest,
                'status_transition',
                "Request moved from {$this->label($oldStatus)} to {$this->label($newStatus)}.",
                ['status' => $oldStatus],
                ['status' => $newStatus, 'reason' => $reason]
            );
        });

        $serviceRequest->assignedTechnician?->updateWorkload();

        if ($notify && $serviceRequest->user?->email) {
            if (in_array($newStatus, ['completed', 'closed'], true)) {
                $this->notifications->sendRequestCompletedNotification($serviceRequest);
            } else {
                $this->notifications->sendStatusUpdateNotification($serviceRequest, $oldStatus, $newStatus, $reason);
            }
        }

        return $serviceRequest->refresh();
    }

    private function timestampsFor(ServiceRequest $serviceRequest, string $newStatus, ?string $reason): array
    {
        $data = match ($newStatus) {
            'processing' => $serviceRequest->date_responded ? [] : ['date_responded' => now()],
            'awaiting_requester_info' => ['status_before_clarification' => $serviceRequest->status],
            'awaiting_confirmation' => [
                'work_completed_at' => now(),
                'date_completed' => $serviceRequest->date_completed ?: now(),
            ],
            'completed', 'closed' => [
                'resolution_confirmed_at' => now(),
                'closed_at' => now(),
                'date_completed' => $serviceRequest->date_completed ?: now(),
            ],
            'reopened' => [
                'reopened_at' => now(),
                'work_completed_at' => null,
                'resolution_confirmed_at' => null,
                'closed_at' => null,
                'date_completed' => null,
            ],
            'escalated' => [
                'escalated_at' => now(),
                'escalation_reason' => $reason,
            ],
            default => [],
        };

        return $data;
    }

    private function label(string $status): string
    {
        return str($status)->replace('_', ' ')->title()->toString();
    }
}
