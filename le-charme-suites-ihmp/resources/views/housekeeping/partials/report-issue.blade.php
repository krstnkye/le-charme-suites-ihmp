@php
    $preselectedRoomId = (int) old('room_id', request('room'));
    $selectedCategory = old('category', 'plumbing');
    $selectedSeverity = old('severity', 'normal');
    $categoryIcons = ['hvac' => 'mode_fan', 'plumbing' => 'faucet', 'door_lock' => 'lock_clock', 'electrical' => 'lightbulb', 'furniture' => 'deck', 'tv_media' => 'tv', 'other' => 'build'];
    $openReportCount = $maintenanceReports->where('status', '!=', 'resolved')->count();
@endphp

<section class="grid gap-6 xl:grid-cols-12">
    <div class="rounded-xl border border-line bg-white shadow-sm xl:col-span-8">
        <div class="border-b border-line p-5">
            <h3 class="font-serif text-3xl font-bold text-primary">Report Maintenance Defect or Room Discrepancy</h3>
            <p class="mt-2 text-sm text-slate-600">Record a defect so maintenance can fix it. Reports are saved and tracked on the Reported Issues page.</p>
        </div>

        <form method="POST" action="{{ route('maintenance-reports.store') }}" enctype="multipart/form-data" class="space-y-6 p-5" data-report-issue-form>
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-bold text-primary">Room
                    <select name="room_id" class="mt-1.5 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">
                        <option value="">Not a guest room (use location)</option>
                        @foreach ($allRooms as $room)
                            <option value="{{ $room->id }}" @selected($preselectedRoomId === $room->id)>Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm font-bold text-primary">Other location <span class="font-normal text-slate-500">(if not a room)</span>
                    <input name="location" value="{{ old('location') }}" maxlength="120" placeholder="e.g. 3rd floor hallway, lobby restroom" class="mt-1.5 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">
                </label>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-bold text-primary">What is the problem?</legend>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (\App\Models\MaintenanceReport::CATEGORIES as $value => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line bg-white p-3 text-sm font-semibold text-primary has-[:checked]:border-secondary has-[:checked]:bg-secondary/10">
                            <input type="radio" name="category" value="{{ $value }}" class="text-secondary focus:ring-secondary" @checked($selectedCategory === $value)>
                            <span class="material-symbols-outlined text-base text-secondary">{{ $categoryIcons[$value] }}</span>{{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend class="mb-2 text-sm font-bold text-primary">How urgent is it?</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach (\App\Models\MaintenanceReport::SEVERITIES as $value => $label)
                        <label @class([
                            'flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm font-semibold has-[:checked]:ring-2 has-[:checked]:ring-secondary',
                            'border-red-200 bg-red-50 text-red-800' => $value === 'critical',
                            'border-amber-200 bg-amber-50 text-amber-900' => $value === 'high',
                            'border-line bg-white text-primary' => in_array($value, ['low', 'normal'], true),
                        ])>
                            <input type="radio" name="severity" value="{{ $value }}" class="text-secondary focus:ring-secondary" @checked($selectedSeverity === $value)>{{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <label class="block text-sm font-bold text-primary">Describe the problem
                <textarea name="description" rows="4" required maxlength="2000" placeholder="e.g. Aircon not cooling, water dripping from unit above the bed" class="mt-1.5 block w-full rounded-lg border-line bg-white px-3 py-2.5 text-sm text-primary focus:border-secondary focus:ring-secondary">{{ old('description') }}</textarea>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-bold text-primary">Photo <span class="font-normal text-slate-500">(optional)</span>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" capture="environment" class="mt-1.5 block w-full rounded-lg border border-line bg-white px-3 py-2 text-sm text-primary file:mr-3 file:rounded file:border-0 file:bg-surface-container file:px-3 file:py-1 file:text-xs file:font-bold">
                    <span class="mt-1 block text-xs font-normal text-slate-500">On a phone or tablet this opens the camera.</span>
                </label>
                <label class="flex items-start gap-3 rounded-lg border border-line bg-[#fcf9f3] p-3 text-sm text-primary">
                    <input type="hidden" name="take_out_of_service" value="0">
                    <input type="checkbox" name="take_out_of_service" value="1" class="mt-1 rounded text-secondary focus:ring-secondary" @checked(old('take_out_of_service'))>
                    <span><span class="font-bold">Take the room out of service</span><span class="mt-0.5 block text-xs text-slate-500">Marks the room as Maintenance so it cannot be sold until the report is resolved. Ignored while a guest is checked in.</span></span>
                </label>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-line pt-4">
                <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="inline-flex justify-center rounded-lg border border-line px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-[#f6f3ed]">View Reported Issues</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white hover:bg-primary/90">
                    <span class="material-symbols-outlined text-base text-[#ffdeaa]">report</span>Submit Report
                </button>
            </div>
        </form>
    </div>

    <aside class="space-y-4 xl:col-span-4">
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <h3 class="font-bold text-primary">Maintenance Summary</h3>
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Open reports</dt><dd class="font-bold text-primary">{{ $openReportCount }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Critical / high</dt><dd class="font-bold text-red-700">{{ $maintenanceReports->where('status', '!=', 'resolved')->whereIn('severity', ['critical', 'high'])->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Rooms out of service</dt><dd class="font-bold text-primary">{{ $maintenanceQueue->count() }}</dd></div>
            </dl>
            <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-secondary px-4 py-2.5 text-sm font-bold text-secondary hover:bg-secondary/10">Open Reported Issues</a>
        </div>
        <div class="rounded-xl border border-line bg-white p-5 shadow-sm">
            <h3 class="font-bold text-primary">Need to tell the front desk?</h3>
            <p class="mt-1 text-sm text-slate-600">Send a department message if a guest is affected or needs a room change.</p>
            <a href="{{ route('messages.index') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-xs font-bold text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm">forum</span>Message Front Desk</a>
        </div>
    </aside>
</section>
