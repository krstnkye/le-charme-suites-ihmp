@extends('layouts.app')

@section('page_title', 'Edit Room')

@section('content')
    <form method="POST" action="{{ route('rooms.update', $room) }}" class="max-w-3xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('rooms.form')
        <button class="mt-6 rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">Update Room</button>
    </form>
@endsection
