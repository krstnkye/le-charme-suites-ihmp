@php
    $canEditGuest = auth()->user()?->hasPermissionTo('guests.write');
    $isIdentificationComplete = $guest->hasCompleteIdentification();
    $showUploadForm = $errors->hasAny(['identification_type', 'identification_number', 'identification_photo']);
@endphp

<div id="guest-identification" class="rounded-lg border border-surface-variant bg-surface-container-low p-3 md:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Identification Documents On File</span>
        @if ($isIdentificationComplete)
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700"><span class="material-symbols-outlined text-xs">verified</span>ID on file</span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-800"><span class="material-symbols-outlined text-xs">warning</span>ID still needed</span>
        @endif
    </div>

    <div class="mt-1 flex flex-wrap items-center gap-2">
        <span class="rounded border border-outline-variant/30 bg-white px-2 py-0.5 text-xs font-semibold">{{ $guest->identification_type ?: 'ID type not provided' }}</span>
        <span class="flex items-center gap-1 rounded border border-secondary/40 bg-white px-2 py-0.5 text-xs font-bold text-primary">
            <span class="material-symbols-outlined text-xs text-secondary">verified_user</span>
            {{ $guest->identification_number ?: 'ID number not provided' }}
        </span>
        @if ($guest->identification_photo_path)
            <a href="{{ route('guests.identification.photo', $guest) }}" target="_blank" rel="noopener" class="flex items-center gap-1 rounded border border-outline-variant/30 bg-white px-2 py-0.5 text-xs font-bold text-primary hover:bg-surface-container">
                <span class="material-symbols-outlined text-xs">image</span>View ID photo
            </a>
        @else
            <span class="rounded border border-outline-variant/30 bg-white px-2 py-0.5 text-xs font-semibold">No ID photo</span>
        @endif
    </div>

    @if (session('identification_status'))
        <p class="mt-2 rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800">{{ session('identification_status') }}</p>
    @endif
    @if ($errors->any())
        <ul class="mt-2 list-disc rounded border border-red-200 bg-red-50 py-1 pl-6 pr-2 text-xs font-semibold text-red-800">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    @if ($canEditGuest)
        <div class="mt-3 flex flex-wrap items-center gap-2">
            @unless ($isIdentificationComplete)
                <form method="POST" action="{{ route('reservations.identification-reminder', $reservation) }}">
                    @csrf
                    <button type="submit" @disabled(blank($guest->email)) title="{{ blank($guest->email) ? 'Add the guest email first' : 'Email the guest a reminder to bring a valid ID' }}" class="inline-flex items-center gap-1.5 rounded-lg border border-secondary px-3 py-1.5 text-xs font-bold text-secondary transition hover:bg-secondary/10 disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="material-symbols-outlined text-sm">forward_to_inbox</span>Remind Guest to Provide ID
                    </button>
                </form>
            @endunless
            <button type="button" data-toggle-identification-form class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:bg-primary-container">
                <span class="material-symbols-outlined text-sm">badge</span>{{ $isIdentificationComplete ? 'Update ID' : 'Upload ID' }}
            </button>
            @if ($guest->identification_reminded_at)
                <span class="text-[11px] text-on-surface-variant">Last reminder sent {{ $guest->identification_reminded_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</span>
            @endif
        </div>

        <form method="POST" action="{{ route('guests.identification.update', $guest) }}" enctype="multipart/form-data" data-identification-form @class(['mt-3 grid gap-3 rounded-lg border border-surface-variant bg-white p-3 sm:grid-cols-2', 'hidden' => ! $showUploadForm])>
            @csrf
            <label class="text-xs font-bold text-on-surface-variant">ID type
                <select name="identification_type" required class="mt-1 block w-full rounded-lg border-outline-variant px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-secondary">
                    <option value="">Select ID type</option>
                    @foreach (\App\Http\Controllers\GuestIdentificationController::ACCEPTED_ID_TYPES as $idType)
                        <option value="{{ $idType }}" @selected(old('identification_type', $guest->identification_type) === $idType)>{{ $idType }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-bold text-on-surface-variant">ID number
                <input name="identification_number" required maxlength="100" value="{{ old('identification_number', $guest->identification_number) }}" placeholder="e.g. P1234567A" class="mt-1 block w-full rounded-lg border-outline-variant px-3 py-2 text-sm text-primary focus:border-secondary focus:ring-secondary">
            </label>
            <label class="text-xs font-bold text-on-surface-variant sm:col-span-2">Photo of the ID {{ $guest->identification_photo_path ? '(optional - replaces the current photo)' : '' }}
                <input type="file" name="identification_photo" accept="image/jpeg,image/png,image/webp" capture="environment" @required(! $guest->identification_photo_path) class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2 text-sm text-primary file:mr-3 file:rounded file:border-0 file:bg-surface-container file:px-3 file:py-1 file:text-xs file:font-bold">
                <span class="mt-1 block font-normal text-on-surface-variant">JPG, PNG, or WEBP up to 5 MB. Stored privately; only staff can view it.</span>
            </label>
            <div class="flex justify-end gap-2 sm:col-span-2">
                <button type="button" data-toggle-identification-form class="rounded-lg border border-outline-variant px-3 py-1.5 text-xs font-bold text-primary hover:bg-surface-container">Cancel</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-1.5 text-xs font-bold text-white hover:bg-primary-container">Save Guest ID</button>
            </div>
        </form>

        <script>
            (() => {
                const form = document.querySelector('[data-identification-form]');
                document.querySelectorAll('[data-toggle-identification-form]').forEach((button) => {
                    button.addEventListener('click', () => form?.classList.toggle('hidden'));
                });
            })();
        </script>
    @endif
</div>
