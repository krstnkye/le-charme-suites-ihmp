@php
    $guest = $reservation->guest;
    $room = $reservation->room;
    $reference = $reservation->booking_reference ?: 'LC-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);
    $nights = $reservation->check_in_date && $reservation->check_out_date
        ? max(1, $reservation->check_in_date->diffInDays($reservation->check_out_date))
        : 1;
    $roomRate = (float) ($room?->rate ?? 0);
    $roomTotal = $roomRate * $nights;
    $taxes = round($roomTotal * 0.12, 2);
    $total = $roomTotal + $taxes;
    $agent = $reservation->confirmer?->name ?? $reservation->creator?->name ?? auth()->user()?->name ?? 'Front Desk Staff';
    $sourceLabels = [
        'front_desk' => 'Front Desk',
        'public_booking' => 'Online Booking Page',
        'online' => 'Online Booking Page',
        'walk_in' => 'Walk-in Guest',
    ];
    $statusLabel = ucwords(str_replace('_', ' ', $reservation->status));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Card {{ $reference }} - Le Charme Suites IHMP</title>
    @include('partials.offline-assets')
    @include('partials.favicon')
    <style>
        :root { --navy:#01162d; --navy-soft:#172b42; --gold:#755a28; --gold-light:#fdd79a; --paper:#fff; --cream:#f6f3ed; --line:#c4c6cd; --text:#1c1c18; --muted:#596273; }
        * { box-sizing: border-box; }
        body { margin:0; background:#e9e7e2; color:var(--text); font-family:Manrope, Arial, sans-serif; }
        .toolbar { position:sticky; top:0; z-index:10; display:flex; justify-content:space-between; align-items:center; gap:16px; min-height:64px; padding:10px 24px; background:var(--navy); color:#fff; box-shadow:0 3px 12px rgba(1,22,45,.2); }
        .toolbar-group { display:flex; align-items:center; gap:10px; }
        .toolbar a, .toolbar button { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:40px; padding:9px 14px; border:1px solid rgba(255,222,170,.55); border-radius:7px; background:transparent; color:#fff; font:700 13px Manrope,Arial,sans-serif; text-decoration:none; cursor:pointer; }
        .toolbar button { background:var(--gold-light); color:var(--navy); }
        .workspace { padding:24px 12px 40px; }
        .card { position:relative; width:min(194mm, calc(100vw - 24px)); min-height:270mm; margin:0 auto; padding:10mm 11mm 8mm; overflow:hidden; background:var(--paper); border:1px solid #d7d4cd; border-radius:5px; box-shadow:0 14px 36px rgba(1,22,45,.14); }
        .watermark { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; opacity:.025; }
        .watermark img { width:135mm; }
        .content { position:relative; z-index:1; }
        .header { display:grid; grid-template-columns:42mm 1fr 42mm; align-items:center; gap:5mm; padding-bottom:4mm; border-bottom:2px solid var(--navy); }
        .reference { font:600 9px Consolas,monospace; color:var(--muted); line-height:1.55; }
        .reference strong { display:block; color:var(--navy); font-size:13px; }
        .brand { text-align:center; }
        .brand img { display:block; width:48mm; max-height:22mm; margin:0 auto 2mm; object-fit:contain; }
        .brand h1 { margin:0; color:var(--navy); font:700 19px Georgia,serif; }
        .brand p { margin:1mm 0 0; color:var(--muted); font-size:9px; }
        .code { padding:3mm; border:1px solid var(--line); border-radius:4px; text-align:center; }
        .code-bars { height:14mm; background:repeating-linear-gradient(90deg,var(--navy) 0 1px,transparent 1px 3px,var(--navy) 3px 5px,transparent 5px 7px); }
        .code span { display:block; margin-top:1mm; color:var(--navy); font:700 9px Consolas,monospace; }
        .document-title { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:3mm 0; font-size:10px; }
        .document-title strong { color:var(--navy); font-size:12px; letter-spacing:.08em; }
        .status { padding:1.5mm 3mm; border:1px solid var(--gold); border-radius:999px; background:#fff8e8; color:var(--gold); font-size:9px; font-weight:800; text-transform:uppercase; }
        .section { margin-top:3mm; break-inside:avoid; }
        .section-title { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:2mm; padding:1.5mm 2.5mm; border-left:4px solid var(--gold); background:#ebe8e2; color:var(--navy); font-size:9px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
        .section-title small { color:var(--muted); font-size:8px; font-weight:600; letter-spacing:0; text-transform:none; }
        .grid { display:grid; gap:2mm; }
        .guest-grid { grid-template-columns:2fr 1fr 1fr; }
        .contact-grid { grid-template-columns:1fr 1.5fr; margin-top:2mm; }
        .address-grid { grid-template-columns:1.6fr 1fr; margin-top:2mm; }
        .stay-grid { grid-template-columns:repeat(4,1fr); border:1px solid var(--line); border-radius:4px; overflow:hidden; gap:0; }
        .charge-grid { grid-template-columns:repeat(4,1fr); margin-top:2mm; }
        .field { min-height:13mm; padding:2mm 2.5mm; border:1px solid var(--line); border-radius:4px; background:#fff; }
        .field label, .stay-cell label { display:block; margin-bottom:1mm; color:var(--muted); font-size:7.5px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .field strong, .stay-cell strong { display:block; color:var(--navy); font-size:10px; line-height:1.3; overflow-wrap:anywhere; }
        .field span, .stay-cell span { color:var(--muted); font-size:8px; line-height:1.3; }
        .stay-cell { min-height:18mm; padding:3mm; border-right:1px solid var(--line); background:#fff; }
        .stay-cell:last-child { border-right:0; }
        .stay-cell:first-child { background:#f4f6f8; }
        .total { background:#fff7e6; border-color:#d6b36a; }
        .terms { display:grid; grid-template-columns:1fr 1fr; gap:2mm 6mm; margin:0; padding:3mm 4mm 3mm 8mm; border:1px solid var(--line); border-radius:4px; color:#44474d; font-size:7.7px; line-height:1.45; }
        .signatures { display:grid; grid-template-columns:1fr 1fr; gap:12mm; margin-top:6mm; }
        .signature { min-height:17mm; padding-top:9mm; border-bottom:1px solid var(--navy); }
        .signature-label { display:flex; justify-content:space-between; gap:8px; padding-top:1mm; color:var(--muted); font-size:8px; }
        .guest-copy { display:flex; justify-content:space-between; align-items:center; gap:8mm; margin-top:6mm; padding:4mm; border-top:2px dashed var(--line); background:var(--cream); }
        .guest-copy strong { color:var(--navy); font-size:10px; }
        .guest-copy p { margin:1mm 0 0; color:var(--muted); font-size:8px; }
        .room-badge { display:flex; width:15mm; height:15mm; flex:none; align-items:center; justify-content:center; border-radius:4px; background:var(--navy); color:var(--gold-light); font-size:12px; font-weight:800; }
        .footer { display:flex; justify-content:space-between; gap:8px; margin-top:3mm; padding-top:2mm; border-top:1px solid #e5e2dc; color:#74777e; font:600 7px Consolas,monospace; }
        @media (max-width:760px) { .toolbar { align-items:flex-start; padding:10px 12px; } .toolbar-note { display:none; } .header { grid-template-columns:1fr; text-align:center; } .reference { text-align:center; } .code { display:none; } .guest-grid,.contact-grid,.address-grid,.stay-grid,.charge-grid,.terms,.signatures { grid-template-columns:1fr; } .stay-cell { border-right:0; border-bottom:1px solid var(--line); } }
        @media print {
            @page { size:A4 portrait; margin:7mm; }
            html, body { width:210mm; height:297mm; background:#fff !important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
            .no-print { display:none !important; }
            .workspace { padding:0; }
            .card { width:196mm; min-height:0; height:auto; margin:0; padding:5mm 7mm 4mm; border:0; border-radius:0; box-shadow:none; }
            .header { grid-template-columns:42mm 1fr 42mm; gap:4mm; padding-bottom:3mm; text-align:initial; }
            .reference { text-align:left; }
            .code { display:block; }
            .guest-grid { grid-template-columns:2fr 1fr 1fr; }
            .contact-grid { grid-template-columns:1fr 1.5fr; }
            .address-grid { grid-template-columns:1.6fr 1fr; }
            .stay-grid,.charge-grid { grid-template-columns:repeat(4,1fr); }
            .terms,.signatures { grid-template-columns:1fr 1fr; }
            .stay-cell { border-right:1px solid var(--line); border-bottom:0; }
            .stay-cell:last-child { border-right:0; }
            .section { margin-top:2.4mm; }
            .signatures { margin-top:4mm; }
            .signature { min-height:14mm; padding-top:7mm; }
            .guest-copy { margin-top:3mm; padding:3mm; }
            .footer { margin-top:2mm; }
        }
    </style>
</head>
<body>
    <header class="toolbar no-print">
        <div class="toolbar-group">
            <a href="{{ route('reservations.show', $reservation) }}"><span class="material-symbols-outlined">arrow_back</span>Back to Reservation</a>
            <div class="toolbar-note"><strong>Registration Card Preview</strong><br><small>{{ $reference }} · A4 · One page</small></div>
        </div>
        <button type="button" onclick="window.print()"><span class="material-symbols-outlined">print</span>Print Registration Card</button>
    </header>

    <main class="workspace">
        <article class="card" aria-label="Guest registration card">
            <div class="watermark"><img src="/images/le-charme-logo-white.png" alt=""></div>
            <div class="content">
                <header class="header">
                    <div class="reference">
                        REGISTRATION NO.
                        <strong>{{ $reference }}</strong>
                        Created: {{ $reservation->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') }}<br>
                        Prepared by: {{ $agent }}
                    </div>
                    <div class="brand">
                        <img src="/images/le-charme-logo-white.png" alt="Le Charme Suites and Hotel">
                        <h1>LE CHARME SUITES SUBIC</h1>
                        <p>Subic Bay Freeport Zone, Philippines · Integrated Hotel Management Portal</p>
                    </div>
                    <div class="code" aria-label="Registration reference code">
                        <div class="code-bars"></div>
                        <span>{{ $reference }}</span>
                    </div>
                </header>

                <div class="document-title">
                    <strong>GUEST REGISTRATION CARD</strong>
                    <span class="status">{{ $statusLabel }}</span>
                </div>

                <section class="section">
                    <div class="section-title"><span>1. Guest Information</span><small>Details saved in the guest record</small></div>
                    <div class="grid guest-grid">
                        <div class="field"><label>Full name</label><strong>{{ $guest?->full_name ?: 'Not recorded' }}</strong></div>
                        <div class="field"><label>Number of guests</label><strong>{{ $reservation->number_of_guests }}</strong></div>
                        <div class="field"><label>Booking source</label><strong>{{ $sourceLabels[$reservation->source] ?? ucwords(str_replace('_', ' ', $reservation->source ?: 'Front Desk')) }}</strong></div>
                    </div>
                    <div class="grid contact-grid">
                        <div class="field"><label>Mobile number</label><strong>{{ $guest?->phone ?: 'Not recorded' }}</strong></div>
                        <div class="field"><label>Email address</label><strong>{{ $guest?->email ?: 'Not recorded' }}</strong></div>
                    </div>
                    <div class="grid address-grid">
                        <div class="field"><label>Home or company address</label><strong>{{ $guest?->address ?: 'Not recorded' }}</strong></div>
                        <div class="field"><label>Government ID</label><strong>{{ trim(($guest?->identification_type ?? '').' '.($guest?->identification_number ?? '')) ?: 'Not recorded' }}</strong></div>
                    </div>
                </section>

                <section class="section">
                    <div class="section-title"><span>2. Room and Stay Details</span><small>Based on the confirmed reservation</small></div>
                    <div class="grid stay-grid">
                        <div class="stay-cell"><label>Assigned room</label><strong>Room {{ $room?->room_number ?? 'Not assigned' }}</strong><span>{{ $room?->room_type ?? 'Room type not recorded' }}</span></div>
                        <div class="stay-cell"><label>Check-in</label><strong>{{ $reservation->check_in_date?->format('M d, Y') ?? 'Not recorded' }}</strong><span>Standard time: 2:00 PM</span></div>
                        <div class="stay-cell"><label>Check-out</label><strong>{{ $reservation->check_out_date?->format('M d, Y') ?? 'Not recorded' }}</strong><span>Standard time: 12:00 PM</span></div>
                        <div class="stay-cell"><label>Length of stay</label><strong>{{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }}</strong><span>{{ $room?->bed_configuration ?: 'Bed details not recorded' }}</span></div>
                    </div>
                    <div class="grid charge-grid">
                        <div class="field"><label>Rate per night</label><strong>PHP {{ number_format($roomRate, 2) }}</strong></div>
                        <div class="field"><label>Room charges</label><strong>PHP {{ number_format($roomTotal, 2) }}</strong></div>
                        <div class="field"><label>Estimated taxes</label><strong>PHP {{ number_format($taxes, 2) }}</strong></div>
                        <div class="field total"><label>Estimated total</label><strong>PHP {{ number_format($total, 2) }}</strong></div>
                    </div>
                    <div class="field" style="margin-top:2mm"><label>Guest requests or reservation notes</label><strong>{{ $reservation->notes ?: 'No special request recorded.' }}</strong></div>
                </section>

                <section class="section">
                    <div class="section-title"><span>3. Hotel Rules and Guest Agreement</span><small>Please read before signing</small></div>
                    <ol class="terms">
                        <li><strong>Check-in and check-out:</strong> Standard check-in is 2:00 PM and check-out is 12:00 PM. Late check-out may have an additional charge.</li>
                        <li><strong>No smoking:</strong> Smoking and vaping are not allowed inside guest rooms and indoor hotel areas. Cleaning charges may apply.</li>
                        <li><strong>Hotel property:</strong> The guest is responsible for lost keys, damaged items, and other approved charges during the stay.</li>
                        <li><strong>Personal belongings:</strong> Guests should secure cash, jewelry, gadgets, and other valuable items. Please report lost items immediately.</li>
                        <li><strong>Data privacy:</strong> Guest information is used only for booking, hotel operations, safety, billing, and legal requirements under the Data Privacy Act.</li>
                        <li><strong>Payment:</strong> The guest agrees to pay the room charges, taxes, approved services, and any valid damage or replacement charges.</li>
                    </ol>
                </section>

                <section class="signatures">
                    <div>
                        <div class="signature"></div>
                        <div class="signature-label"><strong>Guest signature</strong><span>Date and time</span></div>
                    </div>
                    <div>
                        <div class="signature"></div>
                        <div class="signature-label"><strong>Front Desk staff signature</strong><span>{{ $agent }}</span></div>
                    </div>
                </section>

                <section class="guest-copy">
                    <div class="room-badge">{{ $room?->room_number ?? '--' }}</div>
                    <div style="flex:1">
                        <strong>{{ $guest?->full_name ?: 'Guest' }} · {{ $room?->room_type ?? 'Room not assigned' }}</strong>
                        <p>Stay: {{ $reservation->check_in_date?->format('M d') ?? '--' }} to {{ $reservation->check_out_date?->format('M d, Y') ?? '--' }} · Reference: {{ $reference }}</p>
                    </div>
                    <div style="text-align:right"><strong>Front Office</strong><p>+63 (047) 250-3333<br>frontoffice@lecharmehotels.com</p></div>
                </section>

                <footer class="footer">
                    <span>LE CHARME SUITES IHMP · GUEST REGISTRATION RECORD</span>
                    <span>PRINTED {{ now(config('app.timezone'))->format('M d, Y h:i A') }} PHT · PAGE 1 OF 1</span>
                </footer>
            </div>
        </article>
    </main>

    @if (request()->boolean('print'))
        <script>window.addEventListener('load', () => window.setTimeout(() => window.print(), 250));</script>
    @endif
</body>
</html>
