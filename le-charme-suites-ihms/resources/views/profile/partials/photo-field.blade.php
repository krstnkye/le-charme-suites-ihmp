@php
    $photoVersion = $user->updated_at?->timestamp ?? time();
@endphp

<section class="rounded-xl border border-[#e5e2dc] bg-[#f6f3ed] p-4 sm:col-span-2" aria-labelledby="profile-photo-title">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-xl border-2 border-[#755a28] bg-[#01162d] shadow-sm">
            @if ($user->profile_photo_path)
                <img src="{{ route('profile.photo', ['v' => $photoVersion]) }}" alt="Current profile photo" class="h-full w-full object-cover" data-profile-photo-preview>
                <span class="hidden h-full w-full items-center justify-center font-serif text-3xl font-bold text-[#ffdeaa]" data-profile-photo-placeholder>{{ $initials }}</span>
            @else
                <img src="" alt="New profile photo preview" class="hidden h-full w-full object-cover" data-profile-photo-preview>
                <span class="flex h-full w-full items-center justify-center font-serif text-3xl font-bold text-[#ffdeaa]" data-profile-photo-placeholder>{{ $initials }}</span>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <h3 id="profile-photo-title" class="text-sm font-bold text-[#01162d]">Profile Photo</h3>
            <p class="mt-1 text-xs leading-5 text-slate-500">Use a clear, front-facing personnel photo. JPG, PNG, or WebP up to 5 MB.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <label for="camera_photo" class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-[#01162d] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#172b42] focus-within:ring-2 focus-within:ring-[#755a28]">
                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">photo_camera</span>
                    Take Photo
                </label>
                <input id="camera_photo" name="camera_photo" type="file" accept="image/jpeg,image/png,image/webp" capture="user" class="sr-only" data-profile-photo-input>

                <label for="profile_photo" class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-[#c4c6cd] bg-white px-3.5 py-2 text-xs font-bold text-[#01162d] shadow-sm transition hover:border-[#755a28] hover:bg-[#fffaf0] focus-within:ring-2 focus-within:ring-[#755a28]">
                    <span class="material-symbols-outlined text-base text-[#755a28]">upload</span>
                    Choose Photo
                </label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-profile-photo-input>

                @if ($user->profile_photo_path)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">
                        <input type="checkbox" name="remove_profile_photo" value="1" class="rounded border-slate-300 text-red-700 focus:ring-red-600">
                        Remove Photo
                    </label>
                @endif
            </div>
            <p class="mt-2 hidden text-xs font-semibold text-emerald-700" data-profile-photo-status>New photo ready. Save your profile to apply it.</p>
        </div>
    </div>
</section>
