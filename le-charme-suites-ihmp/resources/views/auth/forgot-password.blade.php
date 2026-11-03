<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope'], headline: ['Manrope'] } } } };</script>
</head>
<body class="min-h-screen bg-[#f5f5f2] font-body text-[#161a20] antialiased">
    <main class="lc-auth">
        <figure class="lc-auth-photo" aria-hidden="true">
            <img src="{{ asset('images/rooms/deluxe-twin.jpg') }}" alt="">
            <figcaption><strong>Deluxe Twin</strong>Le Charmé Bldg. 1131 Palm St., Subic Bay Freeport Zone</figcaption>
        </figure>
        <div class="lc-auth-panel">
        <section class="lc-auth-form">
            <div class="text-center">
                @include('partials.brand-logo', ['class' => 'lc-auth-logo'])
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#5b6069]">Account recovery</p>
                <h1 class="mt-2 font-headline text-3xl font-bold text-[#161a20]">Forgot your password?</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Enter your assigned Le Charme work email. We will send a secure, time-limited reset link if it belongs to an account.</p>
            </div>

            @if (session('status'))
                <div class="mt-6 flex items-start gap-2 rounded-lg border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-900">
                    <span class="material-symbols-outlined text-lg text-emerald-700">check_circle</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <span class="material-symbols-outlined text-lg">error</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
                @csrf
                <label class="block text-sm font-bold text-[#161a20]" for="email">Work Email Address
                    <span class="relative mt-1.5 block">
                        <span class="material-symbols-outlined pointer-events-none absolute inset-y-0 left-3 flex items-center text-lg text-[#5b6069]">mail</span>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="sample@lecharmehotel.com" class="w-full rounded-lg border border-slate-300 bg-[#f5f5f2] py-3 pl-10 pr-3 text-sm focus:border-[#17707f] focus:ring-[#17707f]">
                    </span>
                </label>
                <p class="text-xs text-slate-500">Only <strong>@lecharmehotel.com</strong> work emails are accepted.</p>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#161a20] px-4 py-3 text-sm font-bold text-white shadow transition hover:bg-[#2a2f37]">
                    <span class="material-symbols-outlined text-lg text-[#e8e8e4]">outgoing_mail</span>
                    Send reset instructions
                </button>
            </form>

            <div class="mt-6 border-t border-slate-200 pt-5 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#5b6069] hover:underline"><span class="material-symbols-outlined text-lg">arrow_back</span>Back to sign in</a>
            </div>
        </section>
        </div>
    </main>
</body>
</html>
