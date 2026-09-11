# MIS Security Update and Testing Report

**Test date:** July 30, 2026  
**Environment:** Local XAMPP development installation  
**Testing database:** `mis_service_request_testing`  
**Final result:** **50 tests passed, 240 assertions passed**

## Purpose

This folder documents the recent administrator and client account-security update. Automated tests use an isolated database and cannot use the live `mis_service_request` database.

The testing database retains the application schema and migration history but contains no copied campus users, requests, reports, messages, or other production records. Every automated test creates temporary synthetic records and rolls them back afterward.

## New and Updated Functions

### Administrator account security

- The first verified administrator device becomes trusted.
- A later sign-in from another device is paused before dashboard access.
- The new device can be approved from an existing trusted administrator device.
- A six-digit code sent to the administrator email is available as a fallback.
- The administrator can approve or deny the sign-in request.
- A denied, expired, or unapproved device cannot access the administrator dashboard.
- Verification codes are stored as hashes; the plain code is not stored in the notification database.

### Admin-to-client account management

| Administrator action | Client-side result |
|---|---|
| Unlock account | Failed-attempt counters and temporary lock are cleared; the client is notified. |
| Send reset link | The client receives a one-time password-reset link and chooses their own password. |
| Require password change | After verified login, the client must change their password before using the system. |
| Sign out all devices | All client sessions are revoked and the client must sign in again. |

These actions use the same `users`, `user_sessions`, `notifications`, password-reset, and audit-log records shared by the administrator and client applications.

### Login and session recovery

- The application session cookie was rotated to `mis_service_request_session_v2`.
- Expired login forms now refresh the login session instead of showing a raw 419 page.
- Both `localhost:8000` and `127.0.0.1:8000` issue valid session and CSRF cookies.
- “Keep me signed in” remains covered by automated testing.

### Service-request completion workflow

- An administrator cannot mark work complete until a technician is assigned.
- An assigned request must be in **In Progress** before work can be marked complete.
- Reopened and escalated requests must use **Resume Work** before completion.
- Marking work complete moves the request to requester confirmation; it does not skip directly to the final closed state.
- Public, client, and administrator forms show shared processing feedback and disable the submitted button to prevent accidental duplicate submissions.

### Request conversation discovery

- Every account-linked service request appears immediately in the client and administrator Messages pages, even before the first message is sent.
- A new conversation clearly displays as ready instead of disappearing from the list.
- Sent and received messages update the visible conversation preview immediately.
- Client and administrator message indicators refresh while the page remains open.
- Sent messages remain visible after refreshing the Messages page.

### Service-request PDF delivery

- Client PDF view and download use the file cache even when the application's default cache is MySQL.
- Generated PDF binary data is no longer inserted into the database cache or copied into the database-backed session.
- PDF errors shown to users remain short and safe; large internal SQL bindings are retained neither in the session nor in the visible error.
- Ownership authorization remains enforced for client PDF access.

### Independent preventive-maintenance workflow

- ICT incident submission no longer creates a preventive-maintenance checklist automatically.
- Updating an incident no longer overwrites or synchronizes maintenance findings and recommendations.
- Preventive-maintenance records remain optionally linked to a service request, but not to an incident report.
- Existing maintenance records and their entered details were preserved while legacy incident associations were cleared.
- Administrators can independently enter and update inspection checks, findings, recommendations, sign-off details, and status.

### Test database protection

- `phpunit.xml` points automated tests to `mis_service_request_testing`.
- `tests/TestCase.php` stops execution if tests are configured to use the live database.
- The live campus database is therefore protected from test migrations, truncation, and synthetic test data.

## Automated Test Results

Command used:

```powershell
php artisan test
```

Final output:

```text
Tests:    50 passed (240 assertions)
Duration: 55.95s
```

| Test area | Tests | Result |
|---|---:|---|
| Service-request authorization policy | 3 | Passed |
| Client/admin workflow transitions | 4 | Passed |
| Administrator trusted-device verification | 4 | Passed |
| Administrator operations security | 3 | Passed |
| User recovery and session administration | 7 | Passed |
| Authentication, 2FA, rate limiting, and 419 recovery | 8 | Passed |
| Backup archive validation and rejection | 2 | Passed |
| Attachment type, count, and size validation | 3 | Passed |
| Independent incident and maintenance workflows, PDFs, and ownership | 4 | Passed |
| Technician assignment and service-request completion | 4 | Passed |
| Client service-request, messaging, and PDF flow | 4 | Passed |
| New request conversation discovery and refresh persistence | 3 | Passed |
| Shared form-submission feedback | 1 | Passed |
| **Total** | **50** | **Passed** |

## Important Behaviors Verified

