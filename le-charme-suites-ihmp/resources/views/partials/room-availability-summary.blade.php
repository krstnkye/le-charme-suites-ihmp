@php
    $summary = $roomAvailability;
    $base = max($summary['encoded'], 1);
    // One hue for the rooms that can be sold; everything else steps through neutrals.
    $tiles = [
        ['key' => 'available', 'label' => 'Available', 'hint' => 'Ready to sell now', 'swatch' => 'background:#17707f'],
        ['key' => 'reserved', 'label' => 'Reserved', 'hint' => 'Confirmed arrival due today', 'swatch' => 'background:#9cc7cd'],
        ['key' => 'occupied', 'label' => 'Occupied', 'hint' => 'Guest checked in', 'swatch' => 'background:#3f444c'],
        ['key' => 'out_of_service', 'label' => 'Cleaning / Maintenance', 'hint' => 'Not sellable yet', 'swatch' => 'background:repeating-linear-gradient(135deg,#c9cac4 0 2px,#ebebe7 2px 5px)'],
    ];
@endphp

<section data-room-availability-summary class="rounded-[10px] border border-[#e4e4df] bg-white">
    <div class="flex flex-col gap-1 p-5 pb-4 sm:flex-row sm:items-baseline sm:justify-between">
        <h2 class="text-lg font-semibold text-[#161a20]">{{ $summary['available'] }} of {{ $summary['property_total'] }} rooms available today</h2>
        <p class="text-sm text-[#5b6069]">Total hotel rooms <span class="font-semibold tabular-nums text-[#161a20]">{{ $summary['property_total'] }}</span></p>
    </div>
    @if ($summary['encoded'] !== $summary['property_total'])
        <p class="-mt-2 px-5 pb-3 text-xs font-semibold text-amber-800">{{ $summary['encoded'] }} rooms are encoded in the system; the hotel has {{ $summary['property_total'] }}.</p>
    @endif

    <div class="px-5">
        <div class="flex h-3 w-full gap-[3px] overflow-hidden rounded-full" role="img" aria-label="Room availability breakdown">
            @foreach ($tiles as $tile)
                @if ($summary[$tile['key']] > 0)
                    <span class="h-full first:rounded-l-full last:rounded-r-full" style="{{ $tile['swatch'] }}; width: {{ round($summary[$tile['key']] / $base * 100, 2) }}%"></span>
                @endif
            @endforeach
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-2 border-t border-[#eeeeea] lg:grid-cols-4">
        @foreach ($tiles as $tile)
            <div @class(['px-5 py-4', 'lg:border-l lg:border-[#eeeeea]' => ! $loop->first, 'border-l border-[#eeeeea]' => $loop->iteration === 2 || $loop->iteration === 4, 'border-t border-[#eeeeea] lg:border-t-0' => $loop->iteration > 2])>
                <dt class="flex items-center gap-2 text-sm font-medium text-[#5b6069]">
                    <span class="h-2.5 w-2.5 shrink-0 rounded-sm" style="{{ $tile['swatch'] }}" aria-hidden="true"></span>
                    {{ $tile['label'] }}
                </dt>
                <dd class="mt-1.5 text-[26px] font-semibold leading-8 tracking-tight text-[#161a20] tabular-nums">{{ $summary[$tile['key']] }}</dd>
                <dd class="text-xs text-[#6b7079]">{{ $tile['hint'] }} · {{ round($summary[$tile['key']] / $base * 100) }}%</dd>
            </div>
        @endforeach
    </dl>
</section>
