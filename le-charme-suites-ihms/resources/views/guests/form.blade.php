<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium">First name</label>
        <input name="first_name" value="{{ old('first_name', $guest->first_name ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Last name</label>
        <input name="last_name" value="{{ old('last_name', $guest->last_name ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Email</label>
        <input name="email" type="email" value="{{ old('email', $guest->email ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Phone</label>
        <input name="phone" value="{{ old('phone', $guest->phone ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Identification type</label>
        <input name="identification_type" value="{{ old('identification_type', $guest->identification_type ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium">Identification number</label>
        <input name="identification_number" value="{{ old('identification_number', $guest->identification_number ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">
    </div>
</div>
<div class="mt-4">
    <label class="block text-sm font-medium">Address</label>
    <textarea name="address" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">{{ old('address', $guest->address ?? '') }}</textarea>
</div>
<div class="mt-4">
    <label class="block text-sm font-medium">Notes</label>
    <textarea name="notes" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">{{ old('notes', $guest->notes ?? '') }}</textarea>
</div>
