<?php

namespace Tests\Feature;

use App\Jobs\GeneratePdfJob;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GeneratePdfJobCacheTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pdf_job_uses_file_store_when_default_cache_is_database(): void
    {
        config(['cache.default' => 'database']);
        Queue::fake();

        $user = $this->createUser();
        $request = ServiceRequest::create([
            'user_id' => $user->id,
            'requester_name' => $user->full_name,
            'requester_office' => $user->office,
            'office' => $user->office,
            'services' => ['troubleshoot_pc'],
            'description' => 'Verify binary PDF output uses the file cache.',
            'date_of_request' => now()->toDateString(),
            'time_of_request' => now()->format('H:i:s'),
            'status' => 'pending',
        ]);

        $cacheKey = 'pdf_v13_'.$request->id.'_'.$request->updated_at->timestamp;
        Cache::store('file')->forget($cacheKey);

        try {
            (new GeneratePdfJob($request->id))->handle();

            $this->assertTrue(Cache::store('file')->has($cacheKey));
            $this->assertIsString(Cache::store('file')->get($cacheKey));
            $this->assertStringStartsWith('%PDF', Cache::store('file')->get($cacheKey));
        } finally {
            Cache::store('file')->forget($cacheKey);
        }
    }
}
