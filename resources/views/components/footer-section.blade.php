@props(['title', 'links'])

<div>
    <h3 class="text-sm/6 font-semibold text-white">{{ $title }}</h3>
    <ul role="list" class="mt-6 space-y-4">
        @foreach ($links as $link)
            <li>
                <a href="{{ $link['href'] }}" class="text-sm/6 text-gray-300 hover:text-white">{{ $link['text'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
