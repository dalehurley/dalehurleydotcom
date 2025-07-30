<?php

use App\Services\BlogService;

$blogService = new BlogService();
$posts = $blogService->getAllPosts();
$featuredPosts = array_slice($posts, 0, 1);
$regularPosts = array_slice($posts, 1);

?>

<x-layout>
    <x-slot name="title">Blog - Dale Hurley</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <header class="text-center mb-16">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                    Insights & Innovation
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed">
                    Thoughts on AI, fintech innovation, entrepreneurship, and building technology that drives real
                    business value.
                </p>
            </header>

            <!-- Search Bar -->
            <div class="mb-12">
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" id="search-posts" placeholder="Search posts..."
                            class="w-full px-4 py-3 pl-12 pr-4 text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Post -->
            @if (count($featuredPosts) > 0)
                <section class="mb-16">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        @foreach ($featuredPosts as $post)
                            <x-blog-post-card :post="$post" :featured="true" />
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Regular Posts Grid -->
            @if (count($regularPosts) > 0)
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">All Posts</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="posts-grid">
                        @foreach ($regularPosts as $post)
                            <x-blog-post-card :post="$post" />
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- No Posts Message -->
            @if (count($posts) === 0)
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No posts yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Blog posts will appear here soon.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Simple search functionality
            document.getElementById('search-posts').addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase();
                const posts = document.querySelectorAll('#posts-grid article');

                posts.forEach(post => {
                    const title = post.querySelector('h3')?.textContent.toLowerCase() || '';
                    const description = post.querySelector('p')?.textContent.toLowerCase() || '';
                    const tags = Array.from(post.querySelectorAll('.inline-flex')).map(tag => tag.textContent
                        .toLowerCase()).join(' ');

                    const matches = title.includes(query) || description.includes(query) || tags.includes(
                    query);
                    post.style.display = matches ? 'block' : 'none';
                });
            });
        </script>
    @endpush
</x-layout>
