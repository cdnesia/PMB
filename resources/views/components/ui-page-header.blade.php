@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-center justify-between gap-4']) }}>
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @if (isset($action) && $action->isNotEmpty())
        <div class="flex items-center gap-2">
            {{ $action }}
        </div>
    @endif
</div>
