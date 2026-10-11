<?php

namespace App\Jobs;

use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $serviceRequestId;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct($serviceRequestId)
    {
        $this->serviceRequestId = $serviceRequestId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fetch the service request with eager loaded relationships
            $serviceRequest = ServiceRequest::with(['assignedTechnician', 'user'])->findOrFail($this->serviceRequestId);

            // Create cache key
            $cacheKey = 'pdf_v13_'.$serviceRequest->id.'_'.$serviceRequest->updated_at->timestamp;

            // PDF output is binary and cannot be stored safely in the database
            // cache's text column. Keep it on the file cache, matching the
            // user and admin PDF controllers.
            $pdfCache = Cache::store('file');

            // Only generate if not already cached
            if (! $pdfCache->has($cacheKey)) {
                $html = \view('pdf.service-request', ['request' => $serviceRequest])->render();

                // Generate PDF with optimizations
                $pdf = Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');

                // Apply DomPDF optimizations
                $dompdf = $pdf->getDomPDF();
                $options = $dompdf->getOptions();
                $options->set('isPhpEnabled', false);
                $options->set('isRemoteEnabled', false);
                $options->set('logOutputFile', null);
                $options->set('enable_font_subsetting', false);

                // Cache for 30 days
                $pdfCache->put($cacheKey, $pdf->output(), now()->addDays(30));

                Log::info('PDF generated and cached for request: '.$serviceRequest->id);
            }
        } catch (\Exception $e) {
            Log::error('PDF Generation Job Error: '.$e->getMessage());
            throw $e;
        }
    }
}
