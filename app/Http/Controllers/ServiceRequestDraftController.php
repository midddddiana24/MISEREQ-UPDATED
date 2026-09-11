<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestDraftController extends Controller
{
    public function show()
    {
        $draft = ServiceRequestDraft::where('user_id', Auth::id())
            ->where('form_type', 'service_request')
            ->first();

        return response()->json([
            'draft' => $draft ? [
                'payload' => $draft->payload,
                'updated_at' => $draft->updated_at->toIso8601String(),
                'updated_human' => $draft->updated_at->diffForHumans(),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payload' => 'required|array',
        ]);

        $draft = ServiceRequestDraft::updateOrCreate(
            ['user_id' => Auth::id(), 'form_type' => 'service_request'],
            ['payload' => $validated['payload']]
        );

        return response()->json([
            'success' => true,
            'updated_at' => $draft->updated_at->toIso8601String(),
        ]);
    }

    public function destroy()
    {
        ServiceRequestDraft::where('user_id', Auth::id())
            ->where('form_type', 'service_request')
            ->delete();

        return response()->json(['success' => true]);
    }
}
