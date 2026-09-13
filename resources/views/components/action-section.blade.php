<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden']) }}>
    <!-- Header Title Area -->
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h3 class="text-base font-extrabold text-gray-900">{{ $title }}</h3>
            <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $description }}</p>
        </div>
        @if (isset($aside))
            <div>
                {{ $aside }}
            </div>
        @endif
    </div>

    <!-- Content Area -->
    <div class="p-6 sm:p-8">
        {{ $content }}
    </div>
</div>
