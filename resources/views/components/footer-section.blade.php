@props(['title', 'links'])

<div>
    <h3 class="text-sm/6 font-semibold text-white dark:text-gray-200">{{ $title }}</h3>
    <ul role="list" class="mt-6 space-y-4">
        @foreach ($links as $link)
            <li>
                <a href="{{ $link['href'] }}"
                    class="text-sm/6 text-gray-300 dark:text-gray-400 hover:text-white dark:hover:text-gray-200">{{ $link['text'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
