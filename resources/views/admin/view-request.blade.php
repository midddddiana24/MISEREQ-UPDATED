@extends('admin.layout')

@section('title', 'Request Details')

@section('page-title', 'Request Details')

@section('page-subtitle', 'Compatibility view for legacy admin request detail routes.')

@section('content')
    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>{{ $request->control_number ?? 'Request' }}</h2>
                <p>{{ $request->requester_name ?? ($request->user->full_name ?? 'Unknown requester') }}</p>
            </div>
            <span class="status-badge {{ $request->status }}">{{ ucfirst($request->status) }}</span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">Office</span>
                <div class="value">{{ $request->office ?? '-' }}</div>
            </div>
            <div class="info-item">
                <span class="label">Category</span>
                <div class="value">{{ $request->category ?? '-' }}</div>
            </div>
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="label">Description</span>
                <div class="value">{{ $request->description ?? 'No description provided.' }}</div>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 22px;">
            <a href="{{ route('admin.requests.index') }}" class="btn-ghost">Back to Requests</a>
        </div>
    </section>
@endsection
