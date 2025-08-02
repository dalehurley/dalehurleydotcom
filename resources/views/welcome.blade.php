<x-layout title="Dale Hurley - AI-Driven Tech Entrepreneur & Banking Innovation Leader"
    description="AI-driven tech entrepreneur, banking innovation leader, and co-founder of Avenue Bank. Expert in fintech solutions, AI automation, and startup development."
    keywords="Dale Hurley, AI entrepreneur, fintech, banking innovation, Avenue Bank, CreditorWatch, AI automation, LEAP Legal Software, tech entrepreneur, startup founder"
    ogType="website" ogImage="{{ asset('images/dale-hurley-og.jpg') }}" canonical="{{ url('/') }}">
    <!-- Hero Section -->
    <div
        class="relative isolate overflow-hidden bg-gradient-to-b from-[#FF750F]/20 via-[#FF750F]/10 to-white dark:from-[#FF750F]/10 dark:via-[#FF750F]/5 dark:to-gray-900 pt-14">
        <div class="mx-auto max-w-7xl px-6 py-32 sm:py-40 lg:px-8">
            <div
                class="mx-auto max-w-3xl lg:mx-0 lg:grid lg:max-w-none lg:grid-cols-2 lg:gap-x-16 lg:gap-y-8 xl:grid-cols-1 xl:grid-rows-1 xl:gap-x-8">
                <div class="lg:col-span-2 xl:col-auto">
                    <h1
                        class="max-w-3xl text-4xl font-bold tracking-tight text-balance text-gray-900 dark:text-white sm:text-6xl">
                        Building the AI future
                    </h1>
                    <p class="mt-6 text-xl font-semibold text-[#4B0600] dark:text-[#FF750F]">
                        AI-Driven Tech Entrepreneur & Banking Innovation Leader
                    </p>
                    <div class="mt-6 space-y-4">
                        <x-achievement-item text="Co-founded Avenue Bank" />
                        <x-achievement-item text="Founding CTO of CreditorWatch" />
                        <x-achievement-item text="$77M+ funding secured" />
                        <x-achievement-item text="Full banking licence (2024)" />
                    </div>
                </div>
                <div
                    class="mt-10 aspect-[16/12] w-full overflow-hidden rounded-xl sm:mt-16 lg:mt-0 lg:max-w-none xl:row-span-2 xl:row-end-2">
                    <img src="{{ asset('images/dale-hurley.jpg') }}" alt="Dale Hurley"
                        class="h-full w-full object-cover object-top" loading="eager" fetchpriority="high"
                        width="600" height="450" style="opacity: 1 !important; visibility: visible !important;"
                        onload="this.style.opacity='1'; this.style.visibility='visible';"
                        onerror="this.style.opacity='1'; this.style.visibility='visible';" />
                </div>
                <div class="mt-10 lg:mt-0 xl:col-end-1 xl:row-start-1">
                    <div class="mt-10 flex items-center gap-x-6">
                        <a href="#contact"
                            class="rounded-md bg-[#FF750F] px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-[#E5670D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF750F]">
                            Get in Touch
                        </a>
                        <a href="#track-record" class="text-base/6 font-semibold text-gray-900 dark:text-white">
                            View Track Record <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Track Record Section -->
    <section id="track-record" class="py-24 sm:py-32 bg-[#FDFDFC] dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Proven Track
                    Record</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Building successful fintech companies and AI solutions
                </p>
            </div>
            <div class="mt-16 max-w-2xl mx-auto space-y-20">
                <!-- LEAP Legal Software -->
                <x-track-record-item year="2024-Present" title="LEAP Legal Software"
                    role="AI Practice Management Engineering Lead"
                    description="Created no-code AI agentic platform for legal firms, transforming how legal professionals manage matter profitability."
                    image="leap-icon.webp" />

                <!-- Avenue Bank -->
                <x-track-record-item year="2018-2024" title="Avenue Bank" role="Co-founder & Executive Leader"
                    description="Secured $77M+ funding, obtained full banking licence in 2024, and implemented 24-hour processing systems that revolutionised customer experience."
                    image="avenue-bank-icon.webp" />

                <!-- CreditorWatch -->
                <x-track-record-item year="2010-2018" title="CreditorWatch" role="CTO & Innovation Director"
                    description="Led technology and product from startup to successful exit, pioneered AI risk score innovation, and implemented user-centric design principles that transformed credit risk assessment."
                    image="creditorwatch-icon.webp" />
            </div>
        </div>
    </section>

    <!-- AI Solutions Section -->
    <section id="ai-solutions"
        class="py-24 sm:py-32 bg-gradient-to-br from-[#FF750F]/10 via-[#FF750F]/5 to-white dark:from-[#FF750F]/5 dark:via-gray-900/50 dark:to-gray-900">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">AI-Powered
                    Solutions</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    I build AI systems that dramatically accelerate business processes
                </p>
            </div>
            <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <x-solution-item title="Rapid Prototyping"
                    description="Transform ideas into functional prototypes in days, not months. Validate concepts quickly and iterate based on real user feedback."
                    svgPath="M13 10V3L4 14h7v7l9-11h-7z" />

                <x-solution-item title="Intelligent Automation"
                    description="Implement AI systems that automate complex business processes, reduce operational costs by 40-60%, and eliminate human error."
                    svgPath="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />

                <x-solution-item title="Strategic Innovation"
                    description="Develop AI-first strategies that create sustainable competitive advantages and open new revenue streams."
                    svgPath="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                    technicalLeadership="Creator of Claude-3 PHP SDK" />
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="py-24 sm:py-32 bg-white dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Current
                    Projects</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Innovative solutions transforming industries through AI
                </p>
            </div>
            <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                @foreach ([
        ['name' => 'DocCheetah', 'desc' => 'AI-powered document processing and analysis', 'url' => 'https://doccheetah.com/', 'image' => 'doc-cheetah-logo.webp'],
        ['name' => 'Spotfillr', 'desc' => 'Intelligent parking space optimisation', 'url' => 'https://spotfillr.com/', 'image' => 'spotfill-logo.webp'],
        ['name' => 'Full.CX', 'desc' => 'Customer experience enhancement platform', 'url' => 'https://full.cx/', 'image' => 'full-cx-logo.webp'],
        ['name' => 'Custom Homework Maker', 'desc' => 'AI-generated personalised learning materials', 'url' => 'https://customhomeworkmaker.com/', 'image' => 'custom-homework-maker-logo.webp'],
        ['name' => '1 to 5 App', 'desc' => 'Simplified rating system for businesses', 'url' => 'https://www.1to5app.com/', 'image' => '1-to-5-app-logo.webp'],
        ['name' => 'RapidReportCard', 'desc' => 'Automated educational assessment tool', 'url' => 'https://rapidreportcard.com/', 'image' => 'rapid-report-card-logo.webp'],
        ['name' => 'Risks.io', 'desc' => 'Enterprise risk management platform', 'url' => 'https://www.risks.io/', 'image' => 'risks-io-logo.webp'],
        ['name' => 'SpeedBrain', 'desc' => 'Cognitive enhancement training system', 'url' => 'https://speedbrain.app/', 'image' => 'speed-brain-app-logo.webp'],
        ['name' => 'Claude 3 API PHP Package', 'desc' => 'Official SDK for Anthropic\'s Claude 3', 'url' => 'https://github.com/claude-php/clwebpaude-3-api', 'image' => 'php-claude-logo.webp'],
    ] as $project)
                    <x-project-item :name="$project['name']" :description="$project['desc']" :url="$project['url']" :image="$project['image']" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 sm:py-32 bg-[#4B0600]/5 dark:bg-gray-800/50">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Let's Connect
                </h2>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Interested in AI solutions for your business? Reach out to discuss how we can work together.
                </p>
            </div>

            @if (session('success'))
                <div class="mx-auto mt-8 max-w-xl">
                    <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.53a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-400">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-auto mt-8 max-w-xl">
                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-400">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mx-auto mt-16 max-w-xl sm:mt-20">
                <form action="{{ route('contact.submit') }}" method="POST"
                    class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2">
                        <label for="name"
                            class="block text-sm font-semibold leading-6 text-gray-900 dark:text-white">Name</label>
                        <div class="mt-2.5">
                            <input type="text" name="name" id="name" autocomplete="name"
                                value="{{ old('name') }}" required
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6 @error('name') ring-red-500 dark:ring-red-400 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email"
                            class="block text-sm font-semibold leading-6 text-gray-900 dark:text-white">Email</label>
                        <div class="mt-2.5">
                            <input type="email" name="email" id="email" autocomplete="email"
                                value="{{ old('email') }}" required
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6 @error('email') ring-red-500 dark:ring-red-400 @enderror">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message"
                            class="block text-sm font-semibold leading-6 text-gray-900 dark:text-white">Message</label>
                        <div class="mt-2.5">
                            <textarea name="message" id="message" rows="4" required
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6 @error('message') ring-red-500 dark:ring-red-400 @enderror">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="block w-full rounded-md bg-[#FF750F] px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-[#E5670D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF750F] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
        <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Person",
        "name": "Dale Hurley",
        "jobTitle": "AI-Driven Tech Entrepreneur & Banking Innovation Leader",
        "description": "AI-driven tech entrepreneur, banking innovation leader, and co-founder of Avenue Bank. Expert in fintech solutions, AI automation, and startup development.",
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
    @endpush

</x-layout>
