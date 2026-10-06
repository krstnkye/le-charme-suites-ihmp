<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium">Room number</label>
        <input name="room_number" value="{{ old('room_number', $room->room_number ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Room type</label>
        <input name="room_type" value="{{ old('room_type', $room->room_type ?? '') }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Rate</label>
        <input name="rate" type="number" step="0.01" min="0" value="{{ old('rate', $room->rate ?? 0) }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Capacity</label>
        <input name="capacity" type="number" min="1" value="{{ old('capacity', $room->capacity ?? 1) }}" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium">Status</label>
        <select name="status" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2" required>
            @foreach (['available', 'reserved', 'occupied', 'cleaning', 'maintenance'] as $option)
                <option value="{{ $option }}" @selected(old('status', $room->status ?? 'available') === $option)>{{ ucfirst($option) }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-4">
    <label class="block text-sm font-medium">Description</label>
    <textarea name="description" class="mt-2 w-full rounded-md border border-slate-300 px-4 py-2">{{ old('description', $room->description ?? '') }}</textarea>
</div>
