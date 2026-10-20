@extends('layouts.housekeeping')

@php
    $active = 'profile';
    $nameParts = preg_split('/\s+/', trim($user->name));
    $initials = strtoupper(substr($nameParts[0] ?? 'H', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
    $employeeId = 'EMP-HK-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
    $shiftWindow = '06:00 - 14:30 PHT';
    $certifications = [
        ['icon' => 'verified', 'title' => 'Five-Star Room Presentation Standard', 'issuer' => 'Le Charme QA Academy', 'status' => 'Certified', 'validity' => 'Valid until Oct 2027'],
        ['icon' => 'health_and_safety', 'title' => 'Chemical Handling & Safety', 'issuer' => 'Facilities Operations', 'status' => 'Cleared', 'validity' => 'Annual refresher complete'],
        ['icon' => 'key', 'title' => 'RFID Master Key Custody', 'issuer' => 'Security Desk', 'status' => 'Authorized', 'validity' => 'Shift-supervisor access'],
    ];
@endphp

@section('title', 'My Profile & Attendant Credentials Console - Housekeeping')

@section('content')
    <section class="flex flex-col gap-5 border-b border-line pb-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                <span>Housekeeping Oversight</span>
                <span class="text-secondary">/</span>
                <span>Staff Accounts</span>
                <span class="text-secondary">/</span>
                <span class="text-primary">Housekeeping Account</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h2 class="font-serif text-4xl font-bold leading-tight text-primary">Executive Housekeeper Profile &amp; Digital Duty Credentials</h2>
                <span class="rounded-full bg-[#ffdeaa] px-3 py-1 text-xs font-bold text-secondary">Live Staff Console</span>
            </div>
            <p class="mt-3 max-w-4xl text-sm leading-6 text-slate-600">
                Manage your staff identity, shift authorizations, housekeeping terminal access, radio contact details, and service accreditation records.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-secondary bg-white px-4 py-2.5 text-sm font-bold text-secondary shadow-sm transition hover:bg-[#fff7e8]">
                <span class="material-symbols-outlined text-base">download</span>
                Download Credential Sheet
            </button>
            <a href="#profile-form" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#172b42]">
                <span class="material-symbols-outlined text-base">edit</span>
                Edit Profile
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-line bg-primary text-white shadow-lg">
        <div class="grid gap-0 lg:grid-cols-[1.5fr_1fr]">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border border-[#ffdeaa]/50 bg-[#ffdeaa]/15 text-3xl font-extrabold text-[#ffdeaa]">
                        {{ $initials ?: 'H' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#ffdeaa]">Active Housekeeping Personnel</p>
                        <h3 class="mt-2 font-serif text-3xl font-bold">{{ $user->name }}</h3>
                        <p class="mt-1 text-sm text-[#b4c8e5]">{{ $user->email }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white">{{ $employeeId }}</span>
                            <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold text-emerald-200">On Duty</span>
                            <span class="rounded-full bg-[#ffdeaa]/15 px-3 py-1 text-xs font-bold text-[#ffdeaa]">Shift Supervisor</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 border-t border-white/10 bg-[#172b42] lg:border-l lg:border-t-0">
                <div class="border-b border-r border-white/10 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#b4c8e5]">Duty Shift</p>
                    <p class="mt-2 text-lg font-extrabold">{{ $shiftWindow }}</p>
                </div>
                <div class="border-b border-white/10 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#b4c8e5]">Terminal</p>
                    <p class="mt-2 text-lg font-extrabold">HK Mobile</p>
                </div>
                <div class="border-r border-white/10 p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#b4c8e5]">Radio Node</p>
                    <p class="mt-2 text-lg font-extrabold">Ch. 2</p>
                </div>
                <div class="p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#b4c8e5]">Credential</p>
                    <p class="mt-2 text-lg font-extrabold">Verified</p>
                </div>
            </div>
        </div>
    </section>

    <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            @include('profile.partials.photo-field')
            <section class="rounded-xl border border-line bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Personal Details &amp; Contact Information</p>
                        <h3 class="mt-1 font-serif text-2xl font-bold">Identity Record</h3>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Synced with user account</span>
                </div>
                <div class="grid gap-5 p-6 md:grid-cols-2">
                    <label class="text-sm font-bold text-primary">
                        Full Name
                        <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-lg border-line text-sm shadow-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                    <label class="text-sm font-bold text-primary">
                        Email Address
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-lg border-line text-sm shadow-sm focus:border-secondary focus:ring-secondary" required>
                    </label>
                    <div class="rounded-lg border border-line bg-[#fcf9f3] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Employee ID</p>
                        <p class="mt-2 font-mono text-sm font-bold text-primary">{{ $employeeId }}</p>
                    </div>
                    <div class="rounded-lg border border-line bg-[#fcf9f3] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Department</p>
                        <p class="mt-2 text-sm font-bold text-primary">Rooms &amp; Facilities Operations</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-line bg-white shadow-sm">
                <div class="border-b border-line px-6 py-5">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Secure Access &amp; Digital Credentials</p>
                    <h3 class="mt-1 font-serif text-2xl font-bold">Password &amp; Terminal Security</h3>
                    <p class="mt-2 text-sm text-slate-600">Leave the password fields blank when you only want to update your name or email.</p>
                </div>
                <div class="grid gap-5 p-6 md:grid-cols-2">
                    <label class="text-sm font-bold text-primary">
                        New Password
                        <input type="password" name="password" class="mt-2 w-full rounded-lg border-line text-sm shadow-sm focus:border-secondary focus:ring-secondary" autocomplete="new-password">
                    </label>
                    <label class="text-sm font-bold text-primary">
                        Confirm Password
                        <input type="password" name="password_confirmation" class="mt-2 w-full rounded-lg border-line text-sm shadow-sm focus:border-secondary focus:ring-secondary" autocomplete="new-password">
                    </label>
                    <div class="rounded-lg border border-line bg-[#f6f3ed] p-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-secondary">fingerprint</span>
                            <div>
                                <p class="text-sm font-bold">Biometric Terminal</p>
                                <p class="text-xs text-slate-600">Enabled for housekeeping station access</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-line bg-[#f6f3ed] p-4">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-secondary">vpn_key</span>
                            <div>
                                <p class="text-sm font-bold">RFID Key Ledger</p>
                                <p class="text-xs text-slate-600">Authorized for assigned room floors only</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-xl border border-line bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Shift &amp; Duty Roster Preferences</p>
                        <h3 class="mt-1 font-serif text-2xl font-bold">Current Assignment</h3>
                    </div>
                    <span class="material-symbols-outlined text-secondary">calendar_clock</span>
                </div>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-lg bg-[#fcf9f3] px-4 py-3">
                        <span class="text-slate-600">Assigned Shift</span>
                        <span class="font-bold">Shift 1</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-[#fcf9f3] px-4 py-3">
                        <span class="text-slate-600">Duty Window</span>
                        <span class="font-bold">{{ $shiftWindow }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-[#fcf9f3] px-4 py-3">
                        <span class="text-slate-600">Floor Coverage</span>
                        <span class="font-bold">All assigned rooms</span>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-line bg-white p-6 shadow-sm">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Certifications &amp; Hospitality Ledger</p>
                    <h3 class="mt-1 font-serif text-2xl font-bold">Active Credentials</h3>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($certifications as $certification)
                        <div class="rounded-lg border border-line bg-[#fcf9f3] p-4">
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-secondary">{{ $certification['icon'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-bold">{{ $certification['title'] }}</p>
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ $certification['status'] }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-600">{{ $certification['issuer'] }}</p>
                                    <p class="mt-2 text-xs font-semibold text-secondary">{{ $certification['validity'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="rounded-xl border border-secondary/30 bg-[#fff7e8] p-5">
                <p class="text-sm font-bold text-secondary">Profile updates are limited to your own housekeeping account.</p>
                <p class="mt-2 text-xs leading-5 text-slate-600">Role assignments, floor authorization, and terminal permissions are maintained by the administrator.</p>
            </div>

            <div class="flex flex-wrap justify-end gap-3 rounded-xl border border-line bg-white p-4 shadow-sm">
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-line px-5 py-3 text-sm font-bold text-primary transition hover:bg-[#f6f3ed]">Cancel</a>
                <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-[#172b42]">
                    <span class="material-symbols-outlined text-base">save</span>
                    Save Profile
                </button>
            </div>
        </aside>
    </form>
    @include('profile.partials.photo-script')
@endsection
