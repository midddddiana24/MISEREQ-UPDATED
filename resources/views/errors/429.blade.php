@extends('layouts.auth')

@section('title', 'Please Wait')
@section('content-class', 'compact')

@section('content')
    <div class="auth-heading">
        <h2>Please wait a moment</h2>
        <p>The system received several requests in a short time. This temporary pause protects university accounts and records.</p>
    </div>

    <div class="alert" role="status">
        Your information has not been intentionally cleared. Wait about a minute, then try the action again.
    </div>

    <button type="button" class="primary-button" style="width:100%; margin-top:18px;" onclick="history.length > 1 ? history.back() : window.location.assign('{{ url('/') }}')">
        Return and try again
    </button>

    <p class="auth-footer">If this continues after waiting, contact the MIS Office for assistance.</p>
@endsection
