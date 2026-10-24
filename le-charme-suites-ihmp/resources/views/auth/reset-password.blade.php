<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <script>tailwind.config = { theme: { extend: { fontFamily: { body: ['Manrope'], headline: ['Noto Serif'] } } } };</script>
</head>
<body class="min-h-screen bg-[#01162d] font-body text-[#1c1c18] antialiased">
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-10 bg-[radial-gradient(#ffdeaa_1px,transparent_1px)] [background-size:24px_24px]"></div>
        <section class="relative w-full max-w-lg rounded-xl border border-[#e6c185]/40 bg-white p-7 shadow-2xl sm:p-10">
            <div class="text-center">
                <div class="mx-auto mb-5 max-w-xs rounded-lg bg-white px-5 py-3">@include('partials.brand-logo', ['class' => 'h-auto w-full object-contain'])</div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#755a28]">Secure account recovery</p>
                <h1 class="mt-2 font-headline text-3xl font-bold text-[#01162d]">Create a new password</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Choose a strong password with at least eight characters for your IHMP staff account.</p>
            </div>

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <span class="material-symbols-outlined text-lg">error</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label class="block text-sm font-bold text-[#01162d]" for="email">Work Email Address
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-[#fcf9f3] px-3 py-3 text-sm focus:border-[#755a28] focus:ring-[#755a28]">
                </label>
                <label class="block text-sm font-bold text-[#01162d]" for="password">New Password
                    <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Minimum 8 characters" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-[#fcf9f3] px-3 py-3 text-sm focus:border-[#755a28] focus:ring-[#755a28]">
                </label>
                <label class="block text-sm font-bold text-[#01162d]" for="password_confirmation">Confirm New Password
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-[#fcf9f3] px-3 py-3 text-sm focus:border-[#755a28] focus:ring-[#755a28]">
                </label>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#01162d] px-4 py-3 text-sm font-bold text-white shadow transition hover:bg-[#172b42]">
                    <span class="material-symbols-outlined text-lg text-[#ffdeaa]">lock_reset</span>
                    Reset password
                </button>
            </form>

            <div class="mt-6 border-t border-slate-200 pt-5 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#755a28] hover:underline"><span class="material-symbols-outlined text-lg">arrow_back</span>Back to sign in</a>
            </div>
        </section>
    </main>
    @include('partials.cookie-notice')
</body>
</html>
