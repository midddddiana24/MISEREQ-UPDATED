@extends('admin.layout')

@section('title', 'Notifications')
@section('page-title', 'Notification Center')
@section('page-subtitle', 'Review administrative alerts and failed email deliveries.')

@section('content')
    <section class="panel">
        <div class="table-wrap">
            <table class="compact-table">
                <thead><tr><th>Date</th><th>Notification</th><th>Recipient</th><th>Delivery</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                        <td><div class="table-cell-title">{{ $notification->subject ?: 'System notification' }}</div><div class="table-cell-meta">{{ $notification->message }}</div></td>
                        <td><div class="table-cell-title">{{ $notification->user?->full_name ?: 'System recipient' }}</div><div class="table-cell-meta">{{ $notification->recipient_email ?: $notification->user?->email }}</div></td>
                        <td><span class="status-badge {{ $notification->status === 'failed' ? 'rejected' : 'completed' }}">{{ ucfirst($notification->status) }}</span>@if($notification->response)<div class="table-cell-meta">{{ $notification->response }}</div>@endif</td>
                        <td>@if($notification->status === 'failed')<form method="POST" action="{{ route('admin.notifications.retry', $notification) }}">@csrf<button class="btn-primary">Retry Email</button></form>@elseif($notification->action_url)<a class="btn-ghost" href="{{ $notification->action_url }}">Open</a>@else<span class="table-note">No action</span>@endif</td>
                    </tr>
                @empty<tr><td colspan="5" class="empty-state">No notifications found.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;">{{ $notifications->links() }}</div>
    </section>
@endsection
