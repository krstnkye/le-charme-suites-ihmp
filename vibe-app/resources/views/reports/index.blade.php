@extends('layouts.app')

@section('page_title', 'Records and Reports')

@section('content')
    <div class="grid gap-4 md:grid-cols-2">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold">Room Status Summary</h2>
            <dl class="mt-4 space-y-2 text-sm">
                @forelse ($roomStatusCounts as $status => $total)
                    <div class="flex justify-between"><dt>{{ ucfirst($status) }}</dt><dd class="font-semibold">{{ $total }}</dd></div>
                @empty
                    <p class="text-slate-500">No room data yet.</p>
                @endforelse
            </dl>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold">Reservation Status Summary</h2>
            <dl class="mt-4 space-y-2 text-sm">
                @forelse ($reservationStatusCounts as $status => $total)
                    <div class="flex justify-between"><dt>{{ ucfirst($status) }}</dt><dd class="font-semibold">{{ $total }}</dd></div>
                @empty
                    <p class="text-slate-500">No reservation data yet.</p>
                @endforelse
            </dl>
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4">
            <h2 class="font-bold">Transaction Records</h2>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Guest</th>
                    <th class="px-4 py-3">Room</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $transaction->transaction_date?->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($transaction->transaction_type)) }}</td>
                        <td class="px-4 py-3">{{ $transaction->guest?->full_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ $transaction->room?->room_number ?? 'N/A' }}</td>
                        <td class="px-4 py-3">PHP {{ number_format($transaction->amount, 2) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($transaction->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-slate-500">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
