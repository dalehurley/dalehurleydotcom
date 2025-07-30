@props(['title', 'description', 'svgPath', 'technicalLeadership' => null])

<div
    class="flex flex-col bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0 p-3 rounded-lg bg-[#FF750F]/10 dark:bg-[#FF750F]/20">
            <svg class="h-6 w-6 text-[#FF750F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $svgPath }}" />
            </svg>
        </div>
    </div>
    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-4 flex-1 text-base text-gray-600 dark:text-gray-300">
        {{ $description }}
    </p>
    @if ($technicalLeadership)
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm font-medium text-gray-900 dark:text-white">Technical Leadership:</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $technicalLeadership }}</p>
        </div>
    @endif
</div>
