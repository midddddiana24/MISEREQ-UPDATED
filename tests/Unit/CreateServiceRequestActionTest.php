<?php

namespace Tests\Unit;

use App\Actions\CreateServiceRequest;
use App\DataTransferObjects\ServiceRequestCreationData;
use App\Models\ServiceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class CreateServiceRequestActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_action_creates_a_user_request_from_a_typed_dto(): void
    {
        $user = $this->createUser();
        $data = ServiceRequestCreationData::forUser($user, [
            'requester_name' => 'Typed User',
            'services' => ['troubleshoot_pc'],
            'description' => 'Created directly through the action.',
        ], []);

        $request = app(CreateServiceRequest::class)->execute($data);

        $this->assertSame($user->id, $request->user_id);
        $this->assertFalse($request->is_admin_created);
        $this->assertSame('pending', $request->status);
        $this->assertSame('ICT', $request->category);
        $this->assertDatabaseHas('request_status_logs', [
            'service_request_id' => $request->id,
            'status' => 'pending',
        ]);
    }

    public function test_action_handles_user_and_admin_dtos_through_the_same_path(): void
    {
        $user = $this->createUser();
        $admin = $this->createUser(['role' => 'admin', 'is_admin' => true]);

        $userRequest = app(CreateServiceRequest::class)->execute(
            ServiceRequestCreationData::forUser($user, [
                'services' => ['network_connection'],
                'description' => 'User-created request.',
            ], [])
        );
        $adminRequest = app(CreateServiceRequest::class)->execute(
            ServiceRequestCreationData::forAdmin($admin->id, [
                'requester_name' => 'Walk-in requester',
                'requester_office' => 'Library',
                'services' => ['account_creation'],
                'description' => 'Admin-created request.',
            ], [])
        );

        $this->assertSame($user->id, $userRequest->user_id);
        $this->assertNull($userRequest->created_by);
        $this->assertFalse($userRequest->is_admin_created);
        $this->assertSame('submitted', $adminRequest->status);
        $this->assertNull($adminRequest->user_id);
        $this->assertSame($admin->id, $adminRequest->created_by);
        $this->assertTrue($adminRequest->is_admin_created);
    }

    public function test_attachment_row_failure_rolls_back_request_and_status_history(): void
    {
        Storage::fake('secure');
        $user = $this->createUser();
        $file = UploadedFile::fake()->create('rollback.pdf', 10, 'application/pdf');
        $shouldFail = true;
        $statusLogCount = DB::table('request_status_logs')->count();

        DB::listen(function ($query) use (&$shouldFail): void {
            if ($shouldFail && str_contains(strtolower($query->sql), 'insert into `service_request_attachments`')) {
                $shouldFail = false;
                throw new \RuntimeException('Forced attachment persistence failure.');
            }
        });

        try {
            app(CreateServiceRequest::class)->execute(
                ServiceRequestCreationData::forUser($user, [
                    'services' => ['troubleshoot_pc'],
                    'description' => 'This transaction must roll back.',
                ], [$file])
            );
            $this->fail('Expected the forced attachment failure.');
        } catch (Throwable $exception) {
            $this->assertSame('Failed to upload file. Please try again.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('service_requests', [
            'description' => 'This transaction must roll back.',
        ]);
        $this->assertSame(0, ServiceRequest::where('description', 'This transaction must roll back.')->count());
        $this->assertSame($statusLogCount, DB::table('request_status_logs')->count());
    }

    public function test_action_preserves_duplicate_control_number_retry(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 18, 8, 40, 0));
        $this->beforeApplicationDestroyed(fn() => Carbon::setTestNow());
        $this->beforeApplicationDestroyed(fn() => Str::createRandomStringsUsing(null));
        $user = $this->createUser();
        $calls = [];
        Str::createRandomStringsUsing(function (int $length) use (&$calls): string {
            $value = count($calls) === 0 ? 'AAAAAA' : 'BBBBBB';
            $calls[] = $value;

            return $value;
        });
        $first = sprintf('SR-%s-%d-%s', now()->format('YmdHis'), $user->id, 'AAAAAA');
        ServiceRequest::create([
            'user_id' => $user->id,
            'control_number' => $first,
            'requester_name' => 'Existing request',
            'services' => ['troubleshoot_pc'],
            'status' => 'pending',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
        ]);

        $request = app(CreateServiceRequest::class)->execute(
            ServiceRequestCreationData::forUser($user, [
                'services' => ['troubleshoot_pc'],
                'description' => 'Retry through action.',
            ], [])
        );

        $this->assertSame(['AAAAAA', 'BBBBBB'], $calls);
        $this->assertSame(sprintf('SR-%s-%d-%s', now()->format('YmdHis'), $user->id, 'BBBBBB'), $request->control_number);
        $this->assertSame(1, ServiceRequest::where('control_number', $request->control_number)->count());
    }
}
