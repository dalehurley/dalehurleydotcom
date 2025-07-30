@props(['post'])

<x-layout>
    <x-slot name="title">{{ $post['title'] ?? 'Blog Post' }} - Dale Hurley</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <!-- Post Header -->
            <header class="mb-12">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                    {{ $post['title'] ?? 'Blog Post' }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-gray-600 dark:text-gray-400">
                    @if (isset($post['author']))
                        <div class="flex items-center">
                            <img src="/images/dale-hurley-1x1.png" alt="{{ $post['author'] }}"
                                class="w-8 h-8 rounded-full mr-2">
                            <span>{{ $post['author'] }}</span>
                        </div>
                    @endif

                    @if (isset($post['date']))
                        <time datetime="{{ $post['date'] }}" class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ Carbon\Carbon::parse($post['date'])->format('F j, Y') }}
                        </time>
                    @endif
                </div>

                @if (isset($post['description']))
                    <p class="text-xl text-gray-600 dark:text-gray-400 mt-4 leading-relaxed prose prose-lg">
                        {{ $post['description'] }}
                    </p>
                @endif

                @if (isset($post['tags']) && is_array($post['tags']) && count($post['tags']) > 0)
                    <div class="flex flex-wrap gap-2 mt-6">
                        @foreach ($post['tags'] as $tag)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </header>

            <!-- Hero Image -->
            @if (isset($post['image']))
                <div class="mb-12">
                    <img src="{{ $post['image'] }}" alt="{{ $post['title'] ?? 'Blog post hero image' }}"
                        class="w-full aspect-[3/2] object-cover rounded-lg shadow-lg">
                </div>
            @endif

            <!-- Post Content -->
            <article
                class="prose prose-lg max-w-none dark:prose-invert prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-pre:px-1 prose-pre:py-0.5 prose-pre:rounded prose-pre:text-sm prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-orange-600 hover:prose-a:text-orange-500 prose-strong:text-gray-900 dark:prose-strong:text-white prose-code:text-orange-600 prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-code:text-sm">
                {{ $slot }}
            </article>

            <!-- Post Navigation -->
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <a href="/posts"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        All Posts
                    </a>

                    <div class="flex space-x-4">
                        <button onclick="sharePost()" class="p-2 text-gray-600 hover:text-orange-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function sharePost() {
                if (navigator.share) {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    });
                } else {
                    navigator.clipboard.writeText(window.location.href);
                    alert('URL copied to clipboard!');
                }
            }
        </script>
    @endpush
</x-layout>
