@props(['name', 'description', 'url', 'image'])

<article class="flex flex-col items-start">
    <a href="{{ $url }}" target="_blank" class="w-full">
        <div class="relative w-full">
            <div class="aspect-square w-full rounded-2xl bg-gray-100 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/' . $image) }}" alt="{{ $name }} logo"
                    class="max-h-full max-w-full object-contain p-4">
            </div>
        </div>
    </a>
    <div class="max-w-xl">
        <div class="mt-8 flex items-center gap-x-4 text-xs">
            <span class="relative z-10 rounded-full bg-gray-50 px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-100">
                Active Development
            </span>
        </div>
        <div class="group relative">
            <h3 class="mt-3 text-lg font-semibold leading-6 text-gray-900 group-hover:text-[#FF750F]">
                <a href="{{ $url }}" target="_blank">
                    {{ $name }}
                </a>
            </h3>
            <p class="mt-5 line-clamp-3 text-sm leading-6 text-gray-600">
                {{ $description }}
            </p>
        </div>
    </div>
</article>
