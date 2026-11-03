<!DOCTYPE html>
<html class="h-full bg-surface" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
                        surface: '#f5f5f2',
                        primary: '#161a20',
                        secondary: '#5b6069',
                        'secondary-fixed': '#e8e8e4',
                        'secondary-container': '#e8e8e4',
                        'surface-container-lowest': '#ffffff',
                        'surface-container': '#ebebe7',
                        'surface-variant': '#e4e4df',
                        'outline-variant': '#d3d3cd',
                        'on-surface': '#161a20',
                        'on-surface-variant': '#4a4f57',
                        error: '#ba1a1a',
                    },
                    fontFamily: {
                        body: ['Manrope', 'sans-serif'],
                        headline: ['Manrope', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            vertical-align: middle;
        }

        .role-card:has(input:checked) {
            background: #161a20;
            border-color: #5b6069;
            color: #ffffff;
            box-shadow: 0 0 0 2px rgba(117, 90, 40, 0.28), 0 12px 24px -18px rgba(1, 22, 45, 0.7);
        }

        .role-card:has(input:checked) .material-symbols-outlined,
        .role-card:has(input:checked) .selected-check {
            color: #e8e8e4;
        }

        .role-card:has(input:checked) .selected-check {
            opacity: 1;
            transform: scale(1);
        }
    </style>
    @include('partials.favicon')
</head>
<body class="h-full overflow-y-auto bg-[#f5f5f2] font-body text-on-surface antialiased selection:bg-secondary-container">
    <main class="lc-auth">
        <figure class="lc-auth-photo" aria-hidden="true">
            <img src="{{ asset('images/rooms/family-room.jpg') }}" alt="">
            <figcaption><strong>Family Room</strong>Le Charmé Bldg. 1131 Palm St., Subic Bay Freeport Zone</figcaption>
        </figure>
        <div class="lc-auth-panel">

        <section class="lc-auth-form">
            <div class="mb-8 text-center">
                @include('partials.brand-logo', ['class' => 'lc-auth-logo'])
                
                <p class="text-sm font-medium text-on-surface-variant">Integrated Hotel Management Portal account registration</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $roleOptions = collect(config('hotel.staff_roles'))->except('admin')->all();
                $roleLabels = collect($roleOptions)
                    ->mapWithKeys(fn ($role, $value) => [$value => $role['label']])
                    ->all();
            @endphp

            <form id="registration-form" method="POST" action="{{ route('register.store') }}" class="space-y-5">
                @csrf

                <div>
                    <div class="mb-2 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                        <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Hospitality Roles</span>
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
                        <label class="mb-1 block text-sm font-bold text-on-surface" for="name">Staff Full Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                <span class="material-symbols-outlined text-lg">person</span>
                            </span>
                            <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="Example: Maria Santos" required autofocus>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-bold text-on-surface" for="email">Work Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                <span class="material-symbols-outlined text-lg">mail</span>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-3 text-sm focus:border-secondary focus:ring-secondary" placeholder="sample@lecharmehotel.com" required>
                        </div>
                        <p class="mt-1.5 text-xs text-on-surface-variant">Use your assigned <strong>@lecharmehotel.com</strong> work email.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-on-surface" for="password">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                <span class="material-symbols-outlined text-lg">lock</span>
                            </span>
                            <input id="password" name="password" type="password" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-10 text-sm focus:border-secondary focus:ring-secondary" placeholder="Minimum 8 characters" aria-describedby="password-match-status" required>
                            <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant hover:text-primary" data-password-toggle="password" type="button" aria-label="Show password">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-bold text-on-surface" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-secondary">
                                <span class="material-symbols-outlined text-lg">verified_user</span>
                            </span>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-outline-variant bg-surface py-2.5 pl-10 pr-10 text-sm focus:border-secondary focus:ring-secondary" aria-describedby="password-match-status" required>
                            <button class="absolute inset-y-0 right-0 flex items-center pr-3 text-on-surface-variant hover:text-primary" data-password-toggle="password_confirmation" type="button" aria-label="Show confirm password">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div id="password-match-status" class="hidden sm:col-span-2" role="status" aria-live="polite">
                        <p id="password-match-success" class="hidden items-center gap-1.5 text-xs font-bold text-emerald-700">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                            <span>Passwords match.</span>
                        </p>
                        <p id="password-match-error" class="hidden items-center gap-1.5 text-xs font-bold text-error">
                            <span class="material-symbols-outlined text-base" aria-hidden="true">error</span>
                            <span>Passwords do not match.</span>
                        </p>
                    </div>
                </div>

                <p class="rounded-lg border border-secondary/20 bg-secondary-container/20 px-3 py-2 text-xs text-on-surface-variant">A system administrator must approve new accounts before they can sign in.</p>

                <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-secondary/20 bg-primary px-5 py-3 text-base font-bold text-surface shadow-md transition hover:bg-[#2a2f37]" type="submit">
                    <span>Create IHMP Account</span>
                    <span class="material-symbols-outlined text-secondary-fixed">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 border-t border-surface-variant pt-4 text-center">
                <p class="text-xs text-on-surface-variant">
                    Already registered?
                    <a class="font-bold text-secondary hover:underline" href="{{ route('login') }}">Sign in to IHMP</a>
                </p>
                <p class="mt-2 text-xs font-semibold text-on-surface-variant">{{ config('hotel.address') }}</p>
                <p class="mt-1 text-[11px] text-outline">{{ config('hotel.phone') }} · {{ config('hotel.email') }}</p>
            </div>
        </section>
        </div>
    </main>
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
