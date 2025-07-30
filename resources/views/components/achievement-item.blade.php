@props(['text', 'icon' => true])

<p class="flex items-center text-lg text-gray-700">
    @if ($icon)
        <svg class="h-5 w-5 text-[#FF750F] mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd" />
        </svg>
    @endif
    {{ $text }}
</p>
