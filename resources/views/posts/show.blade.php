<x-blog-layout :post="$post">
    @push('meta')
        <!-- Additional SEO meta for individual posts -->
        <meta name="generator" content="Laravel {{ app()->version() }}">
        <meta name="rating" content="general">
        <meta name="revisit-after" content="7 days">

        <!-- Schema.org breadcrumb -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "{{ url('/') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": "Blog",
                    "item": "{{ url('/posts') }}"
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": "{{ $post['title'] ?? 'Blog Post' }}",
                    "item": "{{ url('/posts/' . ($post['slug'] ?? '')) }}"
                }
            ]
        }
        </script>
    @endpush

    {!! $post['html_content'] !!}
</x-blog-layout>
