<?php

namespace Tests\Feature;

use App\Actions\CreateServiceRequest;
use App\DataTransferObjects\ServiceRequestCreationData;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceRequestControlNumberRetryTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Str::createRandomStringsUsing(null);

        parent::tearDown();
    }

    public function test_duplicate_control_number_triggers_retry_and_succeeds_without_propagating_exception(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 13, 16, 52, 47));

        $owner = $this->createUser();
        $calls = [];

        Str::createRandomStringsUsing(function (int $length) use (&$calls): string {
            $value = count($calls) === 0 ? 'AAAAAA' : 'BBBBBB';
            $calls[] = $value;

            return $value;
        });

        $baseAttributes = [
            'user_id' => $owner->id,
            'requester_name' => 'Retry Test User',
            'requester_office' => 'MIS Office',
            'services' => ['troubleshoot_pc'],
            'description' => 'Forced duplicate control number retry test',
            'property_no' => 'RETRY-001',
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i'),
        ];

        $conflictingControlNumber = sprintf('SR-%s-%d-%s', now()->format('YmdHis'), $owner->id, 'AAAAAA');
        ServiceRequest::create([
            ...$baseAttributes,
            'control_number' => $conflictingControlNumber,
            'version' => 0,
        ]);

        $request = app(CreateServiceRequest::class)->execute(
            ServiceRequestCreationData::forUser($owner, [
                'requester_name' => 'Retry Test User',
                'requester_office' => 'MIS Office',
                'services' => ['troubleshoot_pc'],
                'description' => 'Forced duplicate control number retry test',
                'property_no' => 'RETRY-001',
            ], [])
        );

        $this->assertSame(sprintf('SR-%s-%d-%s', now()->format('YmdHis'), $owner->id, 'BBBBBB'), $request->control_number);
        $this->assertSame(['AAAAAA', 'BBBBBB'], $calls);

        $this->assertDatabaseHas('service_requests', ['control_number' => $request->control_number, 'user_id' => $owner->id]);
        $this->assertSame(1, ServiceRequest::where('control_number', $request->control_number)->count());
        $this->assertDatabaseHas('service_requests', ['control_number' => $conflictingControlNumber, 'user_id' => $owner->id]);
        $this->assertSame(2, ServiceRequest::where('user_id', $owner->id)->count());
    }
}
