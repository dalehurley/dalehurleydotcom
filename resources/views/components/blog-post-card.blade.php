@props(['post', 'featured' => false])

<article class="group {{ $featured ? 'col-span-full lg:col-span-2' : '' }}">
    <a href="/posts/{{ $post['slug'] }}" class="block">
        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 h-full">
            @if ($featured)
                <div class="p-8">
                    <div class="flex items-center text-sm text-orange-600 font-medium mb-3">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Featured Post
                    </div>

                    <h2
                        class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-orange-600 transition-colors line-clamp-2">
                        {{ $post['title'] }}
                    </h2>

                    @if (isset($post['description']))
                        <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg leading-relaxed line-clamp-3">
                            {{ $post['description'] }}
                        </p>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            @if (isset($post['author']))
                                <img src="/images/dale-hurley.jpg" alt="{{ $post['author'] }}"
                                    class="w-6 h-6 rounded-full mr-2">
                                <span class="mr-4">{{ $post['author'] }}</span>
                            @endif

                            @if (isset($post['date']))
                                <time datetime="{{ $post['date'] }}">
                                    {{ Carbon\Carbon::parse($post['date'])->format('M j, Y') }}
                                </time>
                            @endif
                            
                            @if (isset($post['reading_time']))
                                <span class="ml-4 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $post['reading_time'] }} min
                                </span>
                            @endif
                        </div>

                        @if (isset($post['tags']) && is_array($post['tags']) && count($post['tags']) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach (array_slice($post['tags'], 0, 3) as $tag)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                                @if (count($post['tags']) > 3)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        +{{ count($post['tags']) - 3 }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-6">
                    <h3
                        class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-orange-600 transition-colors line-clamp-2">
                        {{ $post['title'] }}
                    </h3>

                    @if (isset($post['description']))
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                            {{ $post['description'] }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            @if (isset($post['date']))
                                <time datetime="{{ $post['date'] }}">
                                    {{ Carbon\Carbon::parse($post['date'])->format('M j') }}
                                </time>
                            @endif
                        </div>

                        <div class="flex items-center text-orange-600 text-sm font-medium">
                            Read more
                            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>

                    @if (isset($post['tags']) && is_array($post['tags']) && count($post['tags']) > 0)
                        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                            @foreach (array_slice($post['tags'], 0, 2) as $tag)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </a>
</article>
