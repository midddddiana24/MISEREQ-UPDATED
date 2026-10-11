@php
    $logoBytes = file_get_contents(public_path('images/wvsu-logo.png'));
@endphp
<img src="data:image/png;base64,{{ base64_encode($logoBytes) }}" alt="WVSU Logo" width="60" height="60">
