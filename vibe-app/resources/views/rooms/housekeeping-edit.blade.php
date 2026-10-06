@extends('layouts.housekeeping')

@section('title', 'Update Room Status - Housekeeping')

@section('content')
    <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Housekeeping / Room Status</p><h2 class="mt-1 font-serif text-3xl font-bold">Update Room {{ $room->room_number }}</h2></div>
    <form method="POST" action="{{ route('rooms.update', $room) }}" class="max-w-3xl rounded-xl border border-line bg-white p-6 shadow-sm">@csrf @method('PUT') @include('rooms.form')<div class="mt-6 flex justify-end gap-3"><a href="{{ route('rooms.index') }}" class="rounded-lg border border-line px-5 py-3 text-sm font-semibold">Cancel</a><button class="rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white">Save Room Status</button></div></form>
@endsection
