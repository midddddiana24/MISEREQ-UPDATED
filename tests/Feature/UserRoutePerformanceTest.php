<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UserRoutePerformanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_dashboard_and_request_list_query_counts_do_not_grow_with_records(): void
    {
        $user = $this->createUser();
        $this->makeRequests($user->id, 1);

        $dashboardOne = $this->profileGet($user, route('dashboard'));
        $requestsOne = $this->profileGet($user, route('requests.index'));

        $this->makeRequests($user->id, 20, 1);

        $dashboardMany = $this->profileGet($user, route('dashboard'));
        $requestsMany = $this->profileGet($user, route('requests.index'));

        fwrite(STDOUT, PHP_EOL.'PERF_QUERY_COUNTS '.json_encode([
            'dashboard_1_record' => $dashboardOne,
            'dashboard_21_records' => $dashboardMany,
            'requests_1_record' => $requestsOne,
            'requests_21_records' => $requestsMany,
        ]).PHP_EOL);

        $this->assertLessThanOrEqual(3, $dashboardMany['count'] - $dashboardOne['count']);
        $this->assertLessThanOrEqual(3, $requestsMany['count'] - $requestsOne['count']);
        $this->assertLessThanOrEqual(35, $dashboardMany['count']);
        $this->assertLessThanOrEqual(25, $requestsMany['count']);
    }

    private function profileGet($user, string $url): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->get($url)->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return [
            'count' => count($queries),
            'time_ms' => round(array_sum(array_column($queries, 'time')), 2),
        ];
    }

    private function makeRequests(int $userId, int $count, int $offset = 0): void
    {
        for ($index = 0; $index < $count; $index++) {
            ServiceRequest::withoutEvents(fn () => ServiceRequest::create([
                'control_number' => sprintf('PERF-%d-%03d', $userId, $index + $offset),
                'user_id' => $userId,
                'requester_name' => 'Performance Requester',
                'requester_office' => 'MIS Test Office',
                'office' => 'MIS Test Office',
                'services' => ['troubleshoot_pc'],
                'description' => 'Query growth fixture.',
                'status' => 'pending',
                'date_of_request' => now()->toDateString(),
                'time_of_request' => now()->format('H:i:s'),
            ]));
        }
    }
}
