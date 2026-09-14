@props([
    'target' => 'foto',
    'title' => 'Mengunggah & memproses preview foto...',
    'subtitle' => 'Mohon tunggu sebentar sebelum menyimpan',
    'color' => 'purple' // 'purple' | 'blue'
])

@php
    $bgPulse = $color === 'blue' ? 'bg-[#0052FF]' : 'bg-[#5E17EB]';
    $textColor = $color === 'blue' ? 'text-[#0052FF]' : 'text-[#5E17EB]';
    $borderColor = $color === 'blue' ? 'border-blue-200 bg-blue-50/70' : 'border-purple-200 bg-purple-50/70';
@endphp

<div wire:loading wire:target="{{ $target }}" class="w-full p-5 mb-3 {{ $borderColor }} border rounded-2xl flex flex-col items-center justify-center text-center space-y-2.5 shadow-xs">
    <!-- Spinner & Label -->
    <div class="inline-flex items-center space-x-2">
        <svg class="animate-spin h-5 w-5 {{ $textColor }} shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span class="text-xs font-extrabold text-gray-900">{{ $title }}</span>
    </div>

    <!-- Centered Animated Progress Bar -->
    <div class="w-48 sm:w-64 h-1.5 bg-gray-200 rounded-full overflow-hidden relative">
        <div class="h-full {{ $bgPulse }} rounded-full animate-pulse w-full"></div>
    </div>

    <p class="text-[11px] font-medium text-gray-500">{{ $subtitle }}</p>
</div>
