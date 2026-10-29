@props(['periode'])

@php
    $logoPath = public_path('images/logo_horizontal.png');
    $logo = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $gradientId = 'slip-header-' . \Illuminate\Support\Str::uuid();
@endphp

<div class="slip-gaji-header" style="position:relative; width:100%; height:29mm; overflow:hidden; margin-bottom:6px; border-bottom:1px solid #E2E8F0; background:#fff;">
    <svg aria-hidden="true" viewBox="0 0 1000 180" preserveAspectRatio="none" style="position:absolute; inset:0; display:block; width:100%; height:100%;">
        <defs>
            <linearGradient id="{{ $gradientId }}" x1="390" y1="0" x2="1000" y2="180" gradientUnits="userSpaceOnUse">
                <stop stop-color="#4F16C8"/><stop offset=".58" stop-color="#8124E8"/><stop offset="1" stop-color="#E6009D"/>
            </linearGradient>
        </defs>
        <path fill="url(#{{ $gradientId }})" d="M390 0H1000V180H555Z"/>
        <path fill="#DCCBFF" d="M348 0h26l177 180h-27z"/>
        <path fill="#A855F7" fill-opacity=".75" d="M377 0h22l177 180h-22z"/>
        <path fill="#5E17EB" fill-opacity=".85" d="M401 0h31l176 180h-31z"/>
        <path fill="#FFFFFF" fill-opacity=".08" d="M760 0h180l-90 180h-180z"/>
    </svg>

    @if($logo)
        <img src="{{ $logo }}" alt="Soulmate Komputer" style="position:absolute; left:2.5%; top:0; display:block; width:47%; height:27mm; object-fit:contain; object-position:left center;">
    @else
        <strong style="position:absolute; left:3%; top:5mm; color:#5E17EB; font:900 17px Arial,sans-serif;">SOULMATE KOMPUTER</strong>
    @endif
    <span style="position:absolute; left:16.5%; bottom:1mm; color:#4C3680; font:600 7px/1 Arial,sans-serif; letter-spacing:1.1px; white-space:nowrap; text-transform:uppercase;">Laptop &middot; Komputer &middot; Printer &middot; CCTV &middot; Service</span>

    <div style="position:absolute; left:57%; right:20%; top:50%; transform:translateY(-50%); color:#fff; white-space:nowrap;">
        <strong style="display:block; color:#fff; font:900 30px/1 Arial,sans-serif; letter-spacing:.5px;">SLIP GAJI</strong>
        <span style="display:block; padding-top:4px; color:#F3E8FF; font:700 10px/1.2 Arial,sans-serif; letter-spacing:3px;">K A R Y A W A N</span>
    </div>
    <span style="position:absolute; right:2%; top:50%; transform:translateY(-50%); display:inline-block; padding:10px 16px; border-radius:22px; background:#fff; color:#4F16C8; font:900 12px Arial,sans-serif; text-transform:uppercase; white-space:nowrap;">{{ $periode }}</span>
</div>
