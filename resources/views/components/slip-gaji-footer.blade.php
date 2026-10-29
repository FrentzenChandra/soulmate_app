@props(['periode'])

@php($gradientId = 'slip-footer-' . \Illuminate\Support\Str::uuid())

<table role="presentation" style="position:relative; width:100%; border-collapse:collapse; border-top:1px solid #E2E8F0; margin-top:6px;">
    <tr>
        <td style="width:42%; padding:8px 6px 4px 10px; vertical-align:top;">
            <strong style="display:block; color:#5E17EB; font:italic 700 24px/1.1 'Brush Script MT',cursive,serif;">Terima Kasih</strong>
            <span style="display:block; max-width:220px; padding-top:3px; color:#475569; font:600 9px/1.35 Arial,sans-serif;">Atas dedikasi dan kerja keras Anda untuk kemajuan Soulmate Komputer.</span>
        </td>
        <td style="width:28%; padding:16px 4px 4px; text-align:center; vertical-align:top; color:#CBD5E1; font:900 italic 9px Arial,sans-serif; letter-spacing:2px;">
            BERSAMA<br>LEBIH MAJU
        </td>
        <td style="width:30%; padding:8px 4px 4px; text-align:center; vertical-align:top; color:#111827; font:700 9px/1.25 Arial,sans-serif;">
            Pangkalpinang, {{ $periode }}
            <div style="height:38px;"></div>
            <strong style="display:block; color:#111827; font:900 9px/1.25 Arial,sans-serif; text-transform:uppercase;">Manajemen</strong>
            <span style="display:block; color:#111827; font:700 9px/1.25 Arial,sans-serif;">Soulmate Komputer</span>
        </td>
    </tr>
</table>
<div aria-hidden="true" style="height:0; position:relative;">
    <svg viewBox="0 0 160 70" preserveAspectRatio="none" style="position:absolute; left:-10px; bottom:0; width:120px; height:54px;">
        <defs><linearGradient id="{{ $gradientId }}" x1="0" y1="70" x2="120" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#5E17EB"/><stop offset="1" stop-color="#E6007A"/></linearGradient></defs>
        <path fill="url(#{{ $gradientId }})" fill-opacity=".88" d="M0 70V42L72 0h38L0 70Z"/>
        <path fill="#E6007A" fill-opacity=".62" d="M0 70v-12L94 0h18L0 70Z"/>
        <path fill="#8B5CF6" fill-opacity=".45" d="M0 70v-6L102 0h12L0 70Z"/>
    </svg>
</div>
