@props(['year', 'title', 'role', 'description', 'image'])

<div class="relative pl-8 sm:pl-32 group">
    <div class="font-semibold text-[#FF750F] group-hover:text-[#4B0600] dark:group-hover:text-[#FF9F5F]">
        {{ $year }}</div>
    <div class="flex flex-col items-start">
        <div class="flex items-center">
            @if ($image)
                <img src="{{ asset('images/' . $image) }}" alt="{{ $title }} logo"
                    class="w-10 h-10 mr-3 rounded-full object-cover"
                    loading="lazy" />
            @endif
            <div class="text-2xl font-bold text-gray-900 dark:text-white"> {{ $title }}</div>
        </div>
        <div class="mt-2 text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $role }}</div>
        <p class="mt-4 text-base text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>
    <div
        class="absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-white dark:bg-gray-700 group-hover:bg-[#FF750F] shadow-md">
        <div class="h-2 w-2 rounded-full bg-gray-300 dark:bg-gray-500 group-hover:bg-white"></div>
    </div>
</div>
