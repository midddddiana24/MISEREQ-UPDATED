<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile - MIS Service Request System</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; overflow-x: hidden; }
        .profile-page { width: min(100%, 1180px); margin: 0 auto; padding: 20px 20px 46px; }
        .profile-layout { display: grid; grid-template-columns: minmax(260px, .7fr) minmax(0, 1.3fr); align-items: start; gap: 12px; }
        .profile-card { min-width: 0; padding: 16px; border: 1px solid #d9e2e8; border-radius: 9px; background: #fff; box-shadow: 0 2px 8px rgba(25,55,74,.04); }
        .profile-identity { text-align: center; }
        .profile-avatar { width: 72px; height: 72px; margin: 0 auto 10px; display: grid; place-items: center; border-radius: 50%; color: #fff; background: #236d9e; font-size: 24px; font-weight: 800; }
        .profile-identity h2 { margin: 0; color: #294152; font-size: 17px; }
        .profile-identity p { margin: 4px 0 0; color: #71808b; font-size: 11px; }
        .profile-status { margin-top: 10px; display: inline-flex; padding: 4px 8px; border-radius: 999px; color: #17613f; background: #e9f7ef; font-size: 9px; font-weight: 800; }
        .profile-facts { display: grid; gap: 7px; margin-top: 14px; text-align: left; }
        .profile-fact { padding: 8px 9px; border-radius: 7px; background: #f6f8fa; }
        .profile-fact span { display: block; color: #71808b; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .profile-fact strong { display: block; margin-top: 3px; color: #344754; font-size: 11px; overflow-wrap: anywhere; }
        .profile-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 13px; }
        .profile-card-head h2 { margin: 0; color: #294152; font-size: 14px; }
        .profile-card-head p { margin: 3px 0 0; color: #71808b; font-size: 10px; line-height: 1.45; }
        .profile-form { display: grid; gap: 10px; }
        .profile-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
        .profile-field { min-width: 0; }
        .profile-field.full { grid-column: 1 / -1; }
        .profile-field label { display: block; margin-bottom: 4px; color: #455b69; font-size: 10px; font-weight: 750; }
        .profile-field input { width: 100%; min-height: 39px; padding: 8px 9px; border: 1px solid #ced9e0; border-radius: 6px; font: inherit; font-size: 12px; }
        .profile-field input:focus { border-color: #5791b8; outline: 3px solid rgba(31,111,168,.14); }
        .profile-help { display: block; margin-top: 4px; color: #71808b; font-size: 9px; line-height: 1.4; }
        .profile-error { display: block; margin-top: 4px; color: #a33131; font-size: 9px; }
        .profile-actions { display: flex; flex-wrap: wrap; gap: 7px; }
        .profile-alert { margin-bottom: 10px; padding: 10px 12px; border: 1px solid; border-radius: 7px; font-size: 12px; }
        .profile-alert.success { color: #17613f; border-color: #b9ddca; background: #edf9f2; }
        .profile-alert.error { color: #982f2f; border-color: #e5c0c0; background: #fff2f2; }
        @media (max-width: 760px) {
            .profile-layout { grid-template-columns: 1fr; }
            .profile-form-grid { grid-template-columns: 1fr; }
            .profile-field.full { grid-column: auto; }
        }
        @media (max-width: 390px) {
            .profile-page { padding: 10px 8px 30px; }
            .profile-card { padding: 12px; }
        }
    </style>
    @include('user.partials.theme')
</head>
<body>
    @include('partials.header')

    <main class="profile-page">
        @include('user.partials.navigation')

        <section class="client-page-heading" aria-labelledby="profileTitle">
            <div>
                <span class="eyebrow">University Account</span>
                <h2 id="profileTitle">My Profile</h2>
                <p>Review and maintain the information MIS uses for your service records.</p>
            </div>
            <a href="{{ route('user.settings') }}" class="button secondary">Security Settings</a>
        </section>

        @if(session('success'))<div class="profile-alert success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="profile-alert error" role="alert">Please review the highlighted profile fields.</div>@endif

        <div class="profile-layout">
            <aside class="profile-card profile-identity">
                <div class="profile-avatar" aria-hidden="true">{{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}</div>
                <h2>{{ $user->full_name }}</h2>
                <p>{{ $user->designation ?: 'University client' }}</p>
                <span class="profile-status">Active account</span>

                <div class="profile-facts">
                    <div class="profile-fact"><span>Email</span><strong>{{ $user->email }}</strong></div>
                    <div class="profile-fact"><span>Username</span><strong>{{ $user->username }}</strong></div>
                    <div class="profile-fact"><span>Office / Department</span><strong>{{ $user->office }}</strong></div>
                    <div class="profile-fact"><span>Member since</span><strong>{{ $user->created_at->format('M d, Y') }}</strong></div>
                </div>
            </aside>

            <section class="profile-card" aria-labelledby="profileInformationTitle">
                <div class="profile-card-head">
                    <div><h2 id="profileInformationTitle">Profile information</h2><p>Your password is required before identity information can be changed.</p></div>
                </div>

                <form method="POST" action="{{ route('settings.updateProfile') }}" class="profile-form">
                    @csrf
                    <div class="profile-form-grid">
                        <div class="profile-field">
                            <label for="first_name">First name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" maxlength="255" required>
                            @error('first_name')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field">
                            <label for="last_name">Last name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" maxlength="255" required>
                            @error('last_name')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field">
                            <label for="middle_initial">Middle initial</label>
                            <input id="middle_initial" name="middle_initial" value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="2">
                            @error('middle_initial')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field">
                            <label for="office">Office / Department</label>
                            <input id="office" name="office" value="{{ old('office', $user->office) }}" maxlength="255" required>
                            @error('office')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field">
                            <label for="designation">Designation</label>
                            <input id="designation" name="designation" value="{{ old('designation', $user->designation) }}" maxlength="255">
                            @error('designation')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field">
                            <label for="phone_local">Phone / Local number</label>
                            <input id="phone_local" name="phone_local" value="{{ old('phone_local', $user->phone_local) }}" maxlength="20">
                            @error('phone_local')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="profile-field full">
                            <label for="profile_current_password">Current password</label>
                            <input id="profile_current_password" type="password" name="current_password" autocomplete="current-password" required>
                            <span class="profile-help">Required to prevent unauthorized changes from an unattended session.</span>
                            @error('current_password')<span class="profile-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="profile-actions"><button type="submit" class="button">Save Profile</button></div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