- A client can sign in with the “remember me” option.
- An administrator with 2FA continues to administrator device verification.
- An authenticated administrator cannot be redirected to the client dashboard.
- The first administrator device can be trusted safely.
- A second device requires approval or the emailed verification code.
- An unapproved device cannot enter the administrator dashboard.
- A trusted administrator session can approve another device.
- A client cannot unlock another account or view another user’s request.
- An administrator can unlock an account and clear its login throttle.
- An administrator can send a secure password-reset invitation.
- An administrator can require a password change.
- A required password change blocks normal access until completed.
- An administrator can revoke all sessions and trusted devices.
- An expired login form recovers instead of displaying a 419 page.
- A client can submit and view a service request.
- Messaging rejects access from a different client.
- The official service-request PDF can be generated.
- A request without an assigned technician cannot be marked complete.
- A reopened request cannot be completed until it is resumed as In Progress.
- An assigned In Progress request can move to requester confirmation.
- The generic status action cannot skip directly to Completed.
- Login, client dashboard, and administrator dashboard pages load the shared form-submission feedback.
- A newly submitted request appears immediately as a conversation for its client.
- The same account-linked request appears in the administrator Messenger before any chat message exists.
- A sent message remains visible in both the conversation list and message API after a refresh.
- Client inline PDF viewing succeeds while the default application cache is configured as database.
- Client PDF downloading succeeds and returns the expected official filename under the same production-like cache setting.
- Executable attachments are rejected.
- Requests with more than five attachments are rejected.
- Oversized PDF attachments are rejected.
- Submitting an incident does not create or modify a preventive-maintenance checklist.
- The administrator receives an action notification for a submitted incident.
- An administrator can create and edit independent maintenance findings, recommendations, checks, and sign-off details.
- Incident and preventive-maintenance PDFs are available to the owning client.
- Another client cannot view or download another user’s incident or maintenance records.
- Valid backup archives pass a non-destructive verification.
- Incomplete backup archives are rejected before any restore is attempted.

## Manual Acceptance Checklist

### Test administrator device confirmation

1. Sign in as an administrator in the primary browser.
2. Complete 2FA, if enabled, and trust the first device.
3. Open an incognito window or another browser.
4. Sign in using the same administrator account.
5. Confirm that the new browser stops at **Confirm this administrator device**.
6. In the trusted browser, open the security notification and approve the request.
7. Return to the new browser and select **Check Approval**.
8. Confirm that it enters the administrator dashboard.
9. Repeat and select **Deny Sign-In** to confirm the pending device is rejected.

### Test admin-to-client recovery

1. Open **Admin → Users → User Profile**.
2. Use **Send Reset Link** and confirm the client receives a one-time reset email.
3. Use **Require Password Change**, then sign in as that client.
4. Confirm the client cannot open the dashboard until the password is changed.
5. Use **Sign Out All Devices** and confirm the client is returned to login.
6. Trigger five failed client sign-ins, then use **Unlock Account** and confirm login works again.

### Test expired login recovery

1. Open the login page.
2. Leave the page open while restarting the application or clearing the server session.
3. Submit the old form.
4. Confirm the system returns to a refreshed login page with an explanatory message instead of a 419 error.
5. Enter the credentials again and confirm login succeeds.

## Main Files Added or Updated

- `app/Services/AdminDeviceTrustService.php`
- `app/Services/SecurityNotificationService.php`
- `app/Http/Controllers/Auth/AdminDeviceVerificationController.php`
- `app/Http/Controllers/Auth/RequiredPasswordController.php`
- `app/Http/Middleware/EnsureAdminDeviceVerified.php`
- `app/Http/Middleware/EnsurePasswordChangeRequired.php`
- `app/Http/Controllers/Admin/AdminUserController.php`
- `app/Exceptions/Handler.php`
- `resources/views/auth/admin-device-verify.blade.php`
- `resources/views/auth/admin-device-review.blade.php`
- `resources/views/auth/required-password.blade.php`
- `resources/views/admin/users/show.blade.php`
- `tests/Feature/AdminDeviceVerificationTest.php`
- `tests/Feature/AdminUserSecurityTest.php`
- `tests/Feature/AuthenticationAndSecurityTest.php`
- `tests/Feature/BackupArchiveValidationTest.php`
- `tests/Feature/FileUploadSecurityTest.php`
- `tests/Feature/IncidentMaintenanceFlowTest.php`
- `tests/Feature/ServiceRequestCompletionWorkflowTest.php`
- `tests/Feature/MessageConversationDiscoveryTest.php`
- `tests/Feature/SubmissionFeedbackTest.php`
- `resources/views/partials/submission-feedback.blade.php`
- `phpunit.xml`
- `tests/TestCase.php`

## Pending Browser and Device Acceptance

Responsive layout screenshots, keyboard-only navigation, screen-reader behavior, and automated accessibility scanning were not executed because the connected browser was unavailable during this test run.

These checks remain required on:

- 320px and 375px mobile screens
- 768px and 1024px tablet screens
- 1366px and 1920px desktop screens
- Chrome, Edge, and a mobile browser

Confirm that there is no horizontal page scrolling, focus remains visible, form errors are announced clearly, touch controls remain usable, and administrator device approval works between two real browsers.

## Current Status

The administrator and client workflows pass the complete server-side automated suite in the isolated, privacy-sanitized testing database. Browser-based responsive and accessibility acceptance remains pending and is not represented as completed.
