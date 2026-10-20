@extends('layouts.app')

@section('title', 'Dashboard - Hotel Management System')
@section('page_title', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            'Total rooms' => $totalRooms,
            'Available rooms' => $availableRooms,
            'Occupied rooms' => $occupiedRooms,
            'Active reservations' => $activeReservations,
            'Current guests' => $currentGuests,
        ] as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold">Recent Transactions</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Guest</th>
                        <th class="px-4 py-3">Room</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $transaction->transaction_date?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</td>
                            <td class="px-4 py-3">{{ $transaction->guest?->full_name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $transaction->room?->room_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($transaction->status) }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-6 text-slate-500" colspan="5">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
