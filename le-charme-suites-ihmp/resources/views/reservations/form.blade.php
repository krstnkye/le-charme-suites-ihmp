@php($useExistingGuest = old('guest_mode', 'existing') === 'existing')
<div class="mb-5 rounded-xl border border-[#e5e2dc] bg-[#f6f3ed] p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#01162d]">Guest Information</p>
            <p class="mt-1 text-xs text-slate-500">Use a saved guest profile or enter a walk-in guest manually.</p>
        </div>
        <span class="material-symbols-outlined text-[#755a28]">person_search</span>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-[#e6c185] bg-white p-3 has-[:checked]:ring-2 has-[:checked]:ring-[#755a28]/20">
            <input type="radio" name="guest_mode" value="existing" class="mt-0.5 text-[#755a28] focus:ring-[#755a28]" @checked($useExistingGuest)>
            <span><strong class="block text-sm text-[#01162d]">Existing guest</strong><small class="text-xs text-slate-500">Choose from Guest Management</small></span>
        </label>
        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-[#e5e2dc] bg-white p-3 has-[:checked]:border-[#e6c185] has-[:checked]:ring-2 has-[:checked]:ring-[#755a28]/20">
            <input type="radio" name="guest_mode" value="manual" class="mt-0.5 text-[#755a28] focus:ring-[#755a28]" @checked(!$useExistingGuest)>
            <span><strong class="block text-sm text-[#01162d]">Enter manually</strong><small class="text-xs text-slate-500">For new or walk-in guests</small></span>
        </label>
    </div>
</div>
<div id="existing-guest-fields" class="mb-5 {{ $useExistingGuest ? '' : 'hidden' }}">
    <label for="guest_id" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Saved guest profile <span class="font-normal normal-case tracking-normal text-slate-400">(optional when entering manually)</span></label>
    <select id="guest_id" name="guest_id" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20">
        <option value="">Select guest</option>
        @forelse ($guests as $guest)
            <option value="{{ $guest->id }}" @selected((int) old('guest_id', $reservation->guest_id ?? 0) === $guest->id)>{{ $guest->full_name }}{{ $guest->email ? ' - ' . $guest->email : '' }}</option>
        @empty
            <option value="" disabled>No guest records available</option>
        @endforelse
    </select>
    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
        @if ($guests->isEmpty())<span class="text-amber-700">No saved guests yet.</span>@endif
        <a href="{{ route('guests.create') }}" class="inline-flex items-center gap-1 font-semibold text-[#755a28] hover:underline"><span class="material-symbols-outlined text-sm">person_add</span>Add guest profile</a>
    </div>
</div>
<div id="manual-guest-fields" class="mb-5 rounded-xl border border-[#e6c185]/60 bg-[#fffaf0] p-4 {{ $useExistingGuest ? 'hidden' : '' }}">
    <div class="mb-4 flex items-center gap-2"><span class="material-symbols-outlined text-[#755a28]">edit_note</span><div><p class="text-sm font-bold text-[#01162d]">Manual guest details</p><p class="text-xs text-slate-500">A guest profile will be created automatically when you save.</p></div></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label for="manual_guest_first_name" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">First name <span class="text-red-600">*</span></label><input id="manual_guest_first_name" name="manual_guest_first_name" value="{{ old('manual_guest_first_name') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></div>
        <div><label for="manual_guest_last_name" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Last name <span class="text-red-600">*</span></label><input id="manual_guest_last_name" name="manual_guest_last_name" value="{{ old('manual_guest_last_name') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></div>
        <div><label for="manual_guest_email" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Email address</label><input id="manual_guest_email" name="manual_guest_email" type="email" value="{{ old('manual_guest_email') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></div>
        <div><label for="manual_guest_phone" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone number</label><input id="manual_guest_phone" name="manual_guest_phone" type="tel" value="{{ old('manual_guest_phone') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20"></div>
        <div class="sm:col-span-2"><label for="manual_guest_address" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Address</label><textarea id="manual_guest_address" name="manual_guest_address" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20">{{ old('manual_guest_address') }}</textarea></div>
    </div>
</div>
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <div class="rounded-lg border border-dashed border-[#e6c185] bg-[#fffaf0] p-3 text-xs text-slate-600 md:col-span-2"><span class="material-symbols-outlined mr-1 text-sm align-middle text-[#755a28]">info</span>Guest selection is optional when manual guest details are provided above.</div>
    </div>
    <div>
        <label for="room_id" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Room</label>
        <select id="room_id" name="room_id" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" required>
            <option value="">Select available room</option>
            @forelse ($rooms as $room)
                <option value="{{ $room->id }}" data-rate="{{ $room->rate }}" data-capacity="{{ $room->capacity }}" @selected((int) old('room_id', $reservation->room_id ?? 0) === $room->id)>Room {{ $room->room_number }} - {{ $room->room_type }} - PHP {{ number_format($room->rate, 2) }} / night</option>
            @empty
                <option value="" disabled>No available rooms</option>
            @endforelse
        </select>
        @if ($rooms->isEmpty())
            <p class="mt-2 text-xs text-amber-700">There are no available rooms right now. Check Room Status or ask an administrator to update room availability.</p>
        @else
            <p class="mt-2 text-xs text-slate-500">Only available rooms are shown.</p>
        @endif
    </div>
    <div>
        <label for="check_in_date" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Check-in date</label>
        <input id="check_in_date" name="check_in_date" type="date" value="{{ old('check_in_date', isset($reservation) ? $reservation->check_in_date?->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" required>
    </div>
    <div>
        <label for="check_out_date" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Check-out date</label>
        <input id="check_out_date" name="check_out_date" type="date" value="{{ old('check_out_date', isset($reservation) ? $reservation->check_out_date?->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" required>
    </div>
    <div>
        <label for="number_of_guests" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Number of guests</label>
        <input id="number_of_guests" name="number_of_guests" type="number" min="1" value="{{ old('number_of_guests', $reservation->number_of_guests ?? 1) }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20" required>
        <p id="capacity-note" class="mt-1 text-xs text-slate-500"></p>
    </div>
</div>
<div class="mt-4">
    <label for="notes" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">Notes</label>
    <textarea id="notes" name="notes" rows="4" placeholder="Add arrival details or special requests" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#755a28] focus:ring-2 focus:ring-[#755a28]/20">{{ old('notes', $reservation->notes ?? '') }}</textarea>
</div>
