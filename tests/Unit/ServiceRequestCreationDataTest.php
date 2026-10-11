<?php

namespace Tests\Unit;

use App\DataTransferObjects\ServiceRequestCreationData;
use Tests\TestCase;

class ServiceRequestCreationDataTest extends TestCase
{
    public function test_user_constructor_sets_user_flow_defaults_and_category(): void
    {
        $user = $this->createUser([
            'designation' => 'Staff',
            'office' => 'Registrar',
            'phone' => '555-0100',
        ]);

        $data = ServiceRequestCreationData::forUser($user, [
            'services' => ['new_network_computer'],
        ], []);

        $this->assertSame($user->id, $data->userId);
        $this->assertNull($data->createdBy);
        $this->assertFalse($data->adminCreated);
        $this->assertSame('Staff', $data->requesterDesignation);
        $this->assertSame('Registrar', $data->office);
        $this->assertSame('Network', $data->category);
        $this->assertSame('pending', $data->status);
    }

    public function test_admin_constructor_sets_admin_flow_identity_and_status(): void
    {
        $data = ServiceRequestCreationData::forAdmin(42, [
            'requester_name' => 'Walk-in requester',
            'services' => ['account_creation'],
        ], []);

        $this->assertNull($data->userId);
        $this->assertSame(42, $data->createdBy);
        $this->assertTrue($data->adminCreated);
        $this->assertSame('submitted', $data->status);
        $this->assertSame('MIS', $data->category);
    }

    public function test_user_constructor_handles_optional_input_without_performing_validation(): void
    {
        $user = $this->createUser();
        $data = ServiceRequestCreationData::forUser($user, [], []);

        $this->assertSame([], $data->services);
        $this->assertSame($user->full_name, $data->requesterName);
        $this->assertNull($data->description);
        $this->assertSame('General', $data->category);
    }
}
