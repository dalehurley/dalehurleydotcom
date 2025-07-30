@props(['href', 'name', 'svgPath'])

<a href="{{ $href }}" class="text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200">
    <span class="sr-only">{{ $name }}</span>
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="size-6">
        <path d="{{ $svgPath }}" />
    </svg>
</a>
