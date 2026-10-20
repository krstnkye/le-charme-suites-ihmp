@props(['title', 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'owner-card overflow-hidden rounded-xl']) }}>
    <div class="border-b border-line bg-surface-low p-5">
        <h3 class="font-serif text-2xl font-bold text-primary">{{ $title }}</h3>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="p-5">
        {{ $slot }}
    </div>
</section>
