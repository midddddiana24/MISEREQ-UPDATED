# Device Acceptance Checklist

Run this checklist before the campus production launch using actual devices and
the final HTTPS campus URL. Record the device, browser, tester, date, and result.

## Required viewports

- 320 x 568: small Android/mobile browser
- 375 x 667: standard mobile
- 390 x 844: modern mobile
- 768 x 1024: portrait tablet
- 820 x 1180: modern portrait tablet
- 1024 x 768: landscape tablet
- 1366 x 768: common campus laptop

## Public and account pages

- Welcome heading, campus logos, and all three service summaries fit without horizontal scrolling.
- Login fields, Remember me, Forgot password, and Sign in remain visible.
- Account creation fields stack in a logical order and validation messages remain beside their fields.
- Forgot-password and password-reset actions fit at 320 px.
- Keyboard focus indicators are clearly visible.

## Client workflow

- Dashboard cards are equal in size and do not overflow at 320 px.
- Submit Request can be completed with the mobile keyboard open.
- Request history filters, actions, and pagination remain usable.
- Request details and status history do not require horizontal scrolling.
- Incident and preventive-maintenance pages keep their primary action visible.
- Messenger opens as a contained mobile panel, retains drafts, and shows Retry after a simulated connection failure.
- Settings, sessions, 2FA, and notifications stack without clipped actions.
- PDF buttons show Preparing PDF before opening the document.

## Administrator workflow

- Header title and logout remain on one row at tablet widths.
- Sidebar collapses and opens using keyboard and touch.
- Dashboard cards and recent activity do not create page-wide horizontal scrolling.
- Service Requests, incidents, maintenance, Messenger, technicians, users, and Settings remain reachable.
- Record review controls stack above PDF previews on tablets and phones.
- System Operations health cards, secure action forms, and backup table fit at 320 px.
- Audit filters wrap into a readable single-column form on mobile.

## Accessibility and completion

- Test at 200 percent browser zoom.
- Complete all forms using only a keyboard.
- Confirm labels are announced with a screen reader.
- Confirm status is not communicated by color alone.
- Confirm touch targets are comfortably selectable.
- Capture screenshots for every failed item and retest after correction.
