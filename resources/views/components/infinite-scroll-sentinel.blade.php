@props([
    'hasMore' => false,
    'target' => 'loadMore',
    'label' => 'data',
    'current' => 0,
    'total' => 0,
    'color' => 'purple' // 'purple' | 'blue'
])

@php
    $textColor = $color === 'blue' ? 'text-[#0052FF]' : 'text-[#5E17EB]';
    $bgPulse = $color === 'blue' ? 'bg-[#0052FF]' : 'bg-[#5E17EB]';
    $btnBorder = $color === 'blue' ? 'hover:border-[#0052FF] hover:text-[#0052FF]' : 'hover:border-[#5E17EB] hover:text-[#5E17EB]';
@endphp

<div class="w-full flex justify-center py-4">
    @if($hasMore)
        <div x-intersect.threshold.10="$wire.{{ $target }}()" 
             class="w-full max-w-md flex flex-col items-center justify-center space-y-3 text-center">
            
            <!-- Loading State: Centered Spinner, Text, Bouncing Dots & Animated Progress Bar -->
            <div wire:loading wire:target="{{ $target }}" class="w-full flex flex-col items-center justify-center space-y-2.5">
                
                <!-- Bouncing Dots & Label -->
                <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full bg-white border border-gray-200/80 shadow-xs">
                    <span class="text-xs font-extrabold text-gray-700">Memuat {{ $label }}</span>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full {{ $bgPulse }} animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 rounded-full {{ $bgPulse }} animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 rounded-full {{ $bgPulse }} animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>

                <!-- Animated Sleek Progress Bar -->
                <div class="w-48 sm:w-64 h-1.5 bg-gray-200/80 rounded-full overflow-hidden relative">
                    <div class="h-full {{ $bgPulse }} rounded-full animate-pulse w-full"></div>
                </div>

                @if($total > 0 && $current > 0)
                    <p class="text-[11px] font-semibold text-gray-400">
                        Menampilkan <span class="font-bold text-gray-600">{{ $current }}</span> dari <span class="font-bold text-gray-600">{{ $total }}</span> {{ $label }}
                    </p>
                @endif
            </div>

            <!-- Fallback / Idle State Button (Centered) -->
            <div wire:loading.remove wire:target="{{ $target }}" class="flex flex-col items-center space-y-1.5">
                <button type="button"
                        wire:click="{{ $target }}"
                        class="px-5 py-2 rounded-full bg-white border border-gray-200/90 text-xs font-bold text-gray-600 {{ $btnBorder }} shadow-xs hover:shadow-md transition-all cursor-pointer inline-flex items-center space-x-2 group">
                    <svg class="w-3.5 h-3.5 text-gray-400 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <span>Scroll atau klik untuk muat lebih banyak {{ $label }}</span>
                </button>

                @if($total > 0 && $current > 0)
                    <span class="text-[11px] font-medium text-gray-400">
                        ({{ $current }} dari {{ $total }} {{ $label }} termuat)
                    </span>
                @endif
            </div>
        </div>
    @elseif($total > 0)
        <div class="py-2 px-5 text-center text-xs font-semibold text-gray-400 flex items-center justify-center space-x-2 bg-white/70 rounded-full border border-gray-100 shadow-2xs">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Seluruh <strong>{{ $total }}</strong> {{ $label }} telah ditampilkan</span>
        </div>
    @endif
</div>
