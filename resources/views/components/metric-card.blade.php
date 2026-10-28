@props([
    'label' => '',
    'value' => '0',
    'color' => 'purple',
    'unit' => null,
])

@php
    $colorConfig = [
        'purple' => [
            'border' => 'border-l-[#5E17EB]',
            'box' => 'bg-purple-50 text-[#5E17EB] border-purple-100',
            'text' => 'text-[#5E17EB]',
        ],
        'emerald' => [
            'border' => 'border-l-[#10B981]',
            'box' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'text' => 'text-emerald-700',
        ],
        'amber' => [
            'border' => 'border-l-[#F59E0B]',
            'box' => 'bg-amber-50 text-amber-800 border-amber-100',
            'text' => 'text-amber-800',
        ],
        'blue' => [
            'border' => 'border-l-[#0052FF]',
            'box' => 'bg-blue-50 text-[#0052FF] border-blue-100',
            'text' => 'text-[#0052FF]',
        ],
        'pink' => [
            'border' => 'border-l-[#E6007A]',
            'box' => 'bg-pink-50 text-[#E6007A] border-pink-100',
            'text' => 'text-[#E6007A]',
        ],
    ][$color] ?? [
        'border' => 'border-l-gray-400',
        'box' => 'bg-gray-50 text-gray-700 border-gray-200',
        'text' => 'text-gray-900',
    ];
@endphp

<div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 border-l-4 {{ $colorConfig['border'] }} shadow-xs hover:shadow-sm transition-all flex flex-col justify-between">
    <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block">{{ $label }}</span>
        <div class="w-8 h-8 rounded-xl {{ $colorConfig['box'] }} border flex items-center justify-center shrink-0">
            @if (isset($icon))
                {{ $icon }}
            @elseif ($color === 'purple')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            @elseif ($color === 'emerald')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif ($color === 'amber')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif ($color === 'blue')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            @elseif ($color === 'pink')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @endif
        </div>
    </div>
    <div class="mt-3">
        <span class="text-xl sm:text-2xl font-black font-mono {{ $colorConfig['text'] }} tracking-tight block">
            {{ $value }}
        </span>
        @if ($unit)
            <span class="text-[11px] font-bold text-gray-500 block mt-0.5">{{ $unit }}</span>
        @endif
    </div>
</div>
