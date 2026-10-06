@extends('layouts.app')

@section('page_title', 'Edit Guest')

@section('content')
    <form method="POST" action="{{ route('guests.update', $guest) }}" class="max-w-3xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('guests.form')
        <button class="mt-6 rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">Update Guest</button>
    </form>
@endsection
