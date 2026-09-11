@extends('admin.layout')

@section('title', 'Create Incident Report')
@section('page-title', 'Create Incident Report')
@section('page-subtitle', 'Add a new ICT incident report directly from the admin panel or encode a client-submitted case.')

@section('content')
    <section class="panel incident-editor-panel">
        <form method="POST" action="{{ route('admin.incident-reports.store') }}" class="form-grid">
            @csrf
            @include('admin.incident-reports._form')

            <div class="form-col-12 form-actions incident-form-actions">
                <button type="submit" class="btn-primary">Create Report</button>
                <a href="{{ route('admin.incident-reports.index') }}" class="btn-ghost">Back to List</a>
            </div>
        </form>
    </section>
@endsection
