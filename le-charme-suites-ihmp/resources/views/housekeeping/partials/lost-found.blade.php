@php
    $statusFilter = request('status', 'in_custody');
    $categoryFilter = request('category', 'all');
    $visibleItems = $lostFoundItems
        ->when($statusFilter !== 'all', fn ($items) => $items->where('status', $statusFilter))
        ->when($categoryFilter !== 'all', fn ($items) => $items->where('category', $categoryFilter));
    $statusTone = ['in_custody' => 'bg-amber-500 text-white', 'claimed' => 'bg-emerald-700 text-white', 'disposed' => 'bg-slate-500 text-white'];
    $filterUrl = fn (array $changes) => route('housekeeping.page', ['page' => 'lost-found', 'status' => $statusFilter, 'category' => $categoryFilter, ...$changes]);
@endphp

<section class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-4 xl:col-span-8">
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <h3 class="font-serif text-3xl font-bold text-primary">Lost &amp; Found Storage and Item History</h3>
            <p class="mt-1 text-sm text-slate-600">Every item found on the property, where it is stored, and who it was released to.</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold">
                @foreach (['in_custody' => 'In Custody', 'claimed' => 'Claimed & Released', 'disposed' => 'Donated / Disposed', 'all' => 'All'] as $value => $label)
                    <a href="{{ $filterUrl(['status' => $value]) }}" @class(['rounded-full border px-3 py-1.5', 'border-primary bg-primary text-white' => $statusFilter === $value, 'border-line bg-white text-slate-600 hover:text-primary' => $statusFilter !== $value])>{{ $label }} ({{ $value === 'all' ? $lostFoundItems->count() : $lostFoundItems->where('status', $value)->count() }})</a>
                @endforeach
            </div>
            <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                @foreach (['all' => 'All categories', ...\App\Models\LostFoundItem::CATEGORIES] as $value => $label)
                    <a href="{{ $filterUrl(['category' => $value]) }}" @class(['rounded-full border px-3 py-1', 'border-secondary bg-secondary/10 text-secondary' => $categoryFilter === $value, 'border-line text-slate-500 hover:text-primary' => $categoryFilter !== $value])>{{ $label }}</a>
                @endforeach
            </div>
        </div>

        @forelse ($visibleItems as $item)
            <article class="rounded-xl border border-line bg-white p-4 shadow-sm" data-lost-found-item="{{ $item->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold text-slate-500">{{ $item->reference() }}</span>
                            <h4 class="font-serif text-xl font-bold text-primary">{{ $item->item_name }}</h4>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusTone[$item->status] ?? '' }}">{{ \App\Models\LostFoundItem::STATUSES[$item->status] ?? $item->status }}</span>
                            <span class="rounded-full border border-line px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">{{ \App\Models\LostFoundItem::CATEGORIES[$item->category] ?? $item->category }}</span>
                        </div>
                        @if ($item->description)<p class="mt-1 text-sm text-slate-700">{{ $item->description }}</p>@endif
                        <p class="mt-1 text-xs text-slate-500">Found in {{ $item->foundWhere() }} by {{ $item->finder?->name ?? 'staff' }} · {{ $item->found_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }} · Stored at {{ $item->storage_location ?: 'not recorded' }}</p>
                        @if ($item->status !== 'in_custody')
                            <p class="mt-2 rounded-lg bg-[#f6f3ed] px-3 py-2 text-xs text-slate-700">
                                @if ($item->status === 'claimed')
                                    Released to <span class="font-bold">{{ $item->claimed_by_name }}</span> ({{ $item->claimant_id_details }})
                                @else
                                    Donated / disposed
                                @endif
                                by {{ $item->releaser?->name ?? 'staff' }} on {{ $item->released_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}{{ $item->release_notes ? ' - '.$item->release_notes : '' }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($item->status === 'in_custody')
                    <details class="mt-3 border-t border-line pt-3">
                        <summary class="cursor-pointer text-xs font-bold text-secondary">Release this item</summary>
                        <form method="POST" action="{{ route('lost-found-items.update', $item) }}" class="mt-3 grid gap-2 md:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            <label class="text-xs font-bold text-slate-600">Outcome
                                <select name="status" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                                    <option value="claimed">Claimed by owner</option>
                                    <option value="disposed">Donated / disposed (unclaimed)</option>
                                </select>
                            </label>
                            <label class="text-xs font-bold text-slate-600">Claimed by
                                <input name="claimed_by_name" maxlength="120" placeholder="Full name of claimant" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                            </label>
                            <label class="text-xs font-bold text-slate-600">ID presented
                                <input name="claimant_id_details" maxlength="120" placeholder="e.g. Driver's License N01-23-456789" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                            </label>
                            <label class="text-xs font-bold text-slate-600">Notes
                                <input name="release_notes" maxlength="1000" placeholder="Optional" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                            </label>
                            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary/90 md:col-span-2">Save Release</button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-line bg-white p-10 text-center">
                <span class="material-symbols-outlined text-4xl text-slate-300">inventory_2</span>
                <p class="mt-2 font-semibold text-slate-600">No items match this filter.</p>
            </div>
        @endforelse
    </div>

    <aside class="xl:col-span-4">
        <form method="POST" action="{{ route('lost-found-items.store') }}" class="space-y-3 rounded-xl border border-line bg-white p-5 shadow-sm">
            @csrf
            <h3 class="font-serif text-2xl font-bold text-primary">Log a Found Item</h3>
            <label class="block text-xs font-bold text-slate-600">Item
                <input name="item_name" required maxlength="120" value="{{ old('item_name') }}" placeholder="e.g. Black wallet, iPhone charger" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
            </label>
            <label class="block text-xs font-bold text-slate-600">Category
                <select name="category" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                    @foreach (\App\Models\LostFoundItem::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-bold text-slate-600">Found in room
                <select name="room_id" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                    <option value="">Not in a guest room</option>
                    @foreach ($allRooms as $room)
                        <option value="{{ $room->id }}" @selected((int) old('room_id') === $room->id)>Room {{ $room->room_number }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-bold text-slate-600">Or other location
                <input name="found_location" maxlength="120" value="{{ old('found_location') }}" placeholder="e.g. Pool area, lobby" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
            </label>
            <label class="block text-xs font-bold text-slate-600">Description
                <textarea name="description" rows="2" maxlength="1000" placeholder="Color, brand, contents (do not list card numbers)" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">{{ old('description') }}</textarea>
            </label>
            <label class="block text-xs font-bold text-slate-600">Stored at
                <input name="storage_location" required maxlength="120" value="{{ old('storage_location', 'Housekeeping office cabinet') }}" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
            </label>
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary/90"><span class="material-symbols-outlined text-base text-[#ffdeaa]">add_box</span>Log Item</button>
        </form>
    </aside>
</section>
