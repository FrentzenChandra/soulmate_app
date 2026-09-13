@props([
    'field',
    'sortField' => '',
    'sortDirection' => 'asc',
    'align' => 'left'
])

@php
    $alignmentClasses = match($align) {
        'right' => 'text-right justify-end',
        'center' => 'text-center justify-center',
        default => 'text-left justify-start'
    };
@endphp

<th {{ $attributes->merge(['class' => 'py-3.5 px-4 font-extrabold text-[10px] tracking-wider uppercase text-gray-400 select-none cursor-pointer group hover:text-gray-900 transition-colors ' . ($align === 'right' ? 'text-right' : ($align === 'center' ? 'text-center' : 'text-left'))]) }}
    wire:click="sortBy('{{ $field }}')">
    <div class="inline-flex items-center space-x-1.5 {{ $alignmentClasses }}">
        <span>{{ $slot }}</span>
        <span class="inline-flex items-center text-[10px] transition-all">
            @if($sortField === $field)
                @if($sortDirection === 'asc')
                    <svg class="w-3.5 h-3.5 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                @else
                    <svg class="w-3.5 h-3.5 text-[#5E17EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                @endif
            @else
                <svg class="w-3.5 h-3.5 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
            @endif
        </span>
    </div>
</th>