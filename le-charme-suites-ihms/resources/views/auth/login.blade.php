<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Charme Suites - IHMS Terminal Authentication</title>

    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        'secondary-fixed': '#ffdeaa',
                        'secondary-fixed-dim': '#e6c185',
                        'on-surface-variant': '#44474d',
                        'on-surface': '#1c1c18',
                        'outline-variant': '#c4c6cd',
                        'surface-container-lowest': '#ffffff',
                        'surface-container': '#f0eee8',
                        surface: '#fcf9f3',
                        'surface-variant': '#e5e2dc',
                        'secondary-container': '#fdd79a',
                        secondary: '#755a28',
                        error: '#ba1a1a',
                        'error-container': '#ffdad6',
                        'on-error-container': '#93000a',
                    },
                    fontFamily: {
                        'body-md': ['Manrope', 'sans-serif'],
                        'label-md': ['Manrope', 'sans-serif'],
                        'label-sm': ['Manrope', 'sans-serif'],
                        'headline-md': ['Noto Serif', 'serif'],
                        'title-md': ['Manrope', 'sans-serif'],
                        'body-sm': ['Manrope', 'sans-serif'],
                    },
                    fontSize: {
                        'body-sm': ['12px', { lineHeight: '18px', fontWeight: '400' }],
                        'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.04em', fontWeight: '600' }],
                        'label-sm': ['11px', { lineHeight: '14px', letterSpacing: '0.06em', fontWeight: '700' }],
                        'headline-md': ['24px', { lineHeight: '32px', fontWeight: '500' }],
                        'title-md': ['16px', { lineHeight: '24px', fontWeight: '600' }],
                        'body-md': ['14px', { lineHeight: '22px', fontWeight: '400' }],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }

        .role-card.is-selected {
            background: #01162d;
            border-color: #755a28;
            color: #ffffff;
            box-shadow: 0 0 0 2px rgba(117, 90, 40, 0.28), 0 12px 24px -18px rgba(1, 22, 45, 0.7);
        }

        .role-card.is-selected .material-symbols-outlined,
        .role-card.is-selected .selected-check {
            color: #ffdeaa;
        }

        .role-card.is-selected .selected-check {
            opacity: 1;
            transform: scale(1);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-primary font-body-md text-on-surface antialiased selection:bg-secondary-container">
    <main class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-primary px-4">
        <div class="pointer-events-none absolute inset-0 opacity-10 bg-[radial-gradient(#ffdeaa_1px,transparent_1px)] [background-size:24px_24px]"></div>

        <section class="relative my-8 w-full max-w-2xl rounded-xl border border-secondary-fixed-dim/30 bg-surface-container-lowest p-8 shadow-2xl sm:p-10">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 max-w-sm overflow-hidden rounded-lg bg-white px-5 py-3">
                    @include('partials.brand-logo', ['class' => 'h-auto w-full object-contain'])
                </div>
                <p class="mt-1 font-label-md text-label-md uppercase tracking-widest text-secondary">Subic Bay Philippines</p>
                <div class="mx-auto my-3 h-0.5 w-12 bg-secondary opacity-60"></div>
                <p class="font-body-sm text-body-sm font-medium text-on-surface-variant">
                    Staff Log In Console - Integrated Hotel Management System (IHMS) - IPT101
                </p>
            </div>

            <div class="mb-6">
                <div class="mb-2 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                    <span class="block font-label-sm text-label-sm uppercase text-on-surface-variant">Hospitality Roles</span>
                    <span class="inline-flex w-fit rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-primary">
                        Selected Role: <span id="selected-role-label">None</span>
                    </span>
                </div>
                <div class="grid gap-2 sm:grid-cols-5">
                    @foreach (config('hotel.staff_roles') as $value => $role)
                        <button class="role-card relative cursor-pointer rounded-lg border border-surface-variant bg-surface-container px-3 py-3 text-center text-xs font-bold text-primary transition hover:border-secondary hover:bg-secondary-container {{ old('role') === $value ? 'is-selected' : '' }}" data-label="{{ $role['label'] }}" data-value="{{ $value }}" type="button" aria-pressed="{{ old('role') === $value ? 'true' : 'false' }}">
                            <span class="selected-check material-symbols-outlined absolute right-2 top-2 text-sm opacity-0 transition">check_circle</span>
                            <span class="material-symbols-outlined mb-1 block text-lg">{{ $role['icon'] }}</span>
                            <span>{{ $role['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-error/30 bg-error-container p-3 text-xs text-on-error-container">
                    <span class="material-symbols-outlined mt-0.5 text-[18px] text-error">error</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 flex items-start gap-2 rounded-lg border border-emerald-300 bg-emerald-50 p-3 text-xs text-emerald-900">
                    <span class="material-symbols-outlined mt-0.5 text-[18px] text-emerald-700">check_circle</span>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <input id="selected-role" name="role" type="hidden" value="{{ old('role') }}">

                <div>
                    <label class="mb-1 block font-label-md text-label-md font-semibold text-on-surface" for="email">Work Email Address</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                            <span class="material-symbols-outlined text-lg">badge</span>
                        </span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="sample@lecharmehotel.com"
                            autocomplete="email"
                            class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-3 font-body-md text-body-md text-on-surface focus:border-secondary focus:ring-1 focus:ring-secondary"
                            required
                            autofocus
                        >
                    </div>
                    <p class="mt-1.5 text-xs text-on-surface-variant">Only <strong>@lecharmehotel.com</strong> work emails can sign in.</p>
                </div>

                <div>
                    <label class="mb-1 block font-label-md text-label-md font-semibold text-on-surface" for="password">Security Passcode / Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                            <span class="material-symbols-outlined text-lg">lock</span>
                        </span>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Enter password"
                            class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-10 font-body-md text-body-md text-on-surface focus:border-secondary focus:ring-1 focus:ring-secondary"
                            required
                        >
                        <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant hover:text-primary" id="toggle-password" type="button">
                            <span class="material-symbols-outlined text-lg" id="password-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 text-xs text-on-surface-variant">
                    <label class="flex cursor-pointer items-center gap-1.5">
                        <input name="remember" class="rounded border-outline-variant text-primary focus:ring-secondary" type="checkbox" checked>
                        <span>Remember me</span>
                    </label>
                    <a class="font-semibold text-secondary hover:underline" href="#" onclick="alert('Please contact the system administrator to reset your password.'); return false;">Forgot password?</a>
                </div>

                <button class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-secondary/20 bg-primary py-3 font-title-md text-title-md text-white shadow-md transition hover:bg-primary-container" type="submit">
                    <span>Sign In to IHMS</span>
                    <span class="material-symbols-outlined text-secondary-fixed">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 border-t border-surface-variant pt-4 text-center">
                <p class="text-xs text-on-surface-variant">
                    Need a staff account?
                    <a class="font-semibold text-secondary hover:underline" href="{{ route('register') }}">Create one</a>
                </p>
                <p class="mt-2 text-xs font-semibold text-on-surface-variant">{{ config('hotel.address') }}</p>
                <p class="mt-1 text-[11px] text-outline">{{ config('hotel.phone') }} · {{ config('hotel.email') }}</p>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('.role-card').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('selected-role-label').textContent = button.dataset.label;
                document.getElementById('selected-role').value = button.dataset.value;
                document.querySelectorAll('.role-card').forEach((item) => {
                    item.classList.remove('is-selected');
                    item.setAttribute('aria-pressed', 'false');
                });
                button.classList.add('is-selected');
                button.setAttribute('aria-pressed', 'true');
            });
        });

        const savedRoleButton = document.querySelector('.role-card.is-selected');
        if (savedRoleButton) {
            document.getElementById('selected-role-label').textContent = savedRoleButton.dataset.label;
        }

        document.getElementById('toggle-password').addEventListener('click', () => {
            const password = document.getElementById('password');
            const icon = document.getElementById('password-icon');
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            icon.textContent = isHidden ? 'visibility_off' : 'visibility';
        });
    </script>
</body>
</html>
