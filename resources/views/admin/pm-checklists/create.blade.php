@extends('admin.layout')

@section('title', 'Create PM Checklist')
@section('page-title', 'Create PM Checklist')
@section('page-subtitle', 'Create a preventive maintenance checklist from the admin workspace for direct management or back-office encoding.')

@section('content')
    <section class="panel">
        <form method="POST" action="{{ route('admin.pm-checklists.store') }}" class="form-grid">
            @csrf
            @include('admin.pm-checklists._form')

            <div class="form-col-12 form-actions">
                <button type="submit" class="btn-primary">Create Checklist</button>
                <a href="{{ route('admin.pm-checklists.index') }}" class="btn-ghost">Back to List</a>
            </div>
        </form>
    </section>
@endsection
