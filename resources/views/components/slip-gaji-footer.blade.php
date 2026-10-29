@props(['periode'])

@php($gradientId = 'slip-footer-' . \Illuminate\Support\Str::uuid())

<div style="position:relative; min-height:80px; overflow:hidden; border-top:1px solid #E2E8F0; margin-top:7px;">
    <svg aria-hidden="true" viewBox="0 0 190 86" preserveAspectRatio="none" style="position:absolute; left:0; bottom:0; display:block; width:150px; height:68px;">
        <defs><linearGradient id="{{ $gradientId }}" x1="0" y1="86" x2="150" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#5E17EB"/><stop offset="1" stop-color="#E6007A"/></linearGradient></defs>
        <path fill="url(#{{ $gradientId }})" d="M0 86V58L87 0h43L0 86Z"/>
        <path fill="#E6007A" fill-opacity=".7" d="M0 86V74L112 0h20L0 86Z"/>
        <path fill="#A855F7" fill-opacity=".55" d="M0 86V82L124 0h13L0 86Z"/>
    </svg>
    <table role="presentation" style="position:relative; z-index:1; width:100%; min-height:80px; border-collapse:collapse;">
        <tr>
            <td style="width:42%; padding:8px 6px 4px 28px; vertical-align:top;">
                <strong style="display:block; color:#5E17EB; font:italic 700 24px/1.1 'Brush Script MT',cursive,serif;">Terima Kasih</strong>
                <span style="display:block; max-width:220px; padding-top:3px; color:#475569; font:600 9px/1.35 Arial,sans-serif;">Atas dedikasi dan kerja keras Anda untuk kemajuan Soulmate Komputer.</span>
            </td>
            <td style="width:28%; padding:16px 4px 4px; text-align:center; vertical-align:top; color:#CBD5E1; font:900 italic 9px Arial,sans-serif; letter-spacing:2px;">BERSAMA<br>LEBIH MAJU</td>
            <td style="width:30%; padding:8px 4px 4px; text-align:center; vertical-align:top; color:#111827; font:700 9px/1.25 Arial,sans-serif;">
                Pangkalpinang, {{ $periode }}
                <div style="height:30px;"></div>
                <strong style="display:block; color:#111827; font:900 9px/1.25 Arial,sans-serif; text-transform:uppercase;">Manajemen</strong>
                <span style="display:block; color:#111827; font:700 9px/1.25 Arial,sans-serif;">Soulmate Komputer</span>
            </td>
        </tr>
    </table>
</div>
