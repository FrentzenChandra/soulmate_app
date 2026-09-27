<?php

if (! function_exists('format_phone')) {
    /**
     * Format nomor telepon / WhatsApp ke format standar:
     * - Ubah prefix 62/+62 menjadi 0
     * - Pisahkan setiap 4 digit dengan tanda hubung (-)
     * Contoh: 6281995466255 -> 0819-9546-6255
     */
    function format_phone(?string $phone, ?string $fallback = '-'): string
    {
        if (blank($phone) || $phone === '-') {
            return $fallback ?? '-';
        }

        $digits = preg_replace('/[^\d]/', '', (string) $phone);
        if (empty($digits)) {
            return $fallback ?? '-';
        }

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        } elseif (! str_starts_with($digits, '0')) {
            $digits = '0' . $digits;
        }

        return implode('-', str_split($digits, 4));
    }
}

if (! function_exists('format_whatsapp')) {
    /**
     * Alias untuk format_phone khusus WhatsApp
     */
    function format_whatsapp(?string $phone, ?string $fallback = '-'): string
    {
        return format_phone($phone, $fallback);
    }
}
