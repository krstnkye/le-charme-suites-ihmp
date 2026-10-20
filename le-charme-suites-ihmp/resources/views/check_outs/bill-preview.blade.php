@php
    $reference = $reservation?->booking_reference ?: 'BILL-' . now()->format('Y') . '-' . str_pad($checkIn->id, 5, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guest Bill {{ $reference }} - Le Charme Suites IHMP</title>
    @include('partials.favicon')
    <style>
        :root { --navy:#01162d; --gold:#755a28; --cream:#f6f3ed; --line:#d9d5cd; --muted:#5d6978; }
        * { box-sizing:border-box; }
        html,body { margin:0; background:#ebe8e2; color:var(--navy); font-family:Arial,Helvetica,sans-serif; font-size:12px; }
        .toolbar { position:sticky; top:0; z-index:5; display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 20px; background:var(--navy); color:#fff; }
        .toolbar div { line-height:1.45; }
        .actions { display:flex; gap:8px; }
        .button { display:inline-flex; min-height:38px; align-items:center; justify-content:center; padding:8px 14px; border:1px solid rgba(255,255,255,.45); border-radius:6px; color:#fff; background:transparent; text-decoration:none; font-weight:700; cursor:pointer; }
        .button.print { color:var(--navy); background:#ffdeaa; border-color:#e6c185; }
        .sheet { width:210mm; min-height:297mm; margin:18px auto; padding:14mm 15mm; background:#fff; box-shadow:0 6px 28px rgba(1,22,45,.14); }
        .header { display:grid; grid-template-columns:1.15fr .85fr; gap:18px; align-items:start; padding-bottom:13px; border-bottom:3px solid var(--navy); }
        .brand { display:flex; align-items:center; gap:14px; }
        .brand img { width:150px; height:66px; object-fit:contain; }
        h1,h2 { margin:0; font-family:Georgia,'Times New Roman',serif; }
        .hotel-name { font-size:20px; }
        .hotel-details { margin-top:5px; color:var(--muted); font-size:10px; line-height:1.5; }
        .document { text-align:right; }
        .document small { color:var(--gold); font-weight:800; letter-spacing:.14em; }
        .document h1 { margin:5px 0; font-size:27px; }
        .reference { font:700 13px 'Courier New',monospace; }
        .preview { display:inline-block; margin-top:8px; padding:5px 10px; border:1px solid var(--gold); border-radius:999px; background:#fff8e8; color:var(--gold); font-size:10px; font-weight:800; }
        .section { margin-top:16px; }
        .section-title { display:flex; justify-content:space-between; align-items:end; gap:12px; margin-bottom:8px; padding-bottom:6px; border-bottom:1px solid var(--line); font-size:15px; }
        .section-title span { color:var(--muted); font:400 9px Arial,Helvetica,sans-serif; }
        .info { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .card { padding:11px 12px; border:1px solid var(--line); border-radius:6px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:5px 18px; }
        .field { min-width:0; padding:5px 0; border-bottom:1px dotted var(--line); }
        .label { display:block; margin-bottom:2px; color:var(--muted); font-size:9px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
        .value { display:block; overflow-wrap:anywhere; font-size:11px; font-weight:700; }
        table { width:100%; border-collapse:collapse; }
        th { padding:8px; color:#fff; background:var(--navy); font-size:9px; text-align:left; text-transform:uppercase; }
        td { padding:9px 8px; border-bottom:1px solid var(--line); vertical-align:top; }
        .amount { text-align:right; white-space:nowrap; }
        .description { font-weight:700; }
        .sub { margin-top:2px; color:var(--muted); font-size:9px; }
        .summary { width:290px; margin:14px 0 0 auto; border:1px solid var(--line); border-radius:6px; overflow:hidden; }
        .summary-row { display:flex; justify-content:space-between; gap:16px; padding:9px 11px; border-bottom:1px solid var(--line); }
        .summary-row:last-child { border:0; color:#fff; background:var(--navy); font-size:13px; }
        .notice { margin-top:15px; padding:11px 13px; border-left:4px solid var(--gold); background:var(--cream); color:#455264; line-height:1.55; }
        .signatures { display:grid; grid-template-columns:1fr 1fr; gap:50px; margin-top:42px; }
        .line { padding-top:22px; border-bottom:1px solid var(--navy); }
        .signature-label { margin-top:5px; color:var(--muted); font-size:9px; text-align:center; }
        footer { margin-top:22px; padding-top:9px; border-top:1px solid var(--line); color:var(--muted); font-size:9px; text-align:center; line-height:1.5; }
        @page { size:A4 portrait; margin:0; }
        @media(max-width:820px) { .toolbar { align-items:flex-start; flex-direction:column; } .sheet { width:100%; min-height:auto; margin:0; padding:22px 16px; } .header,.info { grid-template-columns:1fr; } .document { text-align:left; } .brand { align-items:flex-start; flex-direction:column; } }
        @media print {
            html,body { background:#fff; font-size:10px; }
            .no-print { display:none!important; }
            .sheet { width:210mm; min-height:297mm; margin:0; padding:9mm 11mm 6mm; box-shadow:none; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
            .header { grid-template-columns:1.15fr .85fr; gap:12px; padding-bottom:9px; }
            .brand { align-items:center; flex-direction:row; gap:10px; }
            .brand img { width:126px; height:56px; }
            .document { text-align:right; }
            .document h1 { font-size:23px; }
            .section { margin-top:10px; }
            .section-title { margin-bottom:5px; padding-bottom:4px; }
            .info { grid-template-columns:1fr 1fr; gap:8px; }
            .card { padding:7px 8px; }
            .field { padding:3px 0; }
            th,td { padding:5px 6px; }
            .summary { margin-top:9px; }
            .signatures { margin-top:18px; }
            footer { margin-top:9px; padding-top:6px; }
        }
    </style>
</head>
<body>
    <header class="toolbar no-print">
        <div><strong>Guest Bill Preview</strong><br>{{ $reference }}</div>
        <div class="actions"><a class="button" href="{{ route('check-outs.create') }}">Back to Checkout</a><button class="button print" type="button" onclick="window.print()">Print Guest Bill</button></div>
    </header>

    <main class="sheet">
        <header class="header">
            <div class="brand">
                <img src="/images/le-charme-logo-white.png" alt="Le Charme Suites logo">
                <div><h2 class="hotel-name">Le Charme Suites Subic</h2><div class="hotel-details">Le Charme Bldg., 1131 Palm Street<br>Subic Bay Freeport Zone, Philippines 2200<br>+63 (047) 250-3333 · frontoffice@lecharmehotels.com</div></div>
            </div>
            <div class="document"><small>INTEGRATED HOTEL MANAGEMENT PORTAL</small><h1>GUEST BILL</h1><div class="reference">{{ $reference }}</div><div class="preview">FOR REVIEW BEFORE CHECKOUT</div></div>
        </header>

        <section class="section info">
            <div class="card"><h2 class="section-title">Guest Information</h2><div class="grid"><div class="field"><span class="label">Guest name</span><span class="value">{{ $checkIn->guest?->full_name ?? 'Not recorded' }}</span></div><div class="field"><span class="label">Mobile number</span><span class="value">{{ $checkIn->guest?->phone ?: 'Not recorded' }}</span></div><div class="field" style="grid-column:1/-1"><span class="label">Email</span><span class="value">{{ $checkIn->guest?->email ?: 'Not recorded' }}</span></div></div></div>
            <div class="card"><h2 class="section-title">Stay Information</h2><div class="grid"><div class="field"><span class="label">Room</span><span class="value">Room {{ $checkIn->room?->room_number ?? 'Not recorded' }}</span></div><div class="field"><span class="label">Room type</span><span class="value">{{ $checkIn->room?->room_type ?: 'Not recorded' }}</span></div><div class="field"><span class="label">Check-in</span><span class="value">{{ $reservation?->check_in_date?->format('M d, Y') ?? 'Not recorded' }}</span></div><div class="field"><span class="label">Expected check-out</span><span class="value">{{ $reservation?->check_out_date?->format('M d, Y') ?? 'Not recorded' }}</span></div></div></div>
        </section>

        <section class="section">
            <h2 class="section-title">Recorded Charges <span>Based on the room rate and saved hotel charges.</span></h2>
            <table><thead><tr><th>Date</th><th>Description</th><th>Reference</th><th class="amount">Amount</th></tr></thead><tbody>
                <tr><td>{{ $reservation?->check_in_date?->format('M d, Y') ?? 'Not recorded' }}</td><td><div class="description">Room accommodation</div><div class="sub">PHP {{ number_format((float) ($checkIn->room?->rate ?? 0), 2) }} per night for {{ $nights }} {{ $nights === 1 ? 'night' : 'nights' }}</div></td><td>{{ $reference }}</td><td class="amount">PHP {{ number_format($roomCharges, 2) }}</td></tr>
                @foreach($additionalCharges as $charge)
                    <tr><td>{{ $charge->transaction_date?->format('M d, Y') ?? 'Not recorded' }}</td><td><div class="description">{{ $charge->description ?: 'Additional hotel charge' }}</div><div class="sub">{{ ucfirst(str_replace('_', ' ', $charge->transaction_type)) }}</div></td><td>TRX-{{ str_pad($charge->id, 5, '0', STR_PAD_LEFT) }}</td><td class="amount">PHP {{ number_format((float) $charge->amount, 2) }}</td></tr>
                @endforeach
            </tbody></table>
            <div class="summary"><div class="summary-row"><span>Room charges</span><strong>PHP {{ number_format($roomCharges, 2) }}</strong></div><div class="summary-row"><span>Additional recorded charges</span><strong>PHP {{ number_format($additionalTotal, 2) }}</strong></div><div class="summary-row"><span>Total recorded charges</span><strong>PHP {{ number_format($totalCharges, 2) }}</strong></div></div>
        </section>

        <div class="notice"><strong>This is a guest bill preview, not a payment receipt.</strong> Complete the checkout and record the payment to generate the final guest checkout receipt.</div>
        <section class="signatures"><div><div class="line"></div><div class="signature-label">Guest acknowledgement</div></div><div><div class="line"></div><div class="signature-label">Hotel staff signature</div></div></section>
        <footer><strong>Le Charme Suites Subic · IHMP Guest Billing</strong><br>Printed {{ now(config('app.timezone'))->format('M d, Y h:i A') }} PHT · PAGE 1 OF 1</footer>
    </main>

    @if(request()->boolean('print'))
        <script>window.addEventListener('load', () => window.setTimeout(() => window.print(), 250));</script>
    @endif
</body>
</html>
