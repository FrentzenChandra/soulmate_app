@props(['periode'])

@php
    $logoPath = public_path('images/logo_horizontal.png');
    $logo = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    $gradientId = 'slip-header-' . \Illuminate\Support\Str::uuid();
@endphp

<table role="presentation" style="width:100%; border-collapse:collapse; border-bottom:1px solid #E2E8F0; margin-bottom:6px;">
    <tr>
        <td style="width:48%; vertical-align:middle; padding:4px 12px 7px 2px;">
            @if($logo)
                <img src="{{ $logo }}" alt="Soulmate Komputer" style="display:block; width:100%; max-width:250px; height:72px; object-fit:contain; object-position:left center;">
            @else
                <strong style="font:900 17px Arial,sans-serif; color:#5E17EB;">SOULMATE KOMPUTER</strong>
            @endif
        </td>
        <td style="width:52%; vertical-align:middle; padding:0;">
            <div style="position:relative; overflow:hidden; height:76px; color:#fff; background:#7216D8; border-radius:12px 24px 24px 12px;">
                <svg aria-hidden="true" viewBox="0 0 600 100" preserveAspectRatio="none" style="position:absolute; top:0; left:0; display:block; width:100%; height:100%;">
                    <defs>
                        <linearGradient id="{{ $gradientId }}" x1="0" y1="50" x2="600" y2="50" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#5E17EB"/><stop offset=".58" stop-color="#8B2FF5"/><stop offset="1" stop-color="#E6007A"/>
                        </linearGradient>
                    </defs>
                    <path fill="url(#{{ $gradientId }})" d="M0 0h600v100H76L0 0Z"/>
                    <path fill="#fff" fill-opacity=".2" d="M0 0h42l76 100H76L0 0Z"/>
                    <path fill="#fff" fill-opacity=".28" d="M40 0h15l75 100h-15L40 0Z"/>
                </svg>
                <table role="presentation" style="position:relative; z-index:1; width:100%; height:66px; border-collapse:collapse; color:#fff;">
                    <tr>
                        <td style="padding:9px 6px 9px 18px; color:#fff; vertical-align:middle;">
                            <strong style="display:block; color:#fff; font:900 19px/1.05 Arial,sans-serif; letter-spacing:.7px;">SLIP GAJI</strong>
                            <span style="display:block; padding-top:4px; color:#F3E8FF; font:700 8px/1.2 Arial,sans-serif; letter-spacing:2.4px;">K A R Y A W A N</span>
                        </td>
                        <td style="width:42%; padding:0 12px 0 4px; text-align:right; vertical-align:middle;">
                            <span style="display:inline-block; padding:8px 13px; border-radius:18px; background:#fff; color:#5E17EB; font:900 9px Arial,sans-serif; text-transform:uppercase; white-space:nowrap;">{{ $periode }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
