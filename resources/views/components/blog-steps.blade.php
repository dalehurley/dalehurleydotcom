@props(['title' => null])

<div class="my-8">
    @if ($title)
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $title }}</h3>
    @endif

    <div class="space-y-6">
        {{ $slot }}
    </div>
</div>

<style>
    /* Custom styles for step numbering */
    .steps-container {
        counter-reset: step-counter;
    }

    .steps-container .step-item {
        counter-increment: step-counter;
        position: relative;
        padding-left: 3rem;
        margin-bottom: 2rem;
    }

    .steps-container .step-item::before {
        content: counter(step-counter);
        position: absolute;
        left: 0;
        top: 0;
        width: 2rem;
        height: 2rem;
        background: #f97316;
        /* orange-500 */
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .steps-container .step-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 2rem;
        bottom: -1rem;
        width: 2px;
        background: #e5e7eb;
        /* gray-200 */
    }

    .dark .steps-container .step-item:not(:last-child)::after {
        background: #374151;
        /* gray-700 */
    }

    .steps-container .step-item h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        /* gray-900 */
    }

    .dark .steps-container .step-item h3 {
        color: #f9fafb;
        /* gray-50 */
    }
</style>
