@php
    $openReports = $maintenanceReports->where('status', '!=', 'resolved');
@endphp

<section class="grid gap-6 xl:grid-cols-12">
    <div class="space-y-4 xl:col-span-8">
        <div class="flex flex-col justify-between gap-3 rounded-xl border border-line bg-white p-5 shadow-sm md:flex-row md:items-center">
            <div>
                <h3 class="font-serif text-3xl font-bold text-primary">Maintenance Requests &amp; Engineering Assignment List</h3>
                <p class="mt-1 text-sm text-slate-600">Rooms out of service and the open maintenance reports behind them.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="rounded-lg border border-line px-3 py-2 text-xs font-bold text-primary hover:bg-[#f6f3ed]">Room Status</a>
                <a href="{{ route('housekeeping.page', 'report-issue') }}" class="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white">Report Issue</a>
            </div>
        </div>

        <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
            <h4 class="font-bold text-primary">Rooms Out of Service ({{ $maintenanceQueue->count() }})</h4>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @forelse ($maintenanceQueue as $room)
                    @php($roomReports = $openReports->where('room_id', $room->id))
                    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm">
                        <p class="font-bold text-red-900">Room {{ $room->room_number }} - {{ $room->room_type }}</p>
                        <p class="text-xs text-red-800">{{ $roomReports->isEmpty() ? 'No open report linked - file one so the issue is tracked.' : $roomReports->map(fn ($report) => \App\Models\MaintenanceReport::CATEGORIES[$report->category] ?? $report->category)->implode(', ') }}</p>
                        <a href="{{ $roomReports->isEmpty() ? route('housekeeping.page', ['page' => 'report-issue', 'room' => $room->id]) : route('housekeeping.page', 'reported-issues') }}" class="mt-2 inline-block text-xs font-bold text-red-900 underline">{{ $roomReports->isEmpty() ? 'Report issue for this room' : 'View report' }}</a>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No rooms are out of service.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
            <h4 class="font-bold text-primary">Open Reports ({{ $openReports->count() }})</h4>
            <ul class="mt-3 divide-y divide-line text-sm">
                @forelse ($openReports->take(15) as $report)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                        <span><span class="font-bold text-primary">{{ $report->locationLabel() }}</span> - {{ \App\Models\MaintenanceReport::CATEGORIES[$report->category] ?? $report->category }} <span class="text-xs text-slate-500">({{ ucfirst($report->severity) }}, {{ \App\Models\MaintenanceReport::STATUSES[$report->status] }})</span></span>
                        <a href="{{ route('housekeeping.page', 'reported-issues') }}" class="text-xs font-bold text-secondary hover:underline">Update</a>
                    </li>
                @empty
                    <li class="py-2 text-slate-500">No open maintenance reports.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <aside class="xl:col-span-4">
        <form action="{{ route('maintenance-reports.store') }}" method="POST" class="space-y-3 rounded-xl border border-line bg-white p-5 shadow-sm">
            @csrf
            <input type="hidden" name="severity" value="normal">
            <h3 class="font-serif text-2xl font-bold text-primary">Quick Report</h3>
            <label class="block text-xs font-bold text-slate-600">Room
                <select name="room_id" required class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                    @foreach ($allRooms as $room)
                        <option value="{{ $room->id }}">Room {{ $room->room_number }} - {{ $room->room_type }} ({{ ucfirst($room->status) }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-bold text-slate-600">Problem
                <select name="category" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm">
                    @foreach (\App\Models\MaintenanceReport::CATEGORIES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-bold text-slate-600">Description
                <textarea name="description" required maxlength="2000" rows="3" class="mt-1 block w-full rounded-lg border-line bg-white py-2 text-sm" placeholder="Describe the defect"></textarea>
            </label>
            <label class="flex items-center gap-2 text-xs font-bold text-slate-600">
                <input type="hidden" name="take_out_of_service" value="0">
                <input type="checkbox" name="take_out_of_service" value="1" class="rounded text-secondary"> Take room out of service
            </label>
            <button type="submit" class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white">Submit Maintenance Report</button>
            <a href="{{ route('housekeeping.page', 'report-issue') }}" class="block text-center text-xs font-bold text-secondary hover:underline">Need a photo or other location? Open the full report form</a>
        </form>
    </aside>
</section>
