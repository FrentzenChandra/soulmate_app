@props([
    'rows' => 5,
    'cols' => 5,
    'columns' => null,
    'target' => 'search, kategoriFilter, stokFilter, perPage, sortField, sortDirection'
])

@php
    $colCount = $columns ?? $cols ?? 5;
@endphp

<tbody wire:loading.delay wire:target="{{ $target }}" class="divide-y divide-gray-100 bg-white">
    @for($i = 0; $i < $rows; $i++)
        <tr class="animate-pulse">
            @for($j = 0; $j < $colCount; $j++)
                <td class="py-4 px-4">
                    @if($j === 0)
                        <!-- Avatar / Icon / Left column -->
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-200 shrink-0"></div>
                            <div class="space-y-1.5 flex-1">
                                <div class="h-3.5 bg-gray-200 rounded-md w-3/4"></div>
                                <div class="h-2.5 bg-gray-100 rounded-md w-1/2"></div>
                            </div>
                        </div>
                    @elseif($j === $cols - 1)
                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-2">
                            <div class="w-8 h-8 bg-gray-100 rounded-xl"></div>
                            <div class="w-8 h-8 bg-gray-100 rounded-xl"></div>
                        </div>
                    @else
                        <!-- Regular Text Column -->
                        <div class="space-y-1">
                            <div class="h-3 bg-gray-200 rounded-md w-{{ ($j % 2 == 0) ? '3/4' : '1/2' }}"></div>
                        </div>
                    @endif
                </td>
            @endfor
        </tr>
    @endfor
</tbody>
