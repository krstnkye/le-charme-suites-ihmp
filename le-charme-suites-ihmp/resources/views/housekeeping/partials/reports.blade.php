@php
    $since = now()->subDays(7);
    $completedThisWeek = $housekeepingTasks->where('status', 'completed')->filter(fn ($task) => $task->completed_at?->greaterThanOrEqualTo($since));
    $staffRows = $housekeepingStaff->map(fn ($staff) => [
        'name' => $staff->name,
        'open' => $housekeepingTasks->where('assigned_to', $staff->id)->whereNotIn('status', ['completed', 'cancelled'])->count(),
        'completed' => $completedThisWeek->where('assigned_to', $staff->id)->count(),
        'avgMinutes' => (int) round($completedThisWeek->where('assigned_to', $staff->id)->filter(fn ($task) => $task->started_at && $task->completed_at)->avg(fn ($task) => $task->started_at->diffInMinutes($task->completed_at)) ?? 0),
    ]);
    $openReports = $maintenanceReports->where('status', '!=', 'resolved');
@endphp

<section class="space-y-5">
    <div class="flex flex-col justify-between gap-3 rounded-xl border border-line bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h3 class="font-serif text-3xl font-bold text-primary">Housekeeping Operational Reports &amp; Response Time Analytics</h3>
            <p class="mt-1 text-sm text-slate-600">Calculated from saved tasks, room statuses, maintenance reports, and lost &amp; found records (last 7 days for task output).</p>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white"><span class="material-symbols-outlined text-base">print</span>Print Report</button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Tasks completed (7 days)', $completedThisWeek->count(), 'task_alt'],
            ['Open tasks', $housekeepingTasks->whereNotIn('status', ['completed', 'cancelled'])->count(), 'pending_actions'],
            ['Open maintenance reports', $openReports->count(), 'build'],
            ['Items in lost & found', $lostFoundItems->where('status', 'in_custody')->count(), 'inventory_2'],
        ] as [$label, $value, $icon])
            <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}<span class="material-symbols-outlined text-secondary">{{ $icon }}</span></div>
                <p class="mt-2 font-serif text-3xl font-bold text-primary">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-line bg-white shadow-sm">
            <h4 class="border-b border-line p-4 font-bold text-primary">Housekeeper Workload</h4>
            <table class="w-full text-left text-sm">
                <thead class="bg-[#f6f3ed] text-[11px] font-bold uppercase text-slate-500"><tr><th class="px-4 py-2">Housekeeper</th><th class="px-4 py-2 text-right">Open</th><th class="px-4 py-2 text-right">Completed (7d)</th><th class="px-4 py-2 text-right">Avg. minutes</th></tr></thead>
                <tbody class="divide-y divide-line">
                    @forelse ($staffRows as $row)
                        <tr><td class="px-4 py-2 font-semibold text-primary">{{ $row['name'] }}</td><td class="px-4 py-2 text-right">{{ $row['open'] }}</td><td class="px-4 py-2 text-right">{{ $row['completed'] }}</td><td class="px-4 py-2 text-right">{{ $row['avgMinutes'] ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No housekeeping staff accounts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rounded-xl border border-line bg-white p-4 shadow-sm">
            <h4 class="font-bold text-primary">Rooms by Status</h4>
            <dl class="mt-3 space-y-2 text-sm">
                @foreach (['available' => 'Available', 'reserved' => 'Reserved', 'occupied' => 'Occupied', 'out_of_service' => 'Cleaning / Maintenance'] as $key => $label)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ $label }}</dt><dd class="font-bold text-primary">{{ $roomAvailability[$key] }}</dd></div>
                @endforeach
                <div class="flex justify-between border-t border-line pt-2"><dt class="font-bold">Total hotel rooms</dt><dd class="font-bold text-primary">{{ $roomAvailability['property_total'] }}</dd></div>
            </dl>
            <h4 class="mt-5 font-bold text-primary">Open Maintenance by Urgency</h4>
            <dl class="mt-2 space-y-1 text-sm">
                @foreach (['critical', 'high', 'normal', 'low'] as $severity)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ ucfirst($severity) }}</dt><dd class="font-bold">{{ $openReports->where('severity', $severity)->count() }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>
</section>
