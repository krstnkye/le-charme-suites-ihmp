@extends('layouts.app')

@section('title', 'Edit Reservation - Le Charmé Suites IHMS')
@section('section_label', 'Front Desk / Reservations')
@section('page_title', 'Edit Reservation')

@section('content')
    <section class="mb-6 flex flex-col justify-between gap-4 border-b border-[#e5e2dc] pb-5 md:flex-row md:items-end">
        <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-[#755a28]">Reservation LC-{{ str_pad($reservation->id, 5, '0', STR_PAD_LEFT) }}</p><h2 class="ihms-serif mt-2 text-3xl font-semibold text-[#01162d] sm:text-4xl">Edit Reservation &amp; Stay Assignment</h2><p class="mt-2 text-sm text-slate-600">Update guest, room, dates, party size, and booking status from one workspace.</p></div>
        <a href="{{ route('reservations.show', $reservation) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#755a28] px-4 py-2.5 text-sm font-bold text-[#755a28] hover:bg-[#fff8e8]"><span class="material-symbols-outlined text-lg">receipt_long</span>View Reservation</a>
    </section>

    <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @method('PUT')
        <section class="ihms-card rounded-xl p-5 sm:p-7">
            <div class="mb-6 flex items-center gap-3 border-b border-[#e5e2dc] pb-5"><span class="flex h-11 w-11 items-center justify-center rounded-lg bg-[#01162d] text-white"><span class="material-symbols-outlined">edit_calendar</span></span><div><h3 class="font-bold text-[#01162d]">Booking Details</h3><p class="text-xs text-slate-500">Verify room availability before saving date changes.</p></div></div>
            @include('reservations.form')
        </section>
        <aside class="space-y-4">
            <section class="ihms-card rounded-xl p-5"><label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Reservation status</label><select id="status" name="status" class="mt-3 w-full rounded-lg border border-[#c4c6cd] bg-white px-4 py-3 text-sm font-semibold text-[#01162d] focus:border-[#755a28] focus:ring-[#755a28]" required>@foreach (['reserved', 'checked_in', 'completed', 'cancelled'] as $option)<option value="{{ $option }}" @selected(old('status', $reservation->status) === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>@endforeach</select><p class="mt-3 text-xs leading-5 text-slate-500">Use status carefully: it affects front-desk queues and room operations.</p></section>
            <section class="rounded-xl bg-[#01162d] p-5 text-white"><span class="material-symbols-outlined text-[#fdd79a]">sync</span><h3 class="ihms-serif mt-3 text-xl font-semibold">Shared Operations</h3><p class="mt-2 text-sm leading-6 text-slate-300">Saved changes are reflected in the reservation, room, owner, and billing views that use this record.</p></section>
            <div class="flex flex-col gap-3"><button class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#755a28] px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#8a6a32]"><span class="material-symbols-outlined text-lg">save</span>Save Reservation</button><a href="{{ route('reservations.show', $reservation) }}" class="inline-flex items-center justify-center rounded-lg border border-[#c4c6cd] bg-white px-5 py-3 text-sm font-bold text-[#01162d] hover:bg-[#f6f3ed]">Cancel</a></div>
        </aside>
    </form>
@endsection

@push('scripts')
<script>
    (() => {
        const guest = document.getElementById('guest_id');
        const room = document.getElementById('room_id');
        const guestCount = document.getElementById('number_of_guests');
        const capacityNote = document.getElementById('capacity-note');
        const existingFields = document.getElementById('existing-guest-fields');
        const manualFields = document.getElementById('manual-guest-fields');
        const manualFirstName = document.getElementById('manual_guest_first_name');
        const manualLastName = document.getElementById('manual_guest_last_name');
        const updateCapacity = () => {
            const capacity = room.options[room.selectedIndex]?.dataset.capacity;
            capacityNote.textContent = capacity ? `Capacity: ${capacity} guest${capacity === '1' ? '' : 's'}` : '';
            if (capacity) guestCount.max = capacity;
        };
        const updateGuestMode = mode => {
            const manual = mode === 'manual';
            existingFields.classList.toggle('hidden', manual);
            manualFields.classList.toggle('hidden', !manual);
            guest.disabled = manual;
            manualFirstName.required = manual;
            manualLastName.required = manual;
            if (manual) guest.value = '';
        };
        document.querySelectorAll('input[name="guest_mode"]').forEach(input => input.addEventListener('change', event => updateGuestMode(event.target.value)));
        room.addEventListener('change', updateCapacity);
        updateGuestMode(document.querySelector('input[name="guest_mode"]:checked')?.value || 'existing');
        updateCapacity();
    })();
</script>
@endpush
