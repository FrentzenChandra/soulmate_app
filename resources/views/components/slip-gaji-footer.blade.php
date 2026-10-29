@props(['periode'])

@php($gradientId = 'slip-footer-' . \Illuminate\Support\Str::uuid())

<div class="slip-gaji-footer" style="position:relative; min-height:80px; overflow:hidden; margin-top:7px;">
    <svg aria-hidden="true" viewBox="0 0 190 86" preserveAspectRatio="none" style="position:absolute; left:0; bottom:0; display:block; width:105px; height:82px;">
        <defs><linearGradient id="{{ $gradientId }}" x1="0" y1="86" x2="150" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#5E17EB"/><stop offset="1" stop-color="#E6007A"/></linearGradient></defs>
        <path fill="url(#{{ $gradientId }})" d="M0 86V58L87 0h43L0 86Z"/>
        <path fill="#7C3AED" fill-opacity=".8" d="M0 86V74L112 0h20L0 86Z"/>
        <path fill="#C084FC" fill-opacity=".7" d="M0 86V82L124 0h13L0 86Z"/>
    </svg>
    <table role="presentation" style="position:relative; z-index:1; width:100%; min-height:80px; border-collapse:collapse;">
        <tr>
            <td style="width:42%; padding:8px 6px 4px 35px; vertical-align:top;">
                <strong style="display:block; color:#32117C; font:italic 700 27px/1.1 'Dancing Script','Brush Script MT',cursive,serif;">Terima Kasih</strong>
                <span style="display:block; max-width:250px; padding:0 0 0 46px; color:#475569; font:600 11px/1.2 Arial,sans-serif;">Atas dedikasi dan kerja keras Anda untuk kemajuan Soulmate Komputer.</span>
            </td>
            <td style="width:28%; padding:18px 4px 4px; text-align:center; vertical-align:top; color:#C4B5FD; font:900 italic 10px Arial,sans-serif; letter-spacing:2px;">BERSAMA<br>LEBIH MAJU</td>
            <td style="width:30%; padding:8px 8px 4px 4px; text-align:right; vertical-align:top; color:#111827; font:700 9px/1.25 Arial,sans-serif;">
                Pangkalpinang, {{ $periode }}
                <div style="height:30px;"></div>
                <strong style="display:block; color:#111827; font:900 9px/1.25 Arial,sans-serif; text-transform:uppercase;">Manajemen</strong>
                <span style="display:block; color:#111827; font:700 9px/1.25 Arial,sans-serif;">Soulmate Komputer</span>
            </td>
        </tr>
    </table>
</div>
