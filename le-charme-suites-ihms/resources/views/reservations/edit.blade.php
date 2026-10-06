@extends('layouts.app')

@section('page_title', 'Edit Reservation')

@section('content')
    <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="max-w-3xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('reservations.form')
        <div class="mt-4">
            <label class="block text-sm font-medium">Status</label>
            <select name="status" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
                @foreach (['reserved', 'checked_in', 'completed', 'cancelled'] as $option)
                    <option value="{{ $option }}" @selected(old('status', $reservation->status) === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <button class="mt-6 rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">Update Reservation</button>
    </form>
@endsection
