@props(['lang' => '', 'showLineNumbers' => false, 'filename' => ''])

@php
    $code = trim($slot);
    $lines = explode("\n", $code);
    $lineCount = count($lines);
    $showNumbers = $showLineNumbers || str_contains($attributes->get('class', ''), 'showLineNumbers');
@endphp

<div class="my-6 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 code-block">
    @if ($filename)
        <div
            class="bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 code-block-filename">
            {{ $filename }}
        </div>
    @endif

    <div class="relative group">
        <!-- Copy Button -->
        <button onclick="copyCodeToClipboard(this)" data-code="{{ htmlspecialchars($code) }}"
            class="absolute top-3 right-3 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10 copy-button"
            title="Copy code">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </button>

        <div class="flex bg-gray-900 text-gray-100">
            @if ($showNumbers)
                <div
                    class="flex-shrink-0 px-4 py-3 bg-gray-800 text-gray-500 text-sm font-mono leading-6 select-none border-r border-gray-700 line-numbers">
                    @for ($i = 1; $i <= $lineCount; $i++)
                        <div>{{ $i }}</div>
                    @endfor
                </div>
            @endif

            <div class="flex-1 overflow-x-auto">
                <pre class="p-4 text-sm font-mono leading-6" @if ($showNumbers) data-line-numbers="true" @endif><code class="language-{{ $lang }}" data-lang="{{ $lang }}">{{ $code }}</code></pre>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function copyCodeToClipboard(button) {
            const code = button.getAttribute('data-code');
            navigator.clipboard.writeText(code).then(() => {
                const originalHtml = button.innerHTML;
                button.innerHTML =
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                button.classList.add('copied');
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy code: ', err);
            });
        }
    </script>
@endpush
