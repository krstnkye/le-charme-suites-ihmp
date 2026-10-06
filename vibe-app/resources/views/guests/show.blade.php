@extends('layouts.app')

@section('page_title', $guest->full_name)

@section('content')
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2">
            <p><strong>Email:</strong> {{ $guest->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $guest->phone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $guest->address ?? 'N/A' }}</p>
            <p><strong>ID:</strong> {{ $guest->identification_type }} {{ $guest->identification_number }}</p>
        </div>
        <h2 class="mt-8 text-lg font-bold">Reservations</h2>
        <ul class="mt-3 space-y-2 text-sm">
            @forelse ($guest->reservations as $reservation)
                <li class="rounded-md bg-slate-50 p-3">Room {{ $reservation->room?->room_number }}: {{ $reservation->check_in_date?->format('M d, Y') }} to {{ $reservation->check_out_date?->format('M d, Y') }} - {{ ucfirst($reservation->status) }}</li>
            @empty
                <li class="text-slate-500">No reservations yet.</li>
            @endforelse
        </ul>
    </section>
@endsection
