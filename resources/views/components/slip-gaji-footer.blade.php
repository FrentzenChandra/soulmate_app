@props(['periode'])

@php($gradientId = 'slip-footer-' . \Illuminate\Support\Str::uuid())

<div class="slip-gaji-footer" style="position:relative; min-height:48px; overflow:hidden; margin-top:4px;">
    <svg aria-hidden="true" viewBox="0 0 190 86" preserveAspectRatio="none" style="position:absolute; left:0; bottom:0; display:block; width:50px; height:50px;">
        <defs><linearGradient id="{{ $gradientId }}" x1="0" y1="86" x2="150" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#5E17EB"/><stop offset="1" stop-color="#E6007A"/></linearGradient></defs>
        <path fill="url(#{{ $gradientId }})" d="M0 86V58L87 0h43L0 86Z"/>
        <path fill="#7C3AED" fill-opacity=".8" d="M0 86V74L112 0h20L0 86Z"/>
        <path fill="#C084FC" fill-opacity=".7" d="M0 86V82L124 0h13L0 86Z"/>
    </svg>
    <table role="presentation" style="position:relative; z-index:1; width:100%; min-height:48px; border-collapse:collapse;">
        <tr>
            <td style="width:42%; padding:2px 6px 2px 38px; vertical-align:top;">
                <strong style="display:block; color:#32117C; font:italic 700 20px/1.1 'Dancing Script','Brush Script MT',cursive,serif;">Terima Kasih</strong>
                <span style="display:block; max-width:250px; padding:0 0 0 28px; color:#475569; font:600 9px/1.15 Arial,sans-serif;">Atas dedikasi dan kerja keras Anda untuk kemajuan Soulmate Komputer.</span>
            </td>
            <td style="width:28%; padding:8px 4px 2px; text-align:center; vertical-align:top; color:#C4B5FD; font:900 italic 8.5px Arial,sans-serif; letter-spacing:1.5px;">BERSAMA<br>LEBIH MAJU</td>
            <td style="width:30%; padding:2px 8px 2px 4px; text-align:right; vertical-align:top; color:#111827; font:700 8.5px/1.2 Arial,sans-serif;">
                Pangkalpinang, {{ $periode }}
                <div style="height:14px;"></div>
                <strong style="display:block; color:#111827; font:900 8.5px/1.2 Arial,sans-serif; text-transform:uppercase;">Manajemen</strong>
                <span style="display:block; color:#111827; font:700 8.5px/1.2 Arial,sans-serif;">Soulmate Komputer</span>
            </td>
        </tr>
    </table>
</div>
