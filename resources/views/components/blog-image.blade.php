@props(['src', 'alt', 'width' => null, 'height' => null, 'caption' => null])

@php
    // Set default width if not provided
    $width = $width ?? 'auto';
    $height = $height ?? 'auto';
@endphp

<div class="my-8">
    <figure class="relative">
        <img src="{{ $src }}" alt="{{ $alt }}"
            @if ($width !== 'auto') width="{{ $width }}" @endif
            @if ($height !== 'auto') height="{{ $height }}" @endif
            class="w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mx-auto"
            loading="lazy" />

        @if ($caption)
            <figcaption class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400 italic">
                {{ $caption }}
            </figcaption>
        @endif
    </figure>
</div>
