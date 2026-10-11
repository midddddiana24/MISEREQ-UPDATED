<?php

namespace Tests\Unit;

use App\DataTransferObjects\ServiceRequestCreationData;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AdminActivityLogger;
use App\Services\EmailNotificationService;
use App\Services\ServiceRequestWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserSideLogicBoundaryTest extends TestCase
{
    #[DataProvider('serviceCategoryProvider')]
    public function test_user_dto_infers_service_categories(array $services, string $expected): void
    {
        $user = new User([
            'first_name' => 'Route',
            'last_name' => 'Tester',
            'office' => 'MIS',
        ]);
        $user->id = 321;

        $data = ServiceRequestCreationData::forUser($user, ['services' => $services], []);

        $this->assertSame($expected, $data->category);
        $this->assertSame($services, $data->services);
    }

    public static function serviceCategoryProvider(): array
    {
        return [
            'empty services' => [[], 'General'],
            'network prefix' => [['new_network_computer'], 'Network'],
            'network keyword' => [['data_cabling'], 'Network'],
            'ict troubleshoot' => [['troubleshoot_pc'], 'ICT'],
            'mis account' => [['account_creation'], 'MIS'],
            'unknown service' => [['custom_service'], 'General'],
        ];
    }

    public function test_user_dto_preserves_null_empty_and_max_length_boundaries(): void
    {
        $user = new User([
            'first_name' => 'Boundary',
            'last_name' => 'Tester',
            'office' => null,
            'phone' => null,
        ]);
        $user->id = 654;
        $maxName = str_repeat('N', 255);
        $maxDescription = str_repeat('D', 5000);

        $data = ServiceRequestCreationData::forUser($user, [
            'requester_name' => $maxName,
            'services' => [],
            'description' => $maxDescription,
            'property_no' => '',
        ], []);

        $this->assertSame($maxName, $data->requesterName);
        $this->assertSame($maxDescription, $data->description);
        $this->assertSame('', $data->propertyNo);
        $this->assertNull($data->office);
        $this->assertNull($data->phone);
    }

    #[DataProvider('statusPresentationProvider')]
    public function test_status_and_responsibility_labels_cover_user_visible_states(
        string $status,
        string $statusLabel,
        string $responsibility
    ): void {
        $request = new ServiceRequest(['status' => $status]);

        $this->assertSame($statusLabel, $request->status_label);
        $this->assertSame($responsibility, $request->responsibility_label);
    }

    public static function statusPresentationProvider(): array
    {
        return [
            ['submitted', 'Submitted', 'Waiting for MIS'],
            ['pending', 'Pending', 'Waiting for MIS'],
            ['processing', 'Processing', 'Waiting for MIS'],
            ['awaiting_requester_info', 'Response Required', 'Waiting for you'],
            ['awaiting_confirmation', 'Awaiting Confirmation', 'Waiting for you'],
            ['closed', 'Closed', 'Completed'],
            ['cancelled', 'Cancelled', 'Cancelled'],
            ['rejected', 'Rejected', 'Closed by MIS'],
            ['unexpected_state', 'Unexpected_state', 'Status updated'],
        ];
    }

    #[DataProvider('editAndCancelProvider')]
    public function test_user_edit_and_cancel_boundaries(
        string $status,
        ?int $technicianId,
        bool $canEdit,
        bool $canCancel
    ): void {
        $request = new ServiceRequest([
            'status' => $status,
            'assigned_technician_id' => $technicianId,
        ]);

        $this->assertSame($canEdit, $request->canBeEditedByUser());
        $this->assertSame($canCancel, $request->canBeCancelledByUser());
    }

    public static function editAndCancelProvider(): array
    {
        return [
            'pending unassigned' => ['pending', null, true, true],
            'submitted unassigned' => ['submitted', null, false, true],
            'pending assigned' => ['pending', 10, false, false],
            'processing unassigned' => ['processing', null, false, false],
            'closed unassigned' => ['closed', null, false, false],
        ];
    }

    #[DataProvider('workflowTransitionProvider')]
    public function test_workflow_allowed_transitions_are_exposed_for_each_boundary_state(
        string $status,
        array $expected
    ): void {
        $workflow = new ServiceRequestWorkflow(
            $this->createMock(EmailNotificationService::class),
            $this->createMock(AdminActivityLogger::class),
        );

        $this->assertSame($expected, $workflow->allowedTransitions(new ServiceRequest(['status' => $status])));
    }

    public static function workflowTransitionProvider(): array
    {
        return [
            'new request' => ['submitted', ['pending', 'processing', 'awaiting_requester_info', 'rejected', 'cancelled']],
            'waiting for requester' => ['awaiting_requester_info', ['pending', 'processing', 'reopened', 'escalated', 'cancelled', 'rejected']],
            'waiting for confirmation' => ['awaiting_confirmation', ['closed', 'reopened', 'processing']],
            'closed' => ['closed', ['reopened']],
            'unknown' => ['unknown', []],
            'empty' => ['', []],
        ];
    }
}
