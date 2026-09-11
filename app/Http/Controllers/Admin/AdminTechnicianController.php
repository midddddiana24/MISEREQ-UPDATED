<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\ServiceRequest;
use App\Models\LoginAttempt;
use App\Services\AdminActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminTechnicianController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}
    public function index()
    {
        // Fetch all technicians from database
        $technicians = Technician::withCount([
            'serviceRequests as assigned_requests',
            'serviceRequests as active_requests' => fn ($query) => $query->whereIn(
                'status',
                ['pending', 'processing', 'awaiting_requester_info', 'awaiting_confirmation', 'reopened', 'escalated']
            ),
        ])->get()->map(function ($technician) {
            $technician->current_workload = $technician->active_requests;
            return $technician;
        });
        return view('admin.technicians.index', ['technicians' => $technicians]);
    }

    public function create()
    {
        return view('admin.technicians.create');
    }

    public function store(Request $request)
    {
        // Validate technician data
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:technicians,email'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'job_assignment' => ['required', Rule::in(Technician::JOB_ASSIGNMENTS)],
            'status' => ['required', 'in:available,busy,on_leave'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        // Create technician without user_id (optional)
        try {
            $technician = Technician::create($validated + ['is_active' => true]);
            $this->activityLogger->logAdministrative('technician_created', "Technician {$technician->full_name} created.", [], ['technician_id' => $technician->id]);
            return redirect()->route('admin.technicians.index')->with('success', 'Technician created successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create technician: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $technician = Technician::findOrFail($id);
        return view('admin.technicians.show', ['technician' => $technician]);
    }

    public function edit($id)
    {
        $technician = Technician::findOrFail($id);
        return view('admin.technicians.edit', ['technician' => $technician]);
    }

    public function update(Request $request, $id)
    {
        $technician = Technician::findOrFail($id);

        // Validate technician data
        $validated = $request->validate([
            'specialization' => ['nullable', 'string', 'max:255'],
            'job_assignment' => ['required', Rule::in(Technician::JOB_ASSIGNMENTS)],
            'status' => ['required', 'in:available,busy,on_leave'],
            'phone' => ['nullable', 'string', 'max:20'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $validated['skills'] = filled($validated['skills'] ?? null)
                ? array_values(array_filter(array_map('trim', explode(',', $validated['skills']))))
                : null;
            $technician->update($validated);
            $this->activityLogger->logAdministrative('technician_updated', "Technician {$technician->full_name} updated.", [], ['technician_id' => $technician->id, 'status' => $technician->status]);
            return back()->with('success', 'Technician updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update technician: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $technician = Technician::findOrFail($id);

            // Check if technician has active assignments
            $activeRequests = ServiceRequest::where('assigned_technician_id', $technician->id)
                ->whereIn('status', ['pending', 'processing', 'awaiting_requester_info', 'awaiting_confirmation', 'reopened', 'escalated'])
                ->count();
            if ($activeRequests > 0) {
                return back()->with('error', 'Cannot deactivate a technician with active assignments. Please reassign or close those requests first.');
            }

            $technician->update(['status' => 'on_leave', 'is_active' => false, 'deactivated_at' => now(), 'deactivated_by' => Auth::id()]);
            $this->activityLogger->logAdministrative('technician_deactivated', "Technician {$technician->full_name} deactivated.", [], ['technician_id' => $technician->id]);
            return back()->with('success', 'Technician deactivated. Historical request assignments were preserved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete technician: ' . $e->getMessage());
        }
    }

    public function activate(Technician $technician)
    {
        $technician->update(['is_active' => true, 'status' => 'available', 'deactivated_at' => null, 'deactivated_by' => null]);
        $this->activityLogger->logAdministrative('technician_activated', "Technician {$technician->full_name} reactivated.", [], ['technician_id' => $technician->id]);
        return back()->with('success', 'Technician reactivated successfully.');
    }

    /**
     * Get technician details with logs for modal view
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails($id)
    {
        try {
            $technician = Technician::findOrFail($id);

            // Get login attempts for this technician (via email)
            $logs = LoginAttempt::where('username', $technician->email)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'ip_address' => $log->ip_address,
                        'device_info' => $log->device_info ?? 'Unknown',
                        'user_agent' => $log->user_agent ?? 'Unknown',
                        'success' => $log->success,
                        'failure_reason' => $log->failure_reason,
                        'created_at' => $log->created_at ? $log->created_at->format('M d, Y H:i A') : 'Unknown',
                    ];
                });

            return response()->json([
                'success' => true,
                'technician' => [
                    'id' => $technician->id,
                    'full_name' => $technician->first_name . ' ' . $technician->last_name,
                    'email' => $technician->email,
                    'specialization' => $technician->specialization ?? 'N/A',
                    'job_assignment' => $technician->job_assignment ?? 'N/A',
                    'status' => ucfirst($technician->status),
                    'phone' => $technician->phone ?? 'N/A',
                    'current_workload' => $technician->current_workload ?? 0,
                    'notes' => $technician->notes ?? 'N/A',
                    'created_at' => $technician->created_at ? $technician->created_at->format('M d, Y') : 'Unknown',
                ],
                'logs' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching technician details: ' . $e->getMessage(),
            ], 500);
        }
    }
}
