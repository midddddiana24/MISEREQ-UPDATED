@extends('admin.layout')

@section('title', 'Requests')
@section('page-title', 'Service Requests')
@section('page-subtitle', 'Find, review, and manage campus service requests.')

@section('header-actions')
    <a href="{{ route('admin.service-requests.create-on-behalf') }}" class="btn-primary">Add Walk-in Request</a>
@endsection

@section('head')
    <style>
        .request-page { display: grid; gap: 14px; }
        .request-queues { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .request-queue {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            min-height: 72px; padding: 13px 15px; border: 1px solid var(--line);
            border-radius: 10px; background: #fff; color: var(--ink-900);
        }
        .request-queue:hover, .request-queue:focus-visible { border-color: #aac7dc; background: #f5f9fc; }
        .request-queue.active { border-color: var(--brand-700); background: var(--brand-100); }
        .request-queue-copy { min-width: 0; }
        .request-queue-copy strong { display: block; font-size: 14px; }
        .request-queue-copy span { display: block; margin-top: 3px; color: var(--ink-500); font-size: 11px; line-height: 1.35; }
        .request-queue-count { flex: 0 0 auto; min-width: 34px; text-align: right; font-size: 22px; font-weight: 750; }
        .request-filter-panel { padding: 16px; }
        .request-filter-row { display: grid; grid-template-columns: minmax(240px, 1.6fr) minmax(170px, .7fr) minmax(170px, .7fr) auto; gap: 10px; align-items: end; }
        .request-filter-row .field { gap: 5px; }
        .request-filter-row .field input, .request-filter-row .field select { min-height: 42px; }
        .request-filter-actions { display: flex; gap: 7px; }
        .request-advanced { margin-top: 12px; padding: 0; overflow: hidden; }
        .request-advanced > summary { padding: 11px 13px; }
        .request-advanced[open] > summary { border-bottom: 1px solid var(--line); }
        .request-advanced-fields { padding: 13px; }
        .request-results .section-heading { margin-bottom: 12px; }
        .request-results .panel-title-meta { color: var(--ink-500); font-size: 13px; }
        .request-table th:last-child, .request-table td:last-child { width: 156px; padding-left: 12px; padding-right: 12px; text-align: center; }
        .request-table .btn-primary, .request-table .btn-ghost { min-height: 34px; padding: 6px 10px; font-size: 12px; }
        .request-row-actions { display: flex; align-items: center; justify-content: center; gap: 6px; }
        @media (max-width: 1050px) {
            .request-queues { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .request-filter-row { grid-template-columns: minmax(220px, 1fr) minmax(160px, .6fr) minmax(160px, .6fr); }
            .request-filter-actions { grid-column: 1 / -1; }
        }
        @media (max-width: 700px) {
            .request-queues { grid-template-columns: 1fr 1fr; gap: 8px; }
            .request-queue { min-height: 66px; padding: 11px; }
            .request-queue-copy span { display: none; }
            .request-filter-row { grid-template-columns: 1fr; }
            .request-filter-actions { grid-column: auto; }
            .request-filter-actions .btn-primary, .request-filter-actions .btn-ghost { flex: 1; }
            .request-table thead { display: none; }
            .request-table, .request-table tbody, .request-table tr, .request-table td { display: block; width: 100%; }
            .request-table tr { padding: 12px; border-bottom: 1px solid var(--line); }
            .request-table td { padding: 3px 0 !important; border: 0; }
            .request-table td[data-label]::before { content: attr(data-label) ': '; color: var(--ink-500); font-size: 11px; font-weight: 700; text-transform: uppercase; }
            .request-table td:last-child { margin-top: 8px; text-align: left; }
            .request-row-actions { display: grid; grid-template-columns: 1fr 1fr; }
            .request-table td:last-child .btn-primary, .request-table td:last-child .btn-ghost { width: 100%; }
        }
        @media (max-width: 390px) {
            .request-queues { grid-template-columns: 1fr; }
            .request-queue-copy span { display: block; }
        }
    </style>
@endsection

@section('content')
    @php
        $primaryQueues = [
            ['key' => 'attention', 'label' => 'Needs Attention', 'hint' => 'Returned requests requiring review'],
            ['key' => 'triage', 'label' => 'New Requests', 'hint' => 'Review and assign'],
            ['key' => 'unassigned', 'label' => 'Unassigned', 'hint' => 'No technician assigned'],
            ['key' => 'awaiting_confirmation', 'label' => 'Waiting for User', 'hint' => 'Needs user confirmation'],
        ];
        $queueOptions = [
            'attention' => 'Needs Attention',
            'triage' => 'New Requests',
            'unassigned' => 'Unassigned Requests',
            'awaiting_requester_info' => 'Waiting for User Reply',
            'awaiting_confirmation' => 'Waiting for User Confirmation',
            'reopened' => 'Returned Requests',
            'feedback' => 'Feedback to Review',
            'archived' => 'Archived Records',
        ];
        $simpleStatusLabels = [
            'submitted' => 'New', 'pending' => 'Waiting for Assignment', 'processing' => 'In Progress',
            'awaiting_requester_info' => 'Waiting for User Reply', 'awaiting_confirmation' => 'Waiting for User Confirmation',
            'completed' => 'Work Completed', 'closed' => 'Closed', 'reopened' => 'Returned',
            'escalated' => 'Needs Review', 'cancelled' => 'Cancelled', 'rejected' => 'Rejected',
        ];
        $advancedActive = collect($filters)->only(['search_technician','search_category','search_date_from','search_date_to'])->filter()->isNotEmpty();
    @endphp

    <div class="request-page">
        <nav class="request-queues" aria-label="Request groups">
            @foreach($primaryQueues as $card)
                <a href="{{ route('admin.requests.index', ['search_queue' => $card['key']]) }}" class="request-queue {{ ($filters['search_queue'] ?? '') === $card['key'] ? 'active' : '' }}">
                    <span class="request-queue-copy"><strong>{{ $card['label'] }}</strong><span>{{ $card['hint'] }}</span></span>
                    <span class="request-queue-count">{{ $stats[$card['key']] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        <section class="panel request-filter-panel" aria-labelledby="request-search-title">
            <form method="GET" action="{{ route('admin.requests.index') }}">
                <div class="request-filter-row">
                    <div class="field">
                        <label id="request-search-title" for="search">Search requests</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Control number, requester, or concern">
                    </div>
                    <div class="field">
                        <label for="search_queue">Group</label>
                        <select id="search_queue" name="search_queue"><option value="">All requests</option>@foreach($queueOptions as $value => $label)<option value="{{ $value }}" @selected(($filters['search_queue'] ?? '') === $value)>{{ $label }}</option>@endforeach</select>
                    </div>
                    <div class="field">
                        <label for="search_status">Status</label>
                        <select id="search_status" name="search_status"><option value="">All statuses</option>@foreach(\App\Models\ServiceRequest::STATUSES as $status)<option value="{{ $status }}" @selected(($filters['search_status'] ?? '') === $status)>{{ $simpleStatusLabels[$status] ?? str($status)->replace('_', ' ')->title() }}</option>@endforeach</select>
                    </div>
                    <div class="request-filter-actions"><button type="submit" class="btn-primary">Search</button><a href="{{ route('admin.requests.index') }}" class="btn-ghost">Reset</a></div>
                </div>

                <details class="admin-disclosure request-advanced" @if($advancedActive) open @endif>
                    <summary>More filters</summary>
                    <div class="form-grid request-advanced-fields">
                        <div class="field form-col-4"><label for="search_technician">Technician</label><select id="search_technician" name="search_technician"><option value="">All technicians</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) ($filters['search_technician'] ?? '') === (string) $technician->id)>{{ $technician->full_name }}</option>@endforeach</select></div>
                        <div class="field form-col-4"><label for="search_category">Category</label><input id="search_category" name="search_category" value="{{ $filters['search_category'] ?? '' }}" placeholder="e.g. Network"></div>
                        <div class="field form-col-4"><label for="search_date_from">Created from</label><input id="search_date_from" type="date" name="search_date_from" value="{{ $filters['search_date_from'] ?? '' }}"></div>
                        <div class="field form-col-4"><label for="search_date_to">Created to</label><input id="search_date_to" type="date" name="search_date_to" value="{{ $filters['search_date_to'] ?? '' }}"></div>
                    </div>
                </details>
            </form>
        </section>

        <section class="panel request-results">
            <div class="section-heading">
                <div><h2>{{ $queueOptions[$filters['search_queue'] ?? ''] ?? 'All Requests' }}</h2><p class="panel-title-meta">{{ $requests->total() }} {{ \Illuminate\Support\Str::plural('request', $requests->total()) }} found</p></div>
                @if(request()->query())<a href="{{ route('admin.requests.index') }}" class="btn-ghost">Clear filters</a>@endif
            </div>
            <div class="table-wrap">
                <table class="compact-table request-table">
                    <thead><tr><th>Request</th><th>Status</th><th>Technician</th><th>Submitted</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($requests as $item)
                            <tr>
                                <td><div class="table-cell-title">{{ $item->control_number }}</div><div class="table-cell-meta">{{ $item->requester_name ?? $item->user?->full_name ?? '-' }} &middot; {{ $item->category ?? 'Unclassified' }}@if($item->is_admin_created && !$item->user_id) &middot; Walk-in @endif</div></td>
                                <td data-label="Status"><span class="status-badge {{ $item->status }}">{{ $simpleStatusLabels[$item->status] ?? $item->status_label }}</span></td>
                                <td data-label="Technician">{{ $item->assignedTechnician?->full_name ?? 'Unassigned' }}</td>
                                <td data-label="Submitted">{{ $item->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($item->trashed())
                                        <form method="POST" action="{{ route('admin.requests.restore', $item) }}" onsubmit="return confirm('Restore this request?');">@csrf<button class="btn-primary">Restore</button></form>
                                    @else
                                        <div class="request-row-actions">
                                            <a href="{{ route('admin.service-requests.show', $item) }}" class="btn-primary">Review</a>
                                            <a href="{{ route('admin.service-requests.edit', $item) }}" class="btn-ghost">Edit</a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No requests match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:14px;">{{ $requests->links() }}</div>
        </section>
    </div>
@endsection
