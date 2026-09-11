<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionValidity;
use App\Http\Middleware\CheckTwoFactorAuth;
use App\Http\Middleware\TrackUserSession;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->withoutMiddleware([
            CheckTwoFactorAuth::class,
            TrackUserSession::class,
            CheckSessionValidity::class,
        ]);
    }

    public function test_executable_attachment_is_rejected(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->from(route('requests.create'))
            ->post(route('requests.store'), $this->validRequestData() + [
                'attachment' => UploadedFile::fake()->create(
                    'campus-update.exe',
                    20,
                    'application/octet-stream'
                ),
            ])
            ->assertRedirect(route('requests.create'))
            ->assertSessionHasErrors('attachment');

        $this->assertSame(0, ServiceRequest::where('user_id', $user->id)->count());
    }

    public function test_more_than_five_attachments_are_rejected(): void
    {
        $user = $this->createUser();
        $attachments = collect(range(1, 6))
            ->map(fn (int $number) => UploadedFile::fake()->image("evidence-{$number}.jpg"))
            ->all();

        $this->actingAs($user)
            ->from(route('requests.create'))
            ->post(route('requests.store'), $this->validRequestData() + [
                'attachments' => $attachments,
            ])
            ->assertRedirect(route('requests.create'))
            ->assertSessionHasErrors('attachments');
    }

    public function test_oversized_pdf_attachment_is_rejected(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->from(route('requests.create'))
            ->post(route('requests.store'), $this->validRequestData() + [
                'attachment' => UploadedFile::fake()->create(
                    'oversized-evidence.pdf',
                    11 * 1024,
                    'application/pdf'
                ),
            ])
            ->assertRedirect(route('requests.create'))
            ->assertSessionHasErrors('attachment');
    }

    private function validRequestData(): array
    {
        return [
            'services' => ['troubleshoot_pc'],
            'description' => 'Automated attachment security validation.',
            'date_request' => now()->toDateString(),
            'time_request' => now()->format('H:i'),
        ];
    }
}
