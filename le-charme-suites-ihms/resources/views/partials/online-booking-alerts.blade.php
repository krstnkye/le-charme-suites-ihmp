@php
    $latestOnlineBookingId = \App\Models\Reservation::where('status', 'pending')->max('id');
@endphp

<div id="online-booking-alerts" class="pointer-events-none fixed bottom-5 right-5 z-[60] flex w-[min(24rem,calc(100vw-2.5rem))] flex-col gap-3" aria-live="polite"></div>

<script>
    (() => {
        const container = document.getElementById('online-booking-alerts');
        const feedUrl = @json(route('online-booking-requests.feed'));
        const queueUrl = @json(route('reservations.index', ['status' => 'pending']));
        let lastSeenId = @json($latestOnlineBookingId) ?? 0;

        const escapeText = (value) => {
            const span = document.createElement('span');
            span.textContent = value ?? '';
            return span.innerHTML;
        };

        const showAlert = (booking, pendingCount) => {
            const card = document.createElement('div');
            card.className = 'pointer-events-auto rounded-xl border border-[#c5a36a]/60 bg-[#01162d] p-4 text-white shadow-2xl';
            card.setAttribute('role', 'status');
            card.innerHTML = `
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[#e6c185]">notifications_active</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#e6c185]">New online booking · ${escapeText(booking.source)}</p>
                        <p class="mt-1 truncate font-bold">${escapeText(booking.guest)}</p>
                        <p class="text-xs text-slate-300">${escapeText(booking.room_type)} · Arrives ${escapeText(booking.check_in_date)} · ${escapeText(booking.booking_reference)}</p>
                        <div class="mt-3 flex gap-2 text-xs font-bold">
                            <a class="rounded-lg bg-[#c5a36a] px-3 py-1.5 text-[#01162d]" href="${escapeText(booking.url)}">Review booking</a>
                            <a class="rounded-lg border border-white/30 px-3 py-1.5" href="${escapeText(queueUrl)}">Pending queue (${pendingCount})</a>
                        </div>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white" aria-label="Dismiss alert">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>`;
            card.querySelector('button').addEventListener('click', () => card.remove());
            container.prepend(card);
        };

        const poll = async () => {
            try {
                const response = await fetch(feedUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });

                if (!response.ok) {
                    return;
                }

                const feed = await response.json();
                const newBookings = feed.latest.filter((booking) => booking.id > lastSeenId).reverse();

                newBookings.forEach((booking) => showAlert(booking, feed.pending_count));
                lastSeenId = Math.max(lastSeenId, feed.latest_id ?? 0);
            } catch (error) {
                // The front desk keeps working offline; the next poll will try again.
            }
        };

        setInterval(poll, 10000);
    })();
</script>
