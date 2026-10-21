@php
    $statusFilter = request('status', 'open');
    $statusFilters = ['open' => 'Open & In Progress', 'resolved' => 'Resolved', 'all' => 'All'];
    $visibleReports = match ($statusFilter) {
        'resolved' => $maintenanceReports->where('status', 'resolved'),
        'all' => $maintenanceReports,
        default => $maintenanceReports->where('status', '!=', 'resolved'),
    };
    $severityTone = ['critical' => 'border-red-200 bg-red-50 text-red-800', 'high' => 'border-amber-200 bg-amber-50 text-amber-900', 'normal' => 'border-line bg-[#f6f3ed] text-slate-700', 'low' => 'border-line bg-white text-slate-600'];
    $statusTone = ['open' => 'bg-red-700 text-white', 'in_progress' => 'bg-amber-500 text-white', 'resolved' => 'bg-emerald-700 text-white'];
@endphp

<section class="space-y-5">
    <div class="flex flex-col justify-between gap-3 rounded-xl border border-line bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h3 class="font-serif text-3xl font-bold text-primary">Reported Issues &amp; Facilities Work Orders Log</h3>
            <p class="mt-1 text-sm text-slate-600">Every maintenance report saved in the system, with who reported it and how it was fixed.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($statusFilters as $value => $label)
                <a href="{{ route('housekeeping.page', ['page' => 'reported-issues', 'status' => $value]) }}" @class([
                    'rounded-full border px-3 py-1.5 text-xs font-bold',
                    'border-primary bg-primary text-white' => $statusFilter === $value,
                    'border-line bg-white text-slate-600 hover:text-primary' => $statusFilter !== $value,
                ])>{{ $label }}</a>
            @endforeach
            <a href="{{ route('housekeeping.page', 'report-issue') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary/90"><span class="material-symbols-outlined text-sm text-[#ffdeaa]">add</span>Report New Issue</a>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($visibleReports as $report)
            <article class="rounded-xl border border-line bg-white p-4 shadow-sm" data-maintenance-report="{{ $report->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold text-slate-500">MR-{{ str_pad((string) $report->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <h4 class="font-serif text-xl font-bold text-primary">{{ $report->locationLabel() }} - {{ \App\Models\MaintenanceReport::CATEGORIES[$report->category] ?? ucfirst($report->category) }}</h4>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusTone[$report->status] ?? 'bg-slate-500 text-white' }}">{{ \App\Models\MaintenanceReport::STATUSES[$report->status] ?? $report->status }}</span>
                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase {{ $severityTone[$report->severity] ?? '' }}">{{ ucfirst($report->severity) }}</span>
                            @if ($report->room_taken_out_of_service)
                                <span class="rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase text-red-800">Room out of service</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-slate-700">{{ $report->description }}</p>
                        <p class="mt-1 text-xs text-slate-500">Reported by {{ $report->reporter?->name ?? 'Former staff' }} · {{ $report->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}</p>
                        @if ($report->status === 'resolved')
                            <p class="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-900"><span class="font-bold">Resolved</span> by {{ $report->resolver?->name ?? 'staff' }} on {{ $report->resolved_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}: {{ $report->resolution_notes }}</p>
                        @endif
                    </div>
                    @if ($report->photo_path)
                        <a href="{{ route('maintenance-reports.photo', $report) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-line px-3 py-1.5 text-xs font-bold text-primary hover:bg-[#f6f3ed]"><span class="material-symbols-outlined text-sm">image</span>View photo</a>
                    @endif
                </div>

                @if ($report->isOpen())
                    <form method="POST" action="{{ route('maintenance-reports.update', $report) }}" class="mt-3 grid gap-2 border-t border-line pt-3 md:grid-cols-[auto_1fr_auto] md:items-center">
                        @csrf
                        @method('PATCH')
                        <select name="status" aria-label="Status for report MR-{{ $report->id }}" class="rounded-lg border-line bg-white py-2 text-sm font-semibold focus:border-secondary focus:ring-secondary">
                            @foreach (\App\Models\MaintenanceReport::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected($report->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input name="resolution_notes" maxlength="2000" placeholder="How was it fixed? (required when resolving)" class="rounded-lg border-line bg-white py-2 text-sm focus:border-secondary focus:ring-secondary">
                        <button type="submit" class="rounded-lg border border-secondary px-4 py-2 text-sm font-bold text-secondary hover:bg-secondary hover:text-white">Update</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-line bg-white p-10 text-center">
                <span class="material-symbols-outlined text-4xl text-slate-300">build_circle</span>
                <p class="mt-2 font-semibold text-slate-600">No {{ $statusFilter === 'resolved' ? 'resolved' : ($statusFilter === 'all' ? '' : 'open') }} maintenance reports.</p>
                <a href="{{ route('housekeeping.page', 'report-issue') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white">Report an issue</a>
            </div>
        @endforelse
    </div>
</section>
