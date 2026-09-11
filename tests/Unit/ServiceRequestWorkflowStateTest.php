<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Services\AdminActivityLogger;
use App\Services\EmailNotificationService;
use App\Services\ServiceRequestWorkflow;
use PHPUnit\Framework\TestCase;

class ServiceRequestWorkflowStateTest extends TestCase
{
    private static $application;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$application = require dirname(__DIR__, 2) . '/bootstrap/app.php';
        self::$application->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }

    private function workflow(): ServiceRequestWorkflow
    {
        return new ServiceRequestWorkflow(
            $this->createMock(EmailNotificationService::class),
            $this->createMock(AdminActivityLogger::class),
        );
    }

    public function test_client_admin_sequence_has_all_required_transitions(): void
    {
        $workflow = $this->workflow();

        $request = new ServiceRequest(['status' => 'submitted']);
        $this->assertContains('pending', $workflow->allowedTransitions($request));

        $request->status = 'pending';
        $this->assertContains('awaiting_requester_info', $workflow->allowedTransitions($request));

        $request->status = 'awaiting_requester_info';
        $this->assertContains('pending', $workflow->allowedTransitions($request));
        $this->assertContains('processing', $workflow->allowedTransitions($request));

        $request->status = 'processing';
        $this->assertContains('awaiting_confirmation', $workflow->allowedTransitions($request));

        $request->status = 'awaiting_confirmation';
        $this->assertContains('closed', $workflow->allowedTransitions($request));
        $this->assertContains('reopened', $workflow->allowedTransitions($request));
    }

    public function test_responsibility_label_is_clear_for_requester(): void
    {
        $request = new ServiceRequest(['status' => 'awaiting_requester_info']);
        $this->assertSame('Waiting for you', $request->responsibility_label);

        $request->status = 'processing';
        $this->assertSame('Waiting for MIS', $request->responsibility_label);
    }

    public function test_closed_request_can_only_be_reopened_for_fourteen_days(): void
    {
        $request = new ServiceRequest(['status' => 'closed']);
        $request->closed_at = now()->subDays(13);
        $this->assertTrue($request->canBeReopenedByUser());

        $request->closed_at = now()->subDays(15);
        $this->assertFalse($request->canBeReopenedByUser());
    }

    public function test_sla_is_paused_while_waiting_for_requester(): void
    {
        $request = new ServiceRequest([
            'status' => 'awaiting_requester_info',
            'expected_completion_at' => now()->subHour(),
        ]);

        $this->assertFalse($request->is_overdue);
        $this->assertSame('Paused while waiting for requester', $request->sla_label);
    }
}
