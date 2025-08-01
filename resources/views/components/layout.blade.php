@props([
    'title' => 'Dale Hurley - AI-Driven Tech Entrepreneur & Banking Innovation Leader',
    'description' =>
        'AI-driven tech entrepreneur, banking innovation leader, and co-founder of Avenue Bank. Expert in fintech solutions, AI automation, and startup development.',
    'canonical' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'keywords' =>
        'Dale Hurley, AI entrepreneur, fintech, banking innovation, Avenue Bank, CreditorWatch, AI automation',
    'author' => 'Dale Hurley',
    'publishedTime' => null,
    'modifiedTime' => null,
    'articleSection' => null,
    'tags' => [],
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <!-- Meta Description -->
    <meta name="description" content="{{ $description }}">

    <!-- Meta Keywords -->
    <meta name="keywords" content="{{ $keywords }}">

    <!-- Author -->
    <meta name="author" content="{{ $author }}">

    <!-- Canonical URL -->
    @if ($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    <!-- Robots -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Performance & SEO Optimizations -->
    <meta name="theme-color" content="#FF750F">
    <meta name="msapplication-TileColor" content="#FF750F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Favicon and Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Resource Hints for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="dns-prefetch" href="//dalehurley.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <!-- Open Graph Tags -->
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="Dale Hurley">
    <meta property="og:locale" content="en_US">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $title }}">
    @else
        <meta property="og:image" content="{{ asset('images/dale-hurley-og.jpg') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="Dale Hurley - AI Entrepreneur">
    @endif

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@dalehurley">
    <meta name="twitter:creator" content="@dalehurley">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @else
        <meta name="twitter:image" content="{{ asset('images/dale-hurley-og.jpg') }}">
    @endif

    <!-- Article specific meta tags -->
    @if ($ogType === 'article')
        <meta property="article:author" content="{{ $author }}">
        @if ($publishedTime)
            <meta property="article:published_time" content="{{ $publishedTime }}">
        @endif
        @if ($modifiedTime)
            <meta property="article:modified_time" content="{{ $modifiedTime }}">
        @endif
        @if ($articleSection)
            <meta property="article:section" content="{{ $articleSection }}">
        @endif
        @foreach ($tags as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @stack('styles')
    @stack('meta')

    <!-- Default JSON-LD Structured Data -->
    @if ($ogType === 'website')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Person",
        "name": "Dale Hurley",
        "jobTitle": "AI-Driven Tech Entrepreneur & Banking Innovation Leader",
        "description": "{{ $description }}",
        "url": "{{ url('/') }}",
        "image": "{{ asset('images/dale-hurley.jpg') }}",
        "sameAs": [
            "https://www.linkedin.com/in/dalehurley/",
            "https://github.com/dalehurley",
            "https://twitter.com/dalehurley"
        ],
        "worksFor": [
            {
                "@@type": "Organization",
                "name": "LEAP Legal Software",
                "description": "AI Practice Management Engineering Lead"
            }
        ],
        "knowsAbout": [
            "Artificial Intelligence",
            "Fintech",
            "Banking Innovation",
            "Startup Development",
            "AI Automation",
            "Software Engineering"
        ],
        "alumniOf": {
            "@@type": "Organization",
            "name": "CreditorWatch",
            "description": "Former CTO & Innovation Director"
        }
    }
    </script>
    @endif
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- Skip to content link for accessibility -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 bg-[#FF750F] text-white px-4 py-2 z-50">
        Skip to main content
    </a>

    <header class="absolute inset-x-0 top-0 z-50">
        <nav aria-label="Main navigation" class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8">
            <div class="flex lg:flex-1">
                <a href="/" class="-m-1.5 p-1.5" aria-label="Dale Hurley homepage">
                    <span class="sr-only">Dale Hurley</span>
                    <span class="text-xl font-bold text-[#FF750F]">DaleHurley.com</span>
                </a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12" role="navigation">
                <a href="/"
                    class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-[#FF750F] transition-colors"
                    @if (request()->is('/')) aria-current="page" @endif>Home</a>
                <a href="/posts"
                    class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-[#FF750F] transition-colors"
                    @if (request()->is('posts*')) aria-current="page" @endif>Blog</a>
            </div>
        </nav>
    </header>

    <main id="main-content" class="pt-16" role="main">
        {{ $slot }}
    </main>

    <x-footer :showDetailedFooter="true" class="bg-gray-900 text-white py-12" />

    @stack('scripts')
</body>

</html>
