<?php

namespace App\Http\Controllers;

use App\Models\PreventiveMaintenanceChecklist;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PreventiveMaintenanceChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = PreventiveMaintenanceChecklist::where('user_id', Auth::id());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('control_number', 'like', '%' . $search . '%')
                    ->orWhere('system_owner', 'like', '%' . $search . '%')
                    ->orWhere('office_department', 'like', '%' . $search . '%')
                    ->orWhere('property_number', 'like', '%' . $search . '%');
            });
        }

        $checklists = $query->with('serviceRequest:id,control_number,status')
            ->latest()
            ->paginate(15);
        $checklists->appends($request->query());
        return view('mis.pm-checklists.index', compact('checklists'));
    }

    public function create(Request $request)
    {
        return redirect()
            ->route('requests.create', ['service' => 'preventive_maintenance'])
            ->with('success', 'Submit a maintenance request first. MIS will prepare the official checklist.');
    }

    public function store(Request $request)
    {
        abort(403, 'Preventive maintenance checklists are prepared by MIS.');
    }

    public function show(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);
        $pmChecklist->load('serviceRequest');

        return view('mis.pm-checklists.show', ['checklist' => $pmChecklist]);
    }

    public function edit(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);

        return redirect()->route('pm-checklists.show', $pmChecklist);
    }

    public function update(Request $request, PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);
        abort(403, 'Only MIS staff may edit the technical maintenance checklist.');
    }

    /** Generate PDF for printing */
    public function generatePdf(PreventiveMaintenanceChecklist $pmChecklist)
    {
        $this->ensureOwner($pmChecklist);

        $pdf = Pdf::loadView('pdf.preventive-maintenance', ['checklist' => $pmChecklist])
            ->setPaper('A4', 'portrait');

        $response = $pdf->stream('PMC_' . $pmChecklist->control_number . '.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function ensureOwner(PreventiveMaintenanceChecklist $pmChecklist): void
    {
        $user = Auth::user();
        $isAdmin = $user instanceof \App\Models\User ? $user->isAdmin() : false;

        abort_unless($pmChecklist->user_id === Auth::id() || $isAdmin, 403);
    }

}
