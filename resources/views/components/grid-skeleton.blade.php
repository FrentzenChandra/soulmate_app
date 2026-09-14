@props([
    'cards' => 8,
    'target' => 'search, kategoriFilter, selectedKategori'
])

<div wire:loading.delay wire:target="{{ $target }}" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5 w-full">
    @for($i = 0; $i < $cards; $i++)
        <div class="bg-white rounded-2xl border border-gray-100 p-3 shadow-2xs space-y-3 animate-pulse">
            <div class="w-full h-28 bg-gray-200 rounded-xl"></div>
            <div class="space-y-1.5">
                <div class="h-3.5 bg-gray-200 rounded-md w-4/5"></div>
                <div class="h-2.5 bg-gray-100 rounded-md w-1/2"></div>
            </div>
            <div class="flex items-center justify-between pt-1">
                <div class="h-4 bg-purple-100 rounded-md w-2/5"></div>
                <div class="w-7 h-7 bg-gray-100 rounded-lg"></div>
            </div>
        </div>
    @endfor
</div>
