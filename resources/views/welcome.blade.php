<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dale Hurley - AI-Driven Tech Entrepreneur & Banking Innovation Leader</title>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-white">
    <header class="absolute inset-x-0 top-0 z-50">
        <nav aria-label="Global" class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">Dale Hurley</span>
                    <span class="text-xl font-bold text-[#FF750F]">DaleHurley.com</span>
                </a>
            </div>
            <div class="flex lg:hidden">
                <button type="button" command="show-modal" commandfor="mobile-menu"
                    class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700">
                    <span class="sr-only">Open menu</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                        aria-hidden="true" class="size-6">
                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="/posts" class="text-sm/6 font-semibold text-gray-900">Posts</a>
                <a href="#track-record" class="text-sm/6 font-semibold text-gray-900">Track Record</a>
                <a href="#ai-solutions" class="text-sm/6 font-semibold text-gray-900">AI Solutions</a>
                <a href="#projects" class="text-sm/6 font-semibold text-gray-900">Projects</a>
                <a href="#contact" class="text-sm/6 font-semibold text-gray-900">Contact</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                <a href="#contact" class="text-sm/6 font-semibold text-[#FF750F]">Let's Connect <span
                        aria-hidden="true">&rarr;</span></a>
            </div>
        </nav>
        <el-dialog>
            <dialog id="mobile-menu" class="backdrop:bg-transparent lg:hidden">
                <div tabindex="0" class="fixed inset-0 focus:outline-none">
                    <el-dialog-panel
                        class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
                        <div class="flex items-center justify-between">
                            <a href="#" class="-m-1.5 p-1.5">
                                <span class="text-xl font-bold text-[#FF750F]">DaleHurley.com</span>
                            </a>
                            <button type="button" command="close" commandfor="mobile-menu"
                                class="-m-2.5 rounded-md p-2.5 text-gray-700">
                                <span class="sr-only">Close menu</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    data-slot="icon" aria-hidden="true" class="size-6">
                                    <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-6 flow-root">
                            <div class="-my-6 divide-y divide-gray-500/10">
                                <div class="space-y-2 py-6">
                                    <a href="#track-record"
                                        class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Track
                                        Record</a>
                                    <a href="#ai-solutions"
                                        class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">AI
                                        Solutions</a>
                                    <a href="#projects"
                                        class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Projects</a>
                                    <a href="#contact"
                                        class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Contact</a>
                                </div>
                                <div class="py-6">
                                    <a href="#contact"
                                        class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-[#FF750F] hover:bg-gray-50">Let's
                                        Connect</a>
                                </div>
                            </div>
                        </div>
                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>
    </header>

    <!-- Hero Section -->
    <div class="relative isolate overflow-hidden bg-gradient-to-b from-[#FF750F]/20 to-white pt-14">
        <div class="mx-auto max-w-7xl px-6 py-32 sm:py-40 lg:px-8">
            <div
                class="mx-auto max-w-3xl lg:mx-0 lg:grid lg:max-w-none lg:grid-cols-2 lg:gap-x-16 lg:gap-y-8 xl:grid-cols-1 xl:grid-rows-1 xl:gap-x-8">
                <div class="lg:col-span-2 xl:col-auto">
                    <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-balance text-gray-900 sm:text-6xl">
                        Building the AI future
                    </h1>
                    <p class="mt-6 text-xl font-semibold text-[#4B0600]">
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
                        class="h-full w-full object-cover object-top" />
                </div>
                <div class="mt-10 lg:mt-0 xl:col-end-1 xl:row-start-1">
                    <div class="mt-10 flex items-center gap-x-6">
                        <a href="#contact"
                            class="rounded-md bg-[#FF750F] px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-[#E5670D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF750F]">
                            Get in Touch
                        </a>
                        <a href="#track-record" class="text-base/6 font-semibold text-gray-900">
                            View Track Record <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Track Record Section -->
    <section id="track-record" class="py-24 sm:py-32 bg-[#FDFDFC]">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Proven Track Record</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Building successful fintech companies and AI solutions
                </p>
            </div>
            <div class="mt-16 max-w-2xl mx-auto space-y-20">
                <!-- LEAP Legal Software -->
                <x-track-record-item year="2024-Present" title="LEAP Legal Software"
                    role="AI Practice Management Engineering Lead"
                    description="Created no-code AI agentic platform for legal firms, transforming how legal professionals manage cases and automate workflows."
                    image="leap-icon.png" />

                <!-- Avenue Bank -->
                <x-track-record-item year="2018-2024" title="Avenue Bank" role="Co-founder & Executive Leader"
                    description="Secured $77M+ funding, obtained full banking licence in 2024, and implemented 24-hour processing systems that revolutionised customer experience."
                    image="avenue-bank-icon.png" />

                <!-- CreditorWatch -->
                <x-track-record-item year="2010-2018" title="CreditorWatch" role="CTO & Innovation Director"
                    description="Led startup to successful exit, pioneered risk score innovation, and implemented user-centric design principles that transformed credit risk assessment."
                    image="creditorwatch-icon.png" />
            </div>
        </div>
    </section>

    <!-- AI Solutions Section -->
    <section id="ai-solutions" class="py-24 sm:py-32 bg-gradient-to-br from-[#FF750F]/10 to-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">AI-Powered Solutions</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600">
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
    <section id="projects" class="py-24 sm:py-32 bg-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Current Projects</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Innovative solutions transforming industries through AI
                </p>
            </div>
            <div
                class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-4">
                @foreach ([
        ['name' => 'DocCheetah', 'desc' => 'AI-powered document processing and analysis', 'url' => 'https://doccheetah.com/', 'image' => 'doc-cheetah-logo.png'],
        ['name' => 'Spotfillr', 'desc' => 'Intelligent parking space optimisation', 'url' => 'https://spotfillr.com/', 'image' => 'spotfill-logo.png'],
        ['name' => 'Full.CX', 'desc' => 'Customer experience enhancement platform', 'url' => 'https://full.cx/', 'image' => 'full-cx-logo.png'],
        ['name' => 'Custom Homework Maker', 'desc' => 'AI-generated personalised learning materials', 'url' => 'https://customhomeworkmaker.com/', 'image' => 'custom-homework-maker-logo.png'],
        ['name' => '1 to 5 App', 'desc' => 'Simplified rating system for businesses', 'url' => 'https://www.1to5app.com/', 'image' => '1-to-5-app-logo.png'],
        ['name' => 'RapidReportCard', 'desc' => 'Automated educational assessment tool', 'url' => 'https://rapidreportcard.com/', 'image' => 'rapid-report-card-logo.png'],
        ['name' => 'Risks.io', 'desc' => 'Enterprise risk management platform', 'url' => 'https://www.risks.io/', 'image' => 'risks-io-logo.png'],
        ['name' => 'SpeedBrain', 'desc' => 'Cognitive enhancement training system', 'url' => 'https://speedbrain.app/', 'image' => 'speed-brain-app-logo.png'],
        ['name' => 'Claude 3 API PHP Package', 'desc' => 'Official SDK for Anthropic\'s Claude 3', 'url' => 'https://github.com/claude-php/claude-3-api', 'image' => 'php-claude-logo.png'],
    ] as $project)
                    <x-project-item :name="$project['name']" :description="$project['desc']" :url="$project['url']" :image="$project['image']" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 sm:py-32 bg-[#4B0600]/5">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Let's Connect</h2>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    Interested in AI solutions for your business? Reach out to discuss how we can work together.
                </p>
            </div>
            <div class="mx-auto mt-16 max-w-xl sm:mt-20">
                <form action="#" method="POST" class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-semibold leading-6 text-gray-900">Name</label>
                        <div class="mt-2.5">
                            <input type="text" name="name" id="name" autocomplete="name"
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email"
                            class="block text-sm font-semibold leading-6 text-gray-900">Email</label>
                        <div class="mt-2.5">
                            <input type="email" name="email" id="email" autocomplete="email"
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message"
                            class="block text-sm font-semibold leading-6 text-gray-900">Message</label>
                        <div class="mt-2.5">
                            <textarea name="message" id="message" rows="4"
                                class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#FF750F] sm:text-sm sm:leading-6"></textarea>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="block w-full rounded-md bg-[#FF750F] px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-[#E5670D] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF750F]">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-6 pt-16 pb-8 sm:pt-24 lg:px-8 lg:pt-32">
            <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                <div class="space-y-8">
                    <div>
                        <span class="text-2xl font-bold text-[#FF750F]">DaleHurley.com</span>
                    </div>
                    <p class="text-sm/6 text-balance text-gray-300">
                        Building the future with AI and banking innovation
                    </p>
                    <div class="flex gap-x-6">
                        <x-social-link href="#" name="Twitter"
                            svgPath="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        <x-social-link href="#" name="GitHub"
                            svgPath="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                            clip-rule="evenodd" />
                        <x-social-link href="#" name="LinkedIn"
                            svgPath="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                    </div>
                </div>
                <div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
                    <div class="md:grid md:grid-cols-2 md:gap-8">
                        <x-footer-section title="Solutions" :links="[
                            ['href' => '#ai-solutions', 'text' => 'AI Prototyping'],
                            ['href' => '#ai-solutions', 'text' => 'Process Automation'],
                            ['href' => '#ai-solutions', 'text' => 'Strategic Innovation'],
                            ['href' => '#projects', 'text' => 'Fintech Solutions'],
                        ]" />
                        <div class="mt-10 md:mt-0">
                            <x-footer-section title="Connect" :links="[
                                ['href' => '#contact', 'text' => 'Contact Form'],
                                ['href' => '#', 'text' => 'LinkedIn'],
                                ['href' => '#', 'text' => 'Twitter'],
                            ]" />
                        </div>
                    </div>
                    <div class="md:grid md:grid-cols-2 md:gap-8">
                        <x-footer-section title="About" :links="[
                            ['href' => '#track-record', 'text' => 'Track Record'],
                            ['href' => '#projects', 'text' => 'Projects'],
                            ['href' => '#', 'text' => 'Blog'],
                        ]" />
                        <div class="mt-10 md:mt-0">
                            <x-footer-section title="Legal" :links="[
                                ['href' => '#', 'text' => 'Privacy Policy'],
                                ['href' => '#', 'text' => 'Terms of Service'],
                            ]" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-16 border-t border-white/10 pt-8">
                <p class="text-sm/6 text-gray-300 text-center">&copy; {{ date('Y') }} Dale Hurley. All rights
                    reserved. Made with ❤️ in Australia 🐨 🦘.</p>
            </div>
        </div>
    </footer>
</body>

</html>
