<x-layout>
    <x-slot name="title">Blog Posts - Dale Hurley</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                    Blog Posts
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Thoughts on AI, technology, and entrepreneurship
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <a href="{{ $post['url'] }}"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">

                        @if (isset($post['thumbnail']))
                            <div class="aspect-[3/2] overflow-hidden">
                                <img src="{{ $post['thumbnail'] }}" alt="{{ $post['title'] ?? 'Blog post image' }}"
                                    class="w-full aspect-[3/2] object-cover hover:scale-103 transition-transform duration-300">
                            </div>
                        @endif

                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                                {{ $post['title'] ?? 'Untitled Post' }}
                            </h2>

                            @if (isset($post['description']))
                                <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                    {{ $post['description'] }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                @if (isset($post['date']))
                                    <time datetime="{{ $post['date'] }}">
                                        {{ \Carbon\Carbon::parse($post['date'])->format('M j, Y') }}
                                    </time>
                                @endif

                                @if (isset($post['author']))
                                    <span>{{ $post['author'] }}</span>
                                @endif
                            </div>

                            @if (isset($post['tags']) && is_array($post['tags']) && count($post['tags']) > 0)
                                <div class="flex flex-wrap gap-2 mt-4">
                                    @foreach (array_slice($post['tags'], 0, 3) as $tag)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                    @if (count($post['tags']) > 3)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            +{{ count($post['tags']) - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No posts found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layout>
