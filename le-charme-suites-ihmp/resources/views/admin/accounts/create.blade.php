<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#01162d',
                        'primary-container': '#172b42',
                        secondary: '#755a28',
                        'secondary-fixed': '#ffdeaa',
                        'secondary-container': '#fdd79a',
                        background: '#fcf9f3',
                        surface: '#fcf9f3',
                        'surface-container': '#f0eee8',
                        'surface-container-low': '#f6f3ed',
                        'surface-container-lowest': '#ffffff',
                        'surface-container-highest': '#e5e2dc',
                        'surface-variant': '#e5e2dc',
                        outline: '#74777e',
                        'outline-variant': '#c4c6cd',
                        error: '#ba1a1a',
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .nav-shell {
            background-color: #172b42;
            background-image: radial-gradient(rgba(255, 222, 170, 0.14) 1px, transparent 1px);
            background-size: 26px 26px;
        }

        .folio-shadow {
            box-shadow: 0 4px 16px -2px rgba(23, 43, 66, 0.05), 0 1px 3px rgba(197, 163, 106, 0.08);
        }

        .role-card:has(input:checked) {
            background: #01162d;
            border-color: #755a28;
            color: #ffffff;
            box-shadow: 0 0 0 2px rgba(117, 90, 40, 0.28), 0 12px 24px -18px rgba(1, 22, 45, 0.7);
        }

        .role-card:has(input:checked) .material-symbols-outlined,
        .role-card:has(input:checked) .selected-check {
            color: #ffdeaa;
        }

        .role-card:has(input:checked) .selected-check {
            opacity: 1;
            transform: scale(1);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="min-h-screen bg-background font-sans text-primary antialiased">
    @include('admin.partials.sidebar')

    <div class="min-h-screen pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-surface-container-highest bg-surface px-8 shadow-sm">
            <div class="hidden items-center gap-4 text-sm font-bold lg:flex">
                <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.users.index') }}">Users</a>
                <a class="border-b-2 border-secondary pb-1 text-secondary" href="{{ route('admin.accounts.create') }}">Create Account</a>
                <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.roles.index') }}">Roles</a>
                <a class="text-slate-500 transition hover:text-primary" href="{{ route('admin.permissions.index') }}">Permissions</a>
            </div>
            <div class="ml-auto flex items-center gap-4">
                <div class="hidden rounded-full bg-surface-container px-3 py-1 text-xs font-bold text-secondary xl:flex">Subic Bay Central</div>
                <div class="text-right">
                    <p class="text-xs font-bold text-primary">{{ auth()->user()?->name }}</p>
                    <p class="text-[11px] text-slate-500">Chief Systems Admin</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-secondary/40 bg-primary-container text-sm font-bold text-white">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-8 py-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section>
                <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span>Administration</span>
                    <span class="material-symbols-outlined text-xs">chevron_right</span>
                    <span class="text-secondary">User & Role Management</span>
                </div>
                <div class="flex flex-wrap items-baseline gap-3">
                    <h1 class="font-serif text-4xl font-semibold tracking-tight">Create Staff Account</h1>
                    <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-[#5b4313]">{{ $totalUsers }} Active Personnel</span>
                </div>
                <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">Create accounts for receptionist, billing, and housekeeping staff. Accounts created here can sign in right away. Administrator accounts cannot be created here.</p>
            </section>

            <section class="folio-shadow w-full max-w-2xl rounded-xl border border-surface-container-highest bg-white p-8">
                @php
                    $roleLabels = collect($roleOptions)->mapWithKeys(fn ($role, $value) => [$value => $role['label']])->all();
                @endphp

                <form id="registration-form" method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <div class="mb-2 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-600">Hospitality Roles</span>
                            <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-bold text-primary">
                                Selected Role: <span id="selected-role-label">{{ old('role') ? ($roleLabels[old('role')] ?? ucfirst(old('role'))) : 'None' }}</span>
                            </span>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3">
                            @foreach ($roleOptions as $value => $role)
                                <label class="role-card relative cursor-pointer rounded-lg border border-surface-variant bg-surface-container px-3 py-3 text-center text-xs font-bold text-primary transition hover:border-secondary hover:bg-secondary-container" data-role-label="{{ $role['label'] }}">
                                    <input class="sr-only" type="radio" name="role" value="{{ $value }}" @checked(old('role') === $value) required>
                                    <span class="selected-check material-symbols-outlined absolute right-2 top-2 text-sm opacity-0 transition">check_circle</span>
                                    <span class="material-symbols-outlined mb-1 block text-lg">{{ $role['icon'] }}</span>
                                    <span>{{ $role['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-bold text-primary" for="name">Staff Full Name</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                    <span class="material-symbols-outlined text-lg">person</span>
                                </span>
                                <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Example: Maria Santos" required>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-bold text-primary" for="email">Work Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                    <span class="material-symbols-outlined text-lg">mail</span>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="sample@lecharmehotel.com" required>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-600">Staff must use an <strong>@lecharmehotel.com</strong> work email.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold text-primary" for="password">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                    <span class="material-symbols-outlined text-lg">lock</span>
                                </span>
                                <input id="password" name="password" type="password" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Minimum 8 characters" aria-describedby="password-match-status" required>
                                <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 hover:text-primary" data-password-toggle="password" type="button" aria-label="Show password">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-bold text-primary" for="password_confirmation">Confirm Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                    <span class="material-symbols-outlined text-lg">verified_user</span>
                                </span>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-10 text-sm focus:border-secondary focus:ring-secondary" aria-describedby="password-match-status" required>
                                <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600 hover:text-primary" data-password-toggle="password_confirmation" type="button" aria-label="Show confirm password">
                                    <span class="material-symbols-outlined text-lg">visibility</span>
                                </button>
                            </div>
                        </div>

                        <div id="password-match-status" class="hidden sm:col-span-2" role="status" aria-live="polite">
                            <p id="password-match-success" class="hidden items-center gap-1.5 text-xs font-bold text-emerald-700">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                                <span>Passwords match.</span>
                            </p>
                            <p id="password-match-error" class="hidden items-center gap-1.5 text-xs font-bold text-red-700">
                                <span class="material-symbols-outlined text-base" aria-hidden="true">error</span>
                                <span>Passwords do not match.</span>
                            </p>
                        </div>
                    </div>

                    <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-secondary/20 bg-primary px-5 py-3 text-base font-bold text-white shadow-md transition hover:bg-primary-container" type="submit">
                        <span>Create Staff Account</span>
                        <span class="material-symbols-outlined text-secondary-fixed">arrow_forward</span>
                    </button>
                </form>
            </section>
        </main>
    </div>

    <script>
        const selectedRoleLabel = document.getElementById('selected-role-label');
        const registrationForm = document.getElementById('registration-form');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordMatchStatus = document.getElementById('password-match-status');
        const passwordMatchSuccess = document.getElementById('password-match-success');
        const passwordMatchError = document.getElementById('password-match-error');

        const updatePasswordMatchStatus = () => {
            const hasConfirmation = passwordConfirmation.value.length > 0;
            const passwordsMatch = hasConfirmation && password.value === passwordConfirmation.value;
            const passwordsMismatch = hasConfirmation && !passwordsMatch;

            passwordConfirmation.setCustomValidity(passwordsMismatch ? 'Passwords do not match.' : '');
            passwordConfirmation.setAttribute('aria-invalid', passwordsMismatch ? 'true' : 'false');
            passwordMatchStatus.classList.toggle('hidden', !hasConfirmation);
            passwordMatchSuccess.classList.toggle('hidden', !passwordsMatch);
            passwordMatchSuccess.classList.toggle('flex', passwordsMatch);
            passwordMatchError.classList.toggle('hidden', !passwordsMismatch);
            passwordMatchError.classList.toggle('flex', passwordsMismatch);

            return passwordsMatch;
        };

        document.querySelectorAll('.role-card input[name="role"]').forEach((input) => {
            input.addEventListener('change', () => {
                if (input.checked) {
                    selectedRoleLabel.textContent = input.closest('.role-card').dataset.roleLabel;
                }
            });
        });

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('.material-symbols-outlined');
                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                icon.textContent = isHidden ? 'visibility_off' : 'visibility';
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });

        password.addEventListener('input', updatePasswordMatchStatus);
        passwordConfirmation.addEventListener('input', updatePasswordMatchStatus);

        registrationForm.addEventListener('submit', (event) => {
            if (passwordConfirmation.value.length > 0 && !updatePasswordMatchStatus()) {
                event.preventDefault();
                passwordConfirmation.focus();
                passwordConfirmation.reportValidity();
            }
        });
    </script>
</body>
</html>
