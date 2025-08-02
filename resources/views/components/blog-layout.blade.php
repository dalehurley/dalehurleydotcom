@props(['post'])

@php
    $title = ($post['title'] ?? 'Blog Post') . ' - Dale Hurley';
    $description = $post['description'] ?? 'Thoughts on AI, technology, and entrepreneurship by Dale Hurley';
    $canonical = $post['canonical'] ?? url()->current();
    $ogImage = isset($post['image']) ? asset($post['image']) : asset('images/dale-hurley-og.jpg');
    $keywords = isset($post['tags'])
        ? implode(', ', array_merge($post['tags'], ['Dale Hurley', 'AI', 'technology', 'entrepreneurship']))
        : 'Dale Hurley, AI, technology, entrepreneurship';
    $publishedTime = isset($post['date']) ? \Carbon\Carbon::parse($post['date'])->toISOString() : null;
    $modifiedTime = $publishedTime; // You can implement a separate modified time field if needed
    $tags = $post['tags'] ?? [];
    // Calculate reading time (approximate)
    $wordCount = isset($post['content']) ? str_word_count(strip_tags($post['content'])) : 0;
    $readingTime = $wordCount > 0 ? ceil($wordCount / 200) : 5; // 200 words per minute average
@endphp

<x-layout :title="$title" :description="$description" :canonical="$canonical" :og-image="$ogImage" og-type="article" :keywords="$keywords"
    :author="$post['author'] ?? 'Dale Hurley'" :published-time="$publishedTime" :modified-time="$modifiedTime" article-section="Blog" :tags="$tags">
    @push('meta')
        <!-- Article structured data -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "BlogPosting",
            "headline": "{{ $post['title'] ?? 'Blog Post' }}",
            "description": "{{ $post['description'] ?? '' }}",
            "image": "{{ $ogImage }}",
            "author": {
                "@@type": "Person",
                "name": "{{ $post['author'] ?? 'Dale Hurley' }}",
                "url": "{{ url('/') }}",
                "sameAs": [
                    "https://twitter.com/dalehurley",
                    "https://linkedin.com/in/dalehurley",
                    "https://github.com/dalehurley"
                ]
            },
            "publisher": {
                "@@type": "Organization",
                "name": "Dale Hurley",
                "logo": {
                    "@@type": "ImageObject",
                    "url": "{{ asset('images/dale-hurley-logo.png') }}"
                }
            },
            @if($publishedTime)
            "datePublished": "{{ $publishedTime }}",
            "dateModified": "{{ $modifiedTime }}",
            @endif
            @if($wordCount > 0)
            "wordCount": {{ $wordCount }},
            "timeRequired": "PT{{ $readingTime }}M",
            @endif
            "mainEntityOfPage": {
                "@@type": "WebPage",
                "@@id": "{{ $canonical }}"
            },
            @if(isset($post['tags']) && count($post['tags']) > 0)
            "keywords": "{{ implode(', ', $post['tags']) }}",
            @endif
            "url": "{{ $canonical }}"
        }
        </script>

        <!-- Breadcrumb structured data -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "{{ url('/') }}"
                },
                {
                    "@@type": "ListItem",
                    "position": 2,
                    "name": "Blog",
                    "item": "{{ url('/posts') }}"
                },
                {
                    "@@type": "ListItem",
                    "position": 3,
                    "name": "{{ $post['title'] ?? 'Blog Post' }}",
                    "item": "{{ $canonical }}"
                }
            ]
        }
        </script>

        <!-- Fix blog content images -->
        <style>
            article img,
            .prose img {
                opacity: 1 !important;
                visibility: visible !important;
                transition: none !important;
            }

            .centered {
                text-align: center;
                margin: 2rem 0;
            }

            .centered img {
                opacity: 1 !important;
                visibility: visible !important;
                transition: none !important;
                display: inline-block !important;
            }
        </style>
    @endpush

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Breadcrumbs -->
        <nav class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li>
                    <a href="{{ url('/') }}" class="hover:text-orange-600 transition-colors">Home</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ url('/posts') }}" class="hover:text-orange-600 transition-colors">Blog</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span
                        class="text-gray-900 dark:text-white">{{ Str::limit($post['title'] ?? 'Blog Post', 50) }}</span>
                </li>
            </ol>
        </nav>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Post Header -->
            <header class="mb-12">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                    {{ $post['title'] ?? 'Blog Post' }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-gray-600 dark:text-gray-400">
                    @if (isset($post['author']))
                        <div class="flex items-center">
                            <img src="/images/dale-hurley-1x1.webp" alt="{{ $post['author'] }}"
                                class="w-8 h-8 rounded-full mr-2" loading="lazy">
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

                    @if (isset($post['reading_time']))
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $post['reading_time'] }} min read
                        </div>
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
                        class="w-full aspect-[3/2] object-cover rounded-lg shadow-lg" loading="lazy">
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

            // Fix images in blog content specifically
            document.addEventListener('DOMContentLoaded', function() {
                // Target all images within the article content
                const articleImages = document.querySelectorAll('article img, .prose img');

                articleImages.forEach(function(img) {
                    // Force visibility immediately
                    img.style.setProperty('opacity', '1', 'important');
                    img.style.setProperty('visibility', 'visible', 'important');
                    img.style.setProperty('transition', 'none', 'important');

                    // Handle load events
                    img.addEventListener('load', function() {
                        this.style.setProperty('opacity', '1', 'important');
                        this.style.setProperty('visibility', 'visible', 'important');
                    });

                    img.addEventListener('error', function() {
                        this.style.setProperty('opacity', '1', 'important');
                        this.style.setProperty('visibility', 'visible', 'important');
                    });

                    // If already loaded
                    if (img.complete) {
                        img.style.setProperty('opacity', '1', 'important');
                        img.style.setProperty('visibility', 'visible', 'important');
                    }
                });

                // Monitor for new images that might be added
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.tagName === 'IMG') {
                                    node.style.setProperty('opacity', '1', 'important');
                                    node.style.setProperty('visibility', 'visible',
                                    'important');
                                }

                                const newImages = node.querySelectorAll ? node.querySelectorAll(
                                    'img') : [];
                                newImages.forEach(function(img) {
                                    img.style.setProperty('opacity', '1', 'important');
                                    img.style.setProperty('visibility', 'visible',
                                        'important');
                                });
                            }
                        });
                    });
                });

                observer.observe(document.querySelector('article') || document.body, {
                    childList: true,
                    subtree: true
                });
            });
        </script>
    @endpush
</x-layout>
