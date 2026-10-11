<?php

namespace Tests\Feature;

use App\Models\IctIncidentReport;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PdfFormLogoTest extends TestCase
{
    public static function forms(): array
    {
        return [
            'service request' => ['service-request', 'request', ServiceRequest::class],
            'incident report' => ['incident-report', 'report', IctIncidentReport::class],
            'preventive maintenance' => ['preventive-maintenance', 'checklist', PreventiveMaintenanceChecklist::class],
        ];
    }

    #[DataProvider('forms')]
    public function test_form_embeds_the_requested_logo_with_remote_images_disabled(string $view, string $variable, string $model): void
    {
        // Unsaved fixtures keep this rendering regression independent of the database.
        $record = new $model;
        $record->forceFill([
            'control_number' => 'PDF-LOGO-CHECK',
            'document_number' => 'PDF-LOGO-CHECK',
            'requester_name' => 'PDF Preview',
            'system_owner' => 'PDF Preview',
            'office_department' => 'MIS Office',
            'description' => 'Verify the university logo in the generated form.',
            'date_of_request' => now()->toDateString(),
            'status' => 'submitted',
            'services' => [],
        ]);
        $record->setRelation('user', null);
        $record->setRelation('assignedTechnician', null);

        $html = view('pdf.'.$view, [$variable => $record])->render();
        $logo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/wvsu-logo.png')));
        $this->assertStringContainsString('src="'.$logo.'"', $html);
        $this->assertStringNotContainsString('wvsu-janiuay-logo', $html);

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->getOptions()->setIsRemoteEnabled(false);
        $content = $pdf->output();

        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Subtype /Image', $content);

        // Optional visual evidence for local QA; never writes a real request or account.
        if ($directory = getenv('PDF_LOGO_QA_OUTPUT')) {
            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            file_put_contents($directory.'/'.$view.'.pdf', $content);
        }
    }
}
