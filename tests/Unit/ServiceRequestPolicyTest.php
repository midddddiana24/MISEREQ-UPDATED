<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\ServiceRequestPolicy;
use PHPUnit\Framework\TestCase;

class ServiceRequestPolicyTest extends TestCase
{
    public function test_owner_can_view_and_update_own_request(): void
    {
        $user = new User(['role' => 'user', 'is_admin' => false]);
        $user->id = 10;
        $request = new ServiceRequest(['user_id' => 10]);

        $policy = new ServiceRequestPolicy;
        $this->assertTrue($policy->view($user, $request));
        $this->assertTrue($policy->update($user, $request));
    }

    public function test_user_cannot_access_another_users_request(): void
    {
        $user = new User(['role' => 'user', 'is_admin' => false]);
        $user->id = 10;
        $request = new ServiceRequest(['user_id' => 20]);

        $policy = new ServiceRequestPolicy;
        $this->assertFalse($policy->view($user, $request));
        $this->assertFalse($policy->update($user, $request));
    }

    public function test_admin_can_manage_requests_but_locked_user_cannot_create(): void
    {
        $admin = new User(['role' => 'admin', 'is_admin' => true]);
        $admin->id = 1;
        $request = new ServiceRequest(['user_id' => 20]);

        $lockedUser = new class(['role' => 'user', 'is_admin' => false]) extends User
        {
            public function isAccountLocked(): bool
            {
                return true;
            }
        };

        $policy = new ServiceRequestPolicy;
        $this->assertTrue($policy->view($admin, $request));
        $this->assertTrue($policy->delete($admin, $request));
        $this->assertFalse($policy->create($lockedUser));
    }
}
