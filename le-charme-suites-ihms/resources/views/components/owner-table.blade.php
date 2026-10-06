@props(['headers' => []])

<div class="owner-scroll overflow-x-auto">
    <table class="w-full min-w-[760px] text-left text-sm">
        <thead class="border-b border-line bg-surface-mid text-xs font-bold uppercase tracking-wider text-slate-600">
            <tr>
                @foreach ($headers as $header)
                    <th class="px-4 py-3">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            {{ $slot }}
        </tbody>
    </table>
</div>
