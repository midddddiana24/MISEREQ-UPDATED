<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use PHPUnit\Framework\TestCase;

class ServiceRequestControlNumberTest extends TestCase
{
    public function test_control_number_is_unique_across_same_second_submissions_for_same_user(): void
    {
        $first = ServiceRequest::generateControlNumber(42);
        $second = ServiceRequest::generateControlNumber(42);

        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('/^SR-\d{14}-42-[A-Z0-9]{6}$/', $first);
        $this->assertMatchesRegularExpression('/^SR-\d{14}-42-[A-Z0-9]{6}$/', $second);
    }
}
