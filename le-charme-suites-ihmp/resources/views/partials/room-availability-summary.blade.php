@php
    $summary = $roomAvailability;
    $base = max($summary['encoded'], 1);
    $tiles = [
        ['key' => 'available', 'label' => 'Available', 'hint' => 'Ready to sell now', 'icon' => 'check_circle', 'bar' => 'bg-emerald-600', 'text' => 'text-emerald-700'],
        ['key' => 'reserved', 'label' => 'Reserved', 'hint' => 'Confirmed arrival due today', 'icon' => 'event_available', 'bar' => 'bg-amber-500', 'text' => 'text-amber-700'],
        ['key' => 'occupied', 'label' => 'Occupied', 'hint' => 'Guest checked in', 'icon' => 'king_bed', 'bar' => 'bg-[#01162d]', 'text' => 'text-[#01162d]'],
        ['key' => 'out_of_service', 'label' => 'Cleaning / Maintenance', 'hint' => 'Not sellable yet', 'icon' => 'cleaning_services', 'bar' => 'bg-slate-400', 'text' => 'text-slate-600'],
    ];
@endphp

<section data-room-availability-summary class="overflow-hidden rounded-xl border border-[#e5e2dc] bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-[#e5e2dc] p-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#755a28]">Room availability today</p>
            <h2 class="mt-1 font-serif text-2xl font-bold text-[#01162d]">{{ $summary['available'] }} of {{ $summary['property_total'] }} rooms available</h2>
            @if ($summary['encoded'] !== $summary['property_total'])
                <p class="mt-1 text-xs font-semibold text-amber-700">{{ $summary['encoded'] }} rooms are encoded in the system; the hotel has {{ $summary['property_total'] }}.</p>
            @endif
        </div>
        <div class="rounded-lg bg-[#f6f3ed] px-4 py-3 text-right">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total hotel rooms</p>
            <p class="font-serif text-3xl font-bold text-[#01162d]">{{ $summary['property_total'] }}</p>
        </div>
    </div>

    <div class="flex h-2.5 w-full" role="img" aria-label="Room availability breakdown">
        @foreach ($tiles as $tile)
            @if ($summary[$tile['key']] > 0)
                <span class="{{ $tile['bar'] }}" style="width: {{ round($summary[$tile['key']] / $base * 100, 2) }}%"></span>
            @endif
        @endforeach
    </div>

    <div class="grid grid-cols-2 divide-[#e5e2dc] lg:grid-cols-4 lg:divide-x">
        @foreach ($tiles as $tile)
            <div class="p-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg {{ $tile['text'] }}">{{ $tile['icon'] }}</span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $tile['label'] }}</span>
                </div>
                <p class="mt-1 font-serif text-3xl font-bold {{ $tile['text'] }}">{{ $summary[$tile['key']] }}</p>
                <p class="text-[11px] text-slate-500">{{ $tile['hint'] }} · {{ round($summary[$tile['key']] / $base * 100) }}%</p>
            </div>
        @endforeach
    </div>
</section>
