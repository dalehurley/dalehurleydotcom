@props(['title' => 'Dale Hurley - AI-Driven Tech Entrepreneur & Banking Innovation Leader'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @stack('styles')
</head>

<body class="bg-white dark:bg-gray-900">
    <header class="absolute inset-x-0 top-0 z-50">
        <nav aria-label="Global" class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8">
            <div class="flex lg:flex-1">
                <a href="/" class="-m-1.5 p-1.5">
                    <span class="sr-only">Dale Hurley</span>
                    <span class="text-xl font-bold text-[#FF750F]">DaleHurley.com</span>
                </a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="/" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Home</a>
                <a href="/posts" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Blog</a>
            </div>
        </nav>
    </header>

    <main class="pt-16">
        {{ $slot }}
    </main>

    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-400">
                    © {{ date('Y') }} Dale Hurley. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
