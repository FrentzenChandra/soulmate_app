<div class="space-y-6 animate-pulse">
    <!-- Page Header & Action Skeleton -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-100/60 shrink-0"></div>
            <div class="space-y-1.5">
                <div class="h-5 w-36 bg-gray-200 rounded-lg"></div>
                <div class="h-3 w-64 bg-gray-100 rounded-md"></div>
            </div>
        </div>
        <div class="h-9 w-28 bg-purple-100/50 rounded-xl"></div>
    </div>

    <!-- Summary Metrics Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-2">
            <div class="h-3 w-28 bg-gray-200 rounded"></div>
            <div class="h-7 w-20 bg-gray-100 rounded-lg"></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-2">
            <div class="h-3 w-36 bg-gray-200 rounded"></div>
            <div class="h-7 w-20 bg-purple-100/60 rounded-lg"></div>
        </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div class="h-8 w-64 bg-gray-100 rounded-xl"></div>
            <div class="h-8 w-44 bg-gray-100 rounded-xl"></div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="h-8 w-60 bg-gray-100 rounded-xl"></div>
            <div class="h-8 w-24 bg-gray-100 rounded-xl"></div>
        </div>
    </div>

    <!-- Table Skeleton -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex space-x-6">
            <div class="h-4 w-24 bg-gray-200 rounded"></div>
            <div class="h-4 w-20 bg-gray-200 rounded"></div>
            <div class="h-4 w-32 bg-gray-200 rounded"></div>
            <div class="h-4 w-24 bg-gray-200 rounded"></div>
        </div>
        <div class="divide-y divide-gray-100">
            @for ($i = 0; $i < 6; $i++)
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-4 w-24 bg-gray-100 rounded"></div>
                        <div class="h-4 w-16 bg-gray-100 rounded"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-7 h-7 rounded-full bg-gray-200"></div>
                            <div class="h-4 w-28 bg-gray-200 rounded"></div>
                        </div>
                    </div>
                    <div class="h-6 w-20 bg-gray-100 rounded-lg"></div>
                </div>
            @endfor
        </div>
    </div>
</div>

